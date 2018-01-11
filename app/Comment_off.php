<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment_off extends Model
{
    public function off(){
    	 return $this->belongsTo('App\Comment');
    }
}
