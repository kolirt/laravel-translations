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

## Usage
You need use Kolirt\Translations\Traits\Translatable trait to you model and fill $translatable property.
```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kolirt\Translations\Traits\Translatable;

class Tag extends Model
{

    use Translatable;

    protected $fillable = ['name', 'slug', 'sort_order', 'active'];

    protected $translatable = ['name', 'slug'];
    
}
```

#### Return current language translation by column name.
```
<?php

$tag = Tag::first();
$tag->name;

// OR

$tag = Tag::first();
$tag->translation('name');
```

#### Return translations by column name.
```
<?php

$tag = Tag::first();
$tag->translations('name');
```

#### Save translations.
You can't use next example code, because it don't work for saving translations.
```
<?php

$tag = Tag::first();
$tag->name = 1;
$tag->save();
``` 

You need use update method for saving translations.
```
<?php

$tag = Tag::first();
$tag->update([
    'name' => [
        'uk' => 'uk label',
        'en' => 'en label'
    ]
]);
```

#### Validate.
```
<?php

$request->validate([
    'name.*' => [
        'unique_loc:table,name,id' // id not required
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