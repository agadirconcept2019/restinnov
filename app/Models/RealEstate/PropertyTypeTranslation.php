<?php
namespace App\Models\RealEstate;
use Illuminate\Database\Eloquent\Model;
class PropertyTypeTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['property_type_id', 'locale', 'name'];
}
