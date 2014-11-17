<?php

class Icon extends Eloquent {

    protected $table = 'eveIcons';
    protected $primaryKey = 'iconID';

    public function group()
    {
        return $this->hasMany('Group');
    }

}
