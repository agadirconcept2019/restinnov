<?php
namespace App\Models\RealEstate;
use Illuminate\Database\Eloquent\Model;
class AmenityTranslation extends Model
{ public $timestamps=false; protected $fillable=['amenity_id','locale','name']; }
