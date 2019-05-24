<?php

namespace Kolirt\Translations\Traits;

use Kolirt\Translations\Models\Translation;

trait Translatable
{

    public function translations()
    {
        return $this->hasMany(Translation::class, 'translation_id', 'id')->where('translation_type', self::class);
    }

    public function getAttribute($key)
    {
        if (in_array($key, $this->translatable ?? []) && app()->getLocale() !== config('app.fallback_locale')) {
            $translation = $this->translations->where('key', $key)->where('lang', app()->getLocale())->first();
            $result = $translation->value ?? null;

            if ($this->hasGetMutator($key)) {
                return $this->mutateAttribute($key, $result);
            } else {
                return $result;
            }
        }

        return parent::getAttribute($key);
    }



}