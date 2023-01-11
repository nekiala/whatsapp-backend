<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property Session $session
 */
class Customer extends Model
{
    use HasFactory;

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function session(): HasOne
    {
        return $this->hasOne(Session::class);
    }
}
