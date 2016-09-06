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
 * CUBRID Utility Class.
 *
 * @category	Database
 * @author		Esen Sagynov
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_cubrid_utility extends CI_DB_utility
{
    /**
     * List databases.
     *
     * @return	array
     */
    public function _list_databases()
    {
        // CUBRID does not allow to see the list of all databases on the
        // server. It is the way its architecture is designed. Every
        // database is independent and isolated.
        // For this reason we can return only the name of the currect
        // connected database.
        if ($this->conn_id) {
            return "SELECT '".$this->database."'";
        } else {
            return false;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Optimize table query.
     *
     * Generates a platform-specific query so that a table can be optimized
     *
     * @param	string	the table name
     * @return	object
     * @link 	http://www.cubrid.org/manual/840/en/Optimize%20Database
     */
    public function _optimize_table($table)
    {
        // No SQL based support in CUBRID as of version 8.4.0. Database or
        // table optimization can be performed using CUBRID Manager
        // database administration tool. See the link above for more info.
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Repair table query.
     *
     * Generates a platform-specific query so that a table can be repaired
     *
     * @param	string	the table name
     * @return	object
     * @link 	http://www.cubrid.org/manual/840/en/Checking%20Database%20Consistency
     */
    public function _repair_table($table)
    {
        // Not supported in CUBRID as of version 8.4.0. Database or
        // table consistency can be checked using CUBRID Manager
        // database administration tool. See the link above for more info.
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * CUBRID Export.
     *
     * @param	array	Preferences
     * @return	mixed
     */
    public function _backup($params = [])
    {
        // No SQL based support in CUBRID as of version 8.4.0. Database or
        // table backup can be performed using CUBRID Manager
        // database administration tool.
        return $this->db->display_error('db_unsuported_feature');
    }
}

/* End of file cubrid_utility.php */
/* Location: ./system/database/drivers/cubrid/cubrid_utility.php */
