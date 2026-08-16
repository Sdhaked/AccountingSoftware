<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxClass extends Model
{
    protected $fillable = ['name', 'percentage'];

    protected function casts(): array
    {
        return ['percentage' => 'decimal:3'];
    }
}
