# Codeigniter with Eloquent and Blade

[![Latest Stable Version](https://poser.pugx.org/recca0120/laraigniter/v/stable)](https://packagist.org/packages/recca0120/laraigniter)
[![Total Downloads](https://poser.pugx.org/recca0120/laraigniter/downloads)](https://packagist.org/packages/recca0120/laraigniter)
[![Latest Unstable Version](https://poser.pugx.org/recca0120/laraigniter/v/unstable)](https://packagist.org/packages/recca0120/laraigniter)
[![License](https://poser.pugx.org/recca0120/laraigniter/license)](https://packagist.org/packages/recca0120/laraigniter)
[![Monthly Downloads](https://poser.pugx.org/recca0120/laraigniter/d/monthly)](https://packagist.org/packages/recca0120/laraigniter)
[![Daily Downloads](https://poser.pugx.org/recca0120/laraigniter/d/daily)](https://packagist.org/packages/recca0120/laraigniter)


## Installation

Add Presenter to your composer.json file:

```json
"require": {
    "recca0120/laraigniter": "^0.1"
}
```
Now, run a composer update on the command line from the root of your project:

```
composer update
```

## How to use

import user.sql to mysql

### Database Config

application/config/database.php

```php
$db['default']['hostname'] = 'your hostname';
$db['default']['username'] = 'your username';
$db['default']['password'] = 'your password';
$db['default']['database'] = 'your test';
$db['default']['dbdriver'] = 'mysql';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = true;
$db['default']['db_debug'] = true;
$db['default']['cache_on'] = false;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = true;
$db['default']['stricton'] = false;
```



### Model

application/models/User.php

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
```

### Controller

application/controllers/welcome.php

```php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use App\Models\User;

class Welcome extends CI_Controller
{
    public function index()
    {
        User::create([
            'name'     => 'test'.uniqid(),
            'email'    => 'test'.uniqid().'@test.com',
            'password' => md5('test'.uniqid()),
        ]);

        $users = User::paginate(5);
        $this->output->set_output(View::make('users', compact('users')));
    }
}
```

### View

application/views/users.blade.php

```blade
<table width="100%" class="table">
    <tbody>
        <tr>
            <th>id</th>
            <th>name</th>
            <th>email</th>
        </tr>
    </tbody>
    @foreach ($users as $user)
        <tbody>
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
            </tr>
        </tbody>
    @endforeach
</table>

{!! $users->links() !!}
```
