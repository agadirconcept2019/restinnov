<?php
namespace App\Models\RealEstate;
use App\Models\RealEstate\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Area extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable=['city_id','slug','is_active'];
    protected $casts=['is_active'=>'boolean'];
    public function city(){ return $this->belongsTo(City::class); }
    public function translations(){ return $this->hasMany(AreaTranslation::class); }
}
