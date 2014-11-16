<?php

class Category extends Eloquent {

    protected $table = 'invCategories';
    protected $primaryKey = 'categoryID';

    public function group()
    {
        return $this->hasMany('Group');
    }

}
