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
      'count_leads',
      'sale',
      'count_lost_invest',
      'count_lost_apart',

      'count_leads_invest',
      'count_leads_apart',

      'count_active_invest',
      'count_active_apart',

       'sale_invest',
       'sale_apart',

       'count_success_invest',
       'count_success_apart',
    ];
}
