<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\models\User;


class Income extends Model
{
    protected $fillable = ['name','amount'];

    public function user(){
        $this->belongsTo(User::class);
    }
}
