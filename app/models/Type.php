<?php

class Type extends Eloquent {

    protected $table = 'invTypes';
    protected $primaryKey = 'typeID';

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

}
