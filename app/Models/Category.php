<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Expense;
use App\Models\Income;

class Category extends Model
{
    protected $fillable = [
        'name',
        'color',
        'icon',
        'type'
    ];

    public function expense()
    {
        return $this->hasMany(Expense::class);
    }

    public function income()
    {
        return $this->hasMany(Income::class);
    }
}
