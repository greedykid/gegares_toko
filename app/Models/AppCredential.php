<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One integration credential. The value is encrypted on the way in and out, so
 * what sits in the table (and in any backup of it) is ciphertext.
 */
class AppCredential extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'encrypted',
    ];

    /**
     * Never let a credential reach a log, an exception page, or a JSON response
     * by accident.
     */
    protected $hidden = ['value'];
}
