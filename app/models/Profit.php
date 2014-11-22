<?php

class Profit extends Eloquent {

    protected $table = 'profits';
    protected $primaryKey = 'typeID';

    public function type()
    {
        return $this->belongsTo('Type', 'typeID');
    }

}
