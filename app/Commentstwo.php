<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commentstwo extends Model
{
    public function c()
    {
        return $this->belongsTo('App\Comment');
    }
}
