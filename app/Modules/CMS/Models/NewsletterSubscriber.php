<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'subscribed_at', 'status'];

    protected $casts = [
        'subscribed_at' => 'datetime',
    ];
}
