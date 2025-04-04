<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DaroodCount extends Model
{
    protected $table = 'darood_count';
    protected $fillable = [
        'counts',
        'date',
        'isActive'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
