<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebScraper extends Model
{
    use HasFactory;

    protected $fillable = [

        'id', 'p_title', 'location', 'property_type', 'bedroom', 'bathroom', 'deposit', 'price_p_month', 'price_p_week', 'key_feature', 'description', 'agent_name', 'agent_address', 'agent_contact_no', 'agent_description', 'image', 'created_at', 'updated_at'

    ];
}
