<?php

class Kill extends Eloquent {

    protected $table = 'kills';
    protected $primaryKey = 'killID';

    public function ship()
    {
        return $this->hasOne('Ship');
    }

    public function system()
    {
        return $this->hasOne('System');
    }

    public function corporation()
    {
        return $this->hasOne('Corporation');
    }

    public function alliance()
    {
        return $this->hasOne('Alliance');
    }

}
