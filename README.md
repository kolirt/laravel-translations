# Laravel Translations 
Package tested with Laravel 5.8. Other versions are not tested.

| Laravel version  | Tested  |
| ---------------- | ------- |
| 5.8.*            | ✅      |

## Installation
```
$ composer require kolirt/laravel-translations
```
```
$ php artisan translations:install
```
Configure translations config on config/translations.php path.

<hr>

##### If you use sql count method or eloquent count method you need disabling global scope 'translatable'
```php
<?php

$query->withoutGlobalScope('translatable');
```
<hr>

## Important
If you use **where** method with translatable fields then you will see that it isn't working. You have to use **having** method.

## Usage
You need use Kolirt\Translations\Traits\Translatable trait to you model and fill $translatable property. You can set the type of the column. The default type is a string.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kolirt\Translations\Traits\Translatable;

class Tag extends Model
{

    use Translatable;

    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'active'];

    protected $translatable = ['name', 'slug', 'description' => 'text'];
    
}
```

### Availables column types
```
string
text
mediumText
longText
smallInteger
tinyInteger
integer
mediumInteger
bigInteger
decimal
boolean
date
dateTime
timestamp
```

### Return current language translation by column name
```php
<?php

$tag = Tag::first();
$tag->name;

// OR

$tag = Tag::first();
$tag->translation('name');
```

### Return translations by column name
```php
<?php

$tag = Tag::first();
$tag->translations('name');
```

### Save translations
You can't use next example code, because it don't work for saving translations.
```php
<?php

$tag = Tag::first();
$tag->name = 1;
$tag->save();
``` 

You need use update method for saving translations.
```php
<?php

$tag = Tag::first();
$tag->update([
    'name' => [
        'uk' => 'uk label',
        'en' => 'en label'
    ]
]);
```

### Validate
```php
<?php

$request->validate([
    'name.*' => [
        'unique_loc:table,type,name,id' // id not required
    ],
]);

/* 
    'name.*' must have array of language translates. Example:
   
        'name' => [
            'uk' => 'uk label'
            'en' => 'en label'
        ]
*/
```

### Local scope
You can disable globalScope in config and use local scope.
```php
<?php

Tag::translatable()->first();
```