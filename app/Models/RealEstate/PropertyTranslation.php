<?php
namespace App\Models\RealEstate;
use Illuminate\Database\Eloquent\Model;
class PropertyTranslation extends Model
{
    public $timestamps = false;
    protected $fillable=['property_id','locale','title','excerpt','description','house_rules_text'];
}
