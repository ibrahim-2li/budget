<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'icon',
        'type',
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
