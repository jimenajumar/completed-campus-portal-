<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ClubAdvisorHistory;
use Illuminate\Support\Facades\DB;

class ClubController extends Controller
{
   // Display clubs
    public function index(Request $request)
    {
    $user = $request->user();

    $query = Club::with('advisor')->latest();

    // Admin can see all clubs.
    if ($user->role === 'admin') {
        // No additional filter.
    }
    // Club Advisors can see their assigned clubs and approved clubs.
    elseif ($user->role === 'club_advisor') {
        $query->where(function ($q) use ($user) {
            $q->where('advisor_id', $user->id)
              ->orWhere('status', 'approved');
        });
    }
    // Students can only see approved clubs.
    else {
        $query->where('status', 'approved');
    }

    return response()->json($query->get());
}

    // Create a club
public function store(Request $request)
{
    if (!$request->user() || $request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can create clubs.'
        ], 403);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:clubs,name',
        'description' => 'nullable|string',
        'advisor_id' => 'nullable|exists:users,id',
    ]);

    if (!empty($validated['advisor_id'])) {
        $advisor = User::find($validated['advisor_id']);

        if (!$advisor || $advisor->role !== 'club_advisor') {
            return response()->json([
                'message' => 'The selected user is not a club advisor.'
            ], 422);
        }
    }

    $validated['created_by'] = $request->user()->id;
    $validated['status'] = 'approved';

    $club = Club::create($validated);

    $this->logActivity(
        'created',
        'clubs',
        'Created club: ' . $club->name
    );

    return response()->json([
        'message' => 'Club created successfully.',
        'data' => $club,
    ], 201);
}

    // Display one club
public function show(Request $request, Club $club)
{
    $user = $request->user();

    if (
        $user->role !== 'admin' &&
        $club->status !== 'approved' &&
        !(
            $user->role === 'club_advisor' &&
            (int) $club->created_by === (int) $user->id
        ) &&
        !(
            $user->role === 'club_advisor' &&
            (int) $club->advisor_id === (int) $user->id
        )
    ) {
        return response()->json([
            'message' => 'Club not found.',
        ], 404);
    }

    return response()->json($club->load('advisor'));
}

    // Update a club
public function update(Request $request, Club $club)
{
    $user = $request->user();

    if (
        $user->role !== 'admin' &&
        !(
            $user->role === 'club_advisor' &&
            (int) $club->advisor_id === (int) $user->id
        )
    ) {
        return response()->json([
            'message' => 'You are not authorized to update this club.',
        ], 403);
    }

    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255|unique:clubs,name,' . $club->id,
        'description' => 'sometimes|nullable|string',
    ]);

    $club->update($validated);

    $this->logActivity(
        'updated',
        'clubs',
        'Updated club: ' . $club->name
    );

    return response()->json([
        'message' => 'Club updated successfully.',
        'data' => $club,
    ]);
}
// Archive a club (Admin only)
// Archive a club (Admin only)
public function destroy(Request $request, Club $club)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can archive clubs.',
        ], 403);
    }

    $clubName = $club->name;

    $club->delete();

    $this->logActivity(
        'archived',
        'clubs',
        'Archived club: ' . $clubName
    );

    return response()->json([
        'message' => 'Club archived successfully.',
    ]);
}

   // Approve a club
public function approve(Request $request, Club $club)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can approve club requests.',
        ], 403);
    }

    if ($club->status !== 'pending') {
        return response()->json([
            'message' => 'Only pending club requests can be approved.',
        ], 422);
    }

    $club->update(['status' => 'approved']);

    $this->logActivity(
        'approved',
        'clubs',
        'Approved club: ' . $club->name
    );

    return response()->json([
        'message' => 'Club approved successfully.',
        'data' => $club,
    ]);
}

    // Reject a club
public function reject(Request $request, Club $club)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can reject club requests.',
        ], 403);
    }

    if ($club->status !== 'pending') {
        return response()->json([
            'message' => 'Only pending club requests can be rejected.',
        ], 422);
    }

    $club->update(['status' => 'rejected']);

    $this->logActivity(
        'rejected',
        'clubs',
        'Rejected club: ' . $club->name
    );

    return response()->json([
        'message' => 'Club rejected successfully.',
        'data' => $club,
    ]);
}
    // List available club advisors
