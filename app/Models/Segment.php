<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
<<<<<<< HEAD
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
=======
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
>>>>>>> 0d6e2bb8226702340a33516e15cbf9f080bfd1bd
    ];
}
