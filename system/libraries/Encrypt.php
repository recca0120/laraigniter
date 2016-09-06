<?php
 if (! defined('BASEPATH')) {
     exit('No direct script access allowed');
 }
/**
 * CodeIgniter.
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @author		EllisLab Dev Team
 * @copyright		Copyright (c) 2008 - 2014, EllisLab, Inc.
 * @copyright		Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Encryption Class.
 *
 * Provides two-way keyed encoding using Mcrypt
 *
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/encryption.html
 */
class CI_Encrypt
{
    public $CI;
    public $encryption_key = '';
    public $_hash_type = 'sha1';
    public $_mcrypt_exists = false;
    public $_mcrypt_cipher;
    public $_mcrypt_mode;

    /**
     * Constructor.
     *
     * Simply determines whether the mcrypt library exists.
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->_mcrypt_exists = (! function_exists('mcrypt_encrypt')) ? false : true;

        if ($this->_mcrypt_exists === false) {
            show_error('The Encrypt library requires the Mcrypt extension.');
        }

        log_message('debug', 'Encrypt Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the encryption key.
     *
     * Returns it as MD5 in order to have an exact-length 128 bit key.
     * Mcrypt is sensitive to keys that are not the correct length
     *
     * @param	string
     * @return	string
     */
    public function get_key($key = '')
    {
        if ($key == '') {
            if ($this->encryption_key != '') {
                return $this->encryption_key;
            }

            $CI = &get_instance();
            $key = $CI->config->item('encryption_key');

            if ($key == false) {
                show_error('In order to use the encryption class requires that you set an encryption key in your config file.');
            }
        }

        return md5($key);
    }

    // --------------------------------------------------------------------

    /**
     * Set the encryption key.
     *
     * @param	string
     * @return	void
     */
    public function set_key($key = '')
    {
        $this->encryption_key = $key;
    }

    // --------------------------------------------------------------------

    /**
     * Encode.
     *
     * Encodes the message string using bitwise XOR encoding.
     * The key is combined with a random hash, and then it
     * too gets converted using XOR. The whole thing is then run
     * through mcrypt using the randomized key. The end result
     * is a double-encrypted message string that is randomized
     * with each call to this function, even if the supplied
     * message and key are the same.
     *
     * @param	string	the string to encode
     * @param	string	the key
     * @return	string
     */
    public function encode($string, $key = '')
    {
        $key = $this->get_key($key);
        $enc = $this->mcrypt_encode($string, $key);

        return base64_encode($enc);
    }

    // --------------------------------------------------------------------

