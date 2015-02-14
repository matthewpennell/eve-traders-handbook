<?php

class MaterialEfficiency extends Eloquent {

    protected $table = 'materialEfficiency';
    protected $primaryKey = 'typeID';

    public function type()
    {
        return $this->belongsTo('Type', 'typeID');
    }

}
