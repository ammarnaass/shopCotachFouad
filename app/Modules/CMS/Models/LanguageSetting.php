<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'language_id',
        'setting_key',
        'setting_value',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
