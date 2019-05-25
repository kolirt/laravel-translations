<?php

/*\Illuminate\Database\Eloquent\Builder::macro('whereLoc', function($column, $operator = null, $value = null, $boolean = 'and'){
    if (in_array($column, $this->model->translatable ?? []) && app()->getLocale() !== config('app.fallback_locale')) {
        $this->whereHas('translations', function($query) use ($column, $operator, $value, $boolean){
            $query->where('value', $operator, $value, $boolean);
            $query->where('key', $column);
        });
    } else if (in_array($column, $this->model->translatable ?? [])) {
        return $this->where($column, $operator, $value, $boolean);
    }

    return $this;
});*/
