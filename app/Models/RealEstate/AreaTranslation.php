<?php
namespace App\Models\RealEstate;
use Illuminate\Database\Eloquent\Model;
class AreaTranslation extends Model
{ public $timestamps=false; protected $fillable=['area_id','locale','name']; }
