<?php

class Type extends Eloquent {

    protected $table = 'invTypes';
    protected $primaryKey = 'typeID';
    public $timestamps = false;

    public function item()
    {
        return $this->hasMany('Item', 'id');
    }

    public function marketGroup()
    {
        return $this->belongsTo('MarketGroup', 'marketGroupID');
    }

    public function group()
    {
        return $this->belongsTo('Group', 'groupID');
    }

    public function metaType()
    {
        return $this->belongsTo('MetaType', 'typeID');
    }

    public function profit()
    {
        return $this->hasOne('Profit', 'typeID');
    }

    public function materialEfficiency()
    {
        return $this->hasOne('MaterialEfficiency', 'typeID');
    }

}
