<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubAdvisorHistory extends Model
{
    protected $table = 'club_advisor_histories';




    protected $fillable = [
        'club_id',
        'advisor_id',
        'assigned_by',
        'assigned_at',
        'ended_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
