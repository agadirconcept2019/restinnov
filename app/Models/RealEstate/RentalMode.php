<?php

namespace App\Models\RealEstate;

use App\Models\RealEstate\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalMode extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['slug', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function newFactory()
    {
        return \Database\Factories\RentalModeFactory::new();
    }

    public function translations()
    {
        return $this->hasMany(RentalModeTranslation::class);
    }
}
