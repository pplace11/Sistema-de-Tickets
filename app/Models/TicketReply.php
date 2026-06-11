<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'author_type',
        'author_user_id',
        'author_contact_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function authorContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'author_contact_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_reply_id');
    }
}
