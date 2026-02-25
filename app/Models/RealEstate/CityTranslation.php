<?php
namespace App\Models\RealEstate;
use Illuminate\Database\Eloquent\Model;
class CityTranslation extends Model
{ public $timestamps=false; protected $fillable=['city_id','locale','name']; }
