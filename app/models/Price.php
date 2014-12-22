<?php

class Price extends Eloquent {

    protected $table = 'prices';
    protected $primaryKey = 'typeID';
    protected $fillable = array('typeID', 'regions', 'system', 'volume', 'avg', 'min', 'max', 'median');

    public function type()
    {
        return $this->belongsTo('Type', 'typeID');
    }

}
