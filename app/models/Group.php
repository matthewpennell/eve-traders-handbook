<?php

class Group extends Eloquent {

    protected $table = 'invGroups';
    protected $primaryKey = 'groupID';

    public function category()
    {
        return $this->belongsTo('Category', 'categoryID');
    }

    public function icon()
    {
        return $this->belongsTo('Icon', 'iconID');
    }

    public function type()
    {
        return $this->hasMany('Type');
    }

}
