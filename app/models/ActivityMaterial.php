<?php

class ActivityMaterial extends Eloquent {

    protected $table = 'industryActivityMaterials';

    public function type()
    {
        return $this->hasOne('Type', 'typeID');
    }

    public function materialType()
    {
        return $this->hasOne('Type', 'materialTypeID');
    }

}
