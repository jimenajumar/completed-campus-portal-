<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Club;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\RoomReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomReservationController extends Controller
{
    // Admin: View all reservation requests.
    public function index()
    {
        $reservations = RoomReservation::with([
            'announcement.club',
            'room',
            'requestedBy',
            'reviewedBy'
        ])->latest()->get();

        return response()->json([
            'data' => $reservations
        ]);
    }

    // Club Advisor: Create an announcement with a room request.
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'club_advisor') {
            return response()->json([
                'message' => 'Only Club Advisors can request room reservations.'
            ], 403);
        }

        $validated = $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'room_id' => 'required|exists:rooms,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $club = Club::where('id', $validated['club_id'])
            ->where('advisor_id', $user->id)
            ->first();

        if (!$club) {
            return response()->json([
                'message' => 'You are not assigned to this club.'
            ], 403);
        }

        return DB::transaction(function () use ($validated, $user) {

            // Lock the room while checking availability.
            Room::whereKey($validated['room_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Check existing Admin room assignments.
            $assignmentConflict = RoomAssignment::where(
                    'room_id',
                    $validated['room_id']
                )
                ->where('status', 'scheduled')
                ->where('starts_at', '<', $validated['ends_at'])
                ->where('ends_at', '>', $validated['starts_at'])
                ->exists();

            // Check already approved reservations.
            $reservationConflict = RoomReservation::where(
                    'room_id',
                    $validated['room_id']
                )
                ->where('status', 'approved')
                ->where('starts_at', '<', $validated['ends_at'])
                ->where('ends_at', '>', $validated['starts_at'])
                ->exists();

            if ($assignmentConflict || $reservationConflict) {
                return response()->json([
                    'message' => 'This room is already booked during the selected time.'
                ], 409);
            }

            // Create the announcement as unpublished.
            $announcement = Announcement::create([
                'created_by' => $user->id,
                'club_id' => $validated['club_id'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'category' => $validated['category'] ?? 'general',
                'is_published' => false,
                'published_at' => null,
            ]);

            // Create the pending reservation.
            $reservation = RoomReservation::create([
                'announcement_id' => $announcement->id,
                'room_id' => $validated['room_id'],
                'requested_by' => $user->id,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'status' => 'pending',
            ]);

            $this->logActivity(
                'created',
                'room_reservations',
                'Requested a room reservation for: ' . $announcement->title
            );

            return response()->json([
                'message' => 'Announcement submitted. It will be automatically published after Admin approves the room reservation.',
                'data' => $reservation->load([
                    'announcement.club',
                    'room'
                ])
            ], 201);
        });
    }

    // Club Advisor: View their own reservation requests.
    public function myReservations(Request $request)
    {
        $reservations = RoomReservation::with([
            'announcement.club',
            'room',
            'reviewedBy'
        ])
            ->where('requested_by', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $reservations
        ]);
    }

    // Admin: Approve a room reservation and publish its announcement.
    public function approve(Request $request, RoomReservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'This reservation has already been reviewed.'
            ], 422);
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use (
            $request,
            $reservation,
            $validated
        ) {
            Room::whereKey($reservation->room_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Recheck Admin room assignments before approval.
            $assignmentConflict = RoomAssignment::where(
                    'room_id',
                    $reservation->room_id
                )
                ->where('status', 'scheduled')
                ->where('starts_at', '<', $reservation->ends_at)
                ->where('ends_at', '>', $reservation->starts_at)
                ->exists();

            // Recheck other approved reservations.
            $reservationConflict = RoomReservation::where(
                    'room_id',
                    $reservation->room_id
                )
                ->where('status', 'approved')
                ->where('id', '!=', $reservation->id)
                ->where('starts_at', '<', $reservation->ends_at)
                ->where('ends_at', '>', $reservation->starts_at)
                ->exists();

            if ($assignmentConflict || $reservationConflict) {
                return response()->json([
                    'message' => 'This room is no longer available for the requested time.'
                ], 409);
            }

            $reservation->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'admin_note' => $validated['admin_note'] ?? null,
            ]);

            // Automatically publish the announcement.
            $reservation->announcement->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            $this->logActivity(
                'approved',
                'room_reservations',
                'Approved room reservation for: ' .
                    $reservation->announcement->title
            );

            return response()->json([
                'message' => 'Reservation approved and announcement published successfully.',
                'data' => $reservation->fresh()->load([
                    'announcement.club',
                    'room',
                    'requestedBy',
                    'reviewedBy'
                ])
            ]);
        });
    }

    // Admin: Reject a room reservation.
    public function reject(Request $request, RoomReservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'This reservation has already been reviewed.'
            ], 422);
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        $reservation->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note' => $validated['admin_note'],
        ]);

        $reservation->announcement->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->logActivity(
            'rejected',
            'room_reservations',
            'Rejected room reservation for: ' .
                $reservation->announcement->title
        );

        return response()->json([
            'message' => 'Reservation rejected. The announcement remains unpublished.',
            'data' => $reservation->fresh()->load([
                'announcement.club',
                'room',
                'requestedBy',
                'reviewedBy'
            ])
        ]);
    }
}
