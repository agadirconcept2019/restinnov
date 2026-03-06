<?php
namespace App\Models\RealEstate;
use Illuminate\Database\Eloquent\Model;
class RentalModeTranslation extends Model
{ public $timestamps=false; protected $fillable=['rental_mode_id','locale','name']; }
