<?php

class Corporation extends Eloquent {

    protected $table = 'corporations';

    public function kill()
    {
        return $this->hasMany('Kill');
    }

}