public function advisors(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can view the advisor list.',
        ], 403);
    }

    $advisors = User::where('role', 'club_advisor')
        ->orderBy('name')
        ->get(['id', 'name', 'email']);

    return response()->json($advisors);
}

// Assign or remove a club advisor

// Assign or remove a club advisor and record assignment history
public function assignAdvisor(Request $request, Club $club)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can assign club advisors.',
        ], 403);
    }

    $validated = $request->validate([
        'advisor_id' => 'nullable|integer|exists:users,id',
    ]);

    $newAdvisorId = $validated['advisor_id'] ?? null;

    if ($newAdvisorId) {
        $advisor = User::findOrFail($newAdvisorId);

        if ($advisor->role !== 'club_advisor') {
            return response()->json([
                'message' => 'Selected user is not a club advisor.',
            ], 422);
        }
    }

    DB::transaction(function () use ($club, $newAdvisorId, $request) {

        $currentAdvisorId = $club->advisor_id;

        // Do nothing if the assignment has not changed.
        if ((int) $currentAdvisorId === (int) $newAdvisorId) {
            return;
        }

        // Close the previous assignment.
        if ($currentAdvisorId) {
            ClubAdvisorHistory::where('club_id', $club->id)
                ->where('advisor_id', $currentAdvisorId)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => now(),
                ]);
        }

        // Assign the new advisor and record the history.
        $club->update([
            'advisor_id' => $newAdvisorId,
        ]);

        if ($newAdvisorId) {
            ClubAdvisorHistory::create([
                'club_id' => $club->id,
                'advisor_id' => $newAdvisorId,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
                'ended_at' => null,
            ]);
        }
    });

    $this->logActivity(
        'updated',
        'clubs',
        'Updated advisor assignment for club: ' . $club->name
    );

    return response()->json([
        'message' => 'Club advisor updated successfully.',
        'data' => $club->fresh()->load('advisor'),
    ]);
}

// Display instructor assignment history for a club
public function advisorHistory(Request $request, Club $club)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can view instructor assignment history.',
        ], 403);
    }

    $history = $club->advisorHistories()
        ->get()
        ->map(function ($record) {
            return [
                'id' => $record->id,
                'advisor_id' => $record->advisor_id,
                'advisor_name' => $record->advisor?->name,
                'advisor_email' => $record->advisor?->email,
                'assigned_by' => $record->assignedBy?->name,
                'assigned_at' => $record->assigned_at,
                'ended_at' => $record->ended_at,
                'status' => $record->ended_at ? 'Previous' : 'Current',
            ];
        });

    return response()->json([
        'club' => $club->name,
        'history' => $history,
    ]);
}


    // Display clubs assigned to the logged-in advisor.
public function myAssignedClubs(Request $request)
    {
    $user = $request->user();

    if ($user->role !== 'club_advisor') {
        return response()->json([
            'message' => 'Only club advisors can access this list.',
        ], 403);
    }

    $clubs = Club::where('advisor_id', $user->id)
        ->where('status', 'approved')
        ->orderBy('name')
        ->get(['id', 'name', 'advisor_id']);

    return response()->json($clubs);
    }
    // Display archived clubs (Admin only)
public function archived(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can view archived clubs.',
        ], 403);
    }

    $clubs = Club::onlyTrashed()
        ->with('advisor')
        ->latest('deleted_at')
        ->get();

    return response()->json($clubs);
}

// Restore an archived club (Admin only)
public function restore(Request $request, $id)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can restore clubs.',
        ], 403);
    }

    $club = Club::onlyTrashed()->findOrFail($id);

    $club->restore();

    $this->logActivity(
        'restored',
        'clubs',
        'Restored club: ' . $club->name
    );

    return response()->json([
        'message' => 'Club restored successfully.',
        'data' => $club->fresh()->load('advisor'),
    ]);
}
// Display archived clubs (Admin only)
public function archive(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only administrators can view archived clubs.',
        ], 403);
    }

    $clubs = Club::onlyTrashed()
        ->with('advisor')
        ->latest('deleted_at')
        ->get();

    return response()->json($clubs);
}
}
