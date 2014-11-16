<?php

class Item extends Eloquent {

    protected $table = 'items';

    public function kill()
    {
        return $this->hasOne('Kill', 'killID');
    }

    public function type()
    {
        return $this->belongsTo('Type', 'typeID');
    }

}
