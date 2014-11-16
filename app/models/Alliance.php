<?php

class Alliance extends Eloquent {

    protected $table = 'alliances';

    public function kill()
    {
        return $this->hasMany('Kill');
    }

}
