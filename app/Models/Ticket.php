<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'subject',
        'inbox_id',
        'ticket_type_id',
        'ticket_status_id',
        'assigned_operator_id',
        'entity_id',
        'creator_contact_id',
        'creator_user_id',
        'message',
    ];

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function assignedOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function creatorContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'creator_contact_id');
    }

    public function creatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function cc(): HasMany
    {
        return $this->hasMany(TicketCc::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
