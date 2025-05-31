<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingView extends Model
{
    protected $fillable = ['listing_id', 'user_id', 'ip_address', 'viewed_at'];
}
