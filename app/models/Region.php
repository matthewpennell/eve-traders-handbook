<?php

class Region extends Eloquent {

    protected $table = 'mapRegions';
    protected $primaryKey = 'regionID';

    public function system()
    {
        return $this->hasMany('System');
    }

}
