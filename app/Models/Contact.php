<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_function_id',
        'email',
        'phone',
        'mobile',
        'internal_notes',
    ];

    public function contactFunction(): BelongsTo
    {
        return $this->belongsTo(ContactFunction::class, 'contact_function_id');
    }

    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class)->withTimestamps();
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'creator_contact_id');
    }
}
