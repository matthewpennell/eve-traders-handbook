<?php

class ActivitySkill extends Eloquent {

    protected $table = 'industryActivitySkills';

    public function type()
    {
        return $this->hasOne('Type', 'typeID');
    }

    public function skillID()
    {
        return $this->hasOne('Type', 'skillID');
    }

}
