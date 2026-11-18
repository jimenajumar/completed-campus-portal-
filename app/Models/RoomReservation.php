<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'room_id',
        'requested_by',
        'reviewed_by',
        'starts_at',
        'ends_at',
        'status',
        'admin_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
