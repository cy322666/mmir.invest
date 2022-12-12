<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'contact_id',
        'sale',
        'sale_invest',
        'sale_apart',
        'count_leads',
        'is_double',
        'link_double_phone',
        'link_double_email',
        'count_leads_invest',
        'count_leads_apart',
    ];
}
