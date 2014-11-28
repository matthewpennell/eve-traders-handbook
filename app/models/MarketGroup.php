<?php

class MarketGroup extends Eloquent {

    protected $table = 'invMarketGroups';
    protected $primaryKey = 'marketGroupID';

    public function item()
    {
        return $this->hasMany('Type');
    }

    public function icon()
    {
        return $this->belongsTo('Icon', 'iconID');
    }

}
