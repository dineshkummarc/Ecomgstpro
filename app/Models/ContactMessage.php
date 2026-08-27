<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute()
    {
        return $this->phone ? format_phone($this->phone) : 'N/A';
    }

    /**
     * Check if message is new
     */
    public function isNew()
    {
        return $this->status === 'new';
    }

    /**
     * Check if message has been read
     */
    public function isRead()
    {
        return $this->status === 'read';
    }

    /**
     * Check if message has been replied
     */
    public function isReplied()
    {
        return $this->status === 'replied';
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update(['status' => 'read']);
        return $this;
    }

    /**
     * Mark message as replied
     */
    public function markAsReplied()
    {
        $this->update(['status' => 'replied']);
        return $this;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor()
    {
        return match($this->status) {
            'new' => 'danger',
            'read' => 'info',
            'replied' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get status badge text
     */
    public function getStatusBadgeText()
    {
        return ucfirst($this->status);
    }
}
