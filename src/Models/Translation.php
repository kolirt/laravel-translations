<?php

namespace Kolirt\Translations\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{

    public $timestamps = false;

    protected $fillable = ['lang', 'key', 'value', 'translation_id', 'translation_type'];

}
