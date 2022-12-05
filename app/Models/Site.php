<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'name',
        'phone',
        'utm_source',
        'utm_term',
        'utm_medium',
        'utm_content',
        'utm_campaign',
        'body',
        'lead_id',
        'contact_id',
        'error',
    ];
}
