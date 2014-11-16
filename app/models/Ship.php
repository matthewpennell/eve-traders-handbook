<?php

class Ship extends Eloquent {

    protected $table = 'ships';

    public function kill()
    {
        return $this->hasMany('Kill');
    }

}
