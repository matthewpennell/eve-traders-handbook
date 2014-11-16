<?php

class MetaType extends Eloquent {

    protected $table = 'invMetaTypes';
    protected $primaryKey = 'typeID';

    public function item()
    {
        return $this->hasOne('Type', 'typeID');
    }

    public function metaGroup()
    {
        return $this->belongsTo('MetaGroup', 'metaGroupID');
    }

}
