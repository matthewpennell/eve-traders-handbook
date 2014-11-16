<?php

class MetaGroup extends Eloquent {

    protected $table = 'invMetaGroups';
    protected $primaryKey = 'metaGroupID';

    public function metaType()
    {
        return $this->hasMany('MetaType');
    }

}
