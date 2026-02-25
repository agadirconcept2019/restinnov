<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyInquiry extends Model
{
    use HasFactory;

    protected $fillable = ['property_id','first_name','last_name','email','phone','checkin','checkout','guests','message','status','ip_hash','source_page','user_agent'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
