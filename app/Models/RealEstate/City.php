<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\CityFactory::new();
    }

    protected $fillable = ['name', 'slug'];
}
