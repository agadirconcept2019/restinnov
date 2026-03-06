<?php

namespace App\Modules\OwnerPortal\Models;

use Illuminate\Database\Eloquent\Model;

class CrmNote extends Model
{
    protected $fillable = ['entity_type', 'entity_id', 'author_user_id', 'note', 'visibility'];
}
