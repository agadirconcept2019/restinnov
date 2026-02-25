<?php
namespace App\Models\RealEstate;
use App\Models\RealEstate\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Amenity extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable=['slug','is_active'];
    protected $casts=['is_active'=>'boolean'];
    public function translations(){ return $this->hasMany(AmenityTranslation::class); }
}