    /**
     * Decode.
     *
     * Reverses the above process
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function decode($string, $key = '')
    {
        $key = $this->get_key($key);

        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string)) {
            return false;
        }

        $dec = base64_decode($string);

        if (($dec = $this->mcrypt_decode($dec, $key)) === false) {
            return false;
        }

        return $dec;
    }

    // --------------------------------------------------------------------

    /**
     * Encode from Legacy.
     *
     * Takes an encoded string from the original Encryption class algorithms and
     * returns a newly encoded string using the improved method added in 2.0.0
     * This allows for backwards compatibility and a method to transition to the
     * new encryption algorithms.
     *
     * For more details, see http://codeigniter.com/user_guide/installation/upgrade_200.html#encryption
     *
     * @param	string
     * @param	int		(mcrypt mode constant)
     * @param	string
     * @return	string
     */
    public function encode_from_legacy($string, $legacy_mode = MCRYPT_MODE_ECB, $key = '')
    {
        // decode it first
        // set mode temporarily to what it was when string was encoded with the legacy
        // algorithm - typically MCRYPT_MODE_ECB
        $current_mode = $this->_get_mode();
        $this->set_mode($legacy_mode);

        $key = $this->get_key($key);

        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string)) {
            return false;
        }

        $dec = base64_decode($string);

        if (($dec = $this->mcrypt_decode($dec, $key)) === false) {
            return false;
        }

        $dec = $this->_xor_decode($dec, $key);

        // set the mcrypt mode back to what it should be, typically MCRYPT_MODE_CBC
        $this->set_mode($current_mode);

        // and re-encode
        return base64_encode($this->mcrypt_encode($dec, $key));
    }

    // --------------------------------------------------------------------

    /**
     * XOR Decode.
     *
     * Takes an encoded string and key as input and generates the
     * plain-text original message
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function _xor_decode($string, $key)
    {
        $string = $this->_xor_merge($string, $key);

        $dec = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
        }

        return $dec;
    }

    // --------------------------------------------------------------------

    /**
     * XOR key + string Combiner.
     *
     * Takes a string and key as input and computes the difference using XOR
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function _xor_merge($string, $key)
    {
        $hash = $this->hash($key);
        $str = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Encrypt using Mcrypt.
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function mcrypt_encode($data, $key)
    {
        $init_size = mcrypt_get_iv_size($this->_get_cipher(), $this->_get_mode());
        $init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

        return $this->_add_cipher_noise($init_vect.mcrypt_encrypt($this->_get_cipher(), $key, $data, $this->_get_mode(), $init_vect), $key);
    }

    // --------------------------------------------------------------------

    /**
     * Decrypt using Mcrypt.
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function mcrypt_decode($data, $key)
    {
        $data = $this->_remove_cipher_noise($data, $key);
        $init_size = mcrypt_get_iv_size($this->_get_cipher(), $this->_get_mode());

        if ($init_size > strlen($data)) {
            return false;
        }

        $init_vect = substr($data, 0, $init_size);
        $data = substr($data, $init_size);

        return rtrim(mcrypt_decrypt($this->_get_cipher(), $key, $data, $this->_get_mode(), $init_vect), "\0");
    }

    // --------------------------------------------------------------------

    /**
     * Adds permuted noise to the IV + encrypted data to protect
     * against Man-in-the-middle attacks on CBC mode ciphers
     * http://www.ciphersbyritter.com/GLOSSARY.HTM#IV.
     *
     * Function description
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function _add_cipher_noise($data, $key)
    {
        $keyhash = $this->hash($key);
        $keylen = strlen($keyhash);
        $str = '';

        for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
            if ($j >= $keylen) {
                $j = 0;
            }

            $str .= chr((ord($data[$i]) + ord($keyhash[$j])) % 256);
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Removes permuted noise from the IV + encrypted data, reversing
     * _add_cipher_noise().
     *
     * Function description
     *
     * @param	type
     * @return	type
     */
    public function _remove_cipher_noise($data, $key)
    {
        $keyhash = $this->hash($key);
        $keylen = strlen($keyhash);
        $str = '';

        for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
            if ($j >= $keylen) {
                $j = 0;
            }

            $temp = ord($data[$i]) - ord($keyhash[$j]);

            if ($temp < 0) {
                $temp = $temp + 256;
            }

            $str .= chr($temp);
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Set the Mcrypt Cipher.
     *
     * @param	constant
     * @return	string
     */
    public function set_cipher($cipher)
    {
        $this->_mcrypt_cipher = $cipher;
    }

    // --------------------------------------------------------------------

    /**
     * Set the Mcrypt Mode.
     *
     * @param	constant
     * @return	string
     */
    public function set_mode($mode)
    {
        $this->_mcrypt_mode = $mode;
    }

    // --------------------------------------------------------------------

    /**
     * Get Mcrypt cipher Value.
     *
     * @return	string
     */
    public function _get_cipher()
    {
        if ($this->_mcrypt_cipher == '') {
            $this->_mcrypt_cipher = MCRYPT_RIJNDAEL_256;
        }

        return $this->_mcrypt_cipher;
    }

    // --------------------------------------------------------------------

    /**
     * Get Mcrypt Mode Value.
     *
     * @return	string
     */
    public function _get_mode()
    {
        if ($this->_mcrypt_mode == '') {
            $this->_mcrypt_mode = MCRYPT_MODE_CBC;
        }

        return $this->_mcrypt_mode;
    }

    // --------------------------------------------------------------------

    /**
     * Set the Hash type.
     *
     * @param	string
     * @return	string
     */
    public function set_hash($type = 'sha1')
    {
        $this->_hash_type = ($type != 'sha1' and $type != 'md5') ? 'sha1' : $type;
    }

    // --------------------------------------------------------------------

    /**
     * Hash encode a string.
     *
     * @param	string
     * @return	string
     */
    public function hash($str)
    {
        return ($this->_hash_type == 'sha1') ? $this->sha1($str) : md5($str);
    }

    // --------------------------------------------------------------------

    /**
     * Generate an SHA1 Hash.
     *
     * @param	string
     * @return	string
     */
    public function sha1($str)
    {
        if (! function_exists('sha1')) {
            if (! function_exists('mhash')) {
                require_once BASEPATH.'libraries/Sha1.php';
                $SH = new CI_SHA;

                return $SH->generate($str);
            } else {
                return bin2hex(mhash(MHASH_SHA1, $str));
            }
        } else {
            return sha1($str);
        }
    }
}

// END CI_Encrypt class

/* End of file Encrypt.php */
/* Location: ./system/libraries/Encrypt.php */
