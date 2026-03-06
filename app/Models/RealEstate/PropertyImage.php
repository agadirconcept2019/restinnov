<?php
namespace App\Models\RealEstate;
use App\Models\Core\Media;
use Illuminate\Database\Eloquent\Model;
class PropertyImage extends Model
{
    public $timestamps=false;
    protected $fillable=['property_id','media_id','sort_order','is_cover'];
    protected $casts=['is_cover'=>'boolean'];
    public function media(){ return $this->belongsTo(Media::class); }
}
