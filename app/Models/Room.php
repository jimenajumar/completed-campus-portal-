<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_name',
        'location',
        'capacity',
        'room_type',
        'description',
    ];
    public function reservations()
{
    return $this->hasMany(RoomReservation::class);
}
}
