<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Club;
use App\Models\ClubMembership;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // Display announcements according to the user's role.
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Announcement::with('club')->latest();

        if ($user->role === 'admin') {
            // Admin can see all announcements.
        } elseif ($user->role === 'club_advisor') {
            // Advisor can see announcements belonging to assigned clubs.
            $query->whereIn(
                'club_id',
                Club::where('advisor_id', $user->id)->select('id')
            );
        } elseif ($user->role === 'student') {
            // Students can see general published announcements
            // and published announcements from approved clubs.
            $query->where('is_published', true)
                ->where(function ($q) use ($user) {
                    $q->whereNull('club_id')
                        ->orWhereIn(
                            'club_id',
                            ClubMembership::where('student_id', $user->id)
                                ->where('status', 'approved')
                                ->select('club_id')
                        );
                });
        } else {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        return response()->json($query->get());
    }

    // Create an announcement.
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'club_advisor'])) {
            return response()->json([
                'message' => 'You are not authorized to create announcements.'
            ], 403);
        }

        $validated = $request->validate([
            'club_id' => $user->role === 'club_advisor'
                ? 'required|exists:clubs,id'
                : 'nullable|exists:clubs,id',

            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
            'is_published' => 'sometimes|boolean',
            'published_at' => 'nullable|date',
        ]);

        // Advisors can only publish for clubs assigned to them.
        if (
            $user->role === 'club_advisor' &&
            !Club::where('id', $validated['club_id'])
                ->where('advisor_id', $user->id)
                ->exists()
        ) {
            return response()->json([
                'message' => 'You are not assigned to this club.'
            ], 403);
        }

        $validated['created_by'] = $user->id;

        $announcement = Announcement::create($validated);

        $this->logActivity(
            'created',
            'announcements',
            'Created announcement: ' . $announcement->title
        );

        return response()->json([
            'message' => 'Announcement created successfully.',
            'data' => $announcement->load('club'),
        ], 201);
    }

    // Display one announcement.
    public function show(Request $request, Announcement $announcement)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return response()->json($announcement->load('club'));
        }

        if ($user->role === 'club_advisor') {
            $authorized = $announcement->club_id !== null &&
                Club::where('id', $announcement->club_id)
                    ->where('advisor_id', $user->id)
                    ->exists();
        } elseif ($user->role === 'student') {
            $authorized = $announcement->is_published &&
                (
                    $announcement->club_id === null ||
                    ClubMembership::where('student_id', $user->id)
                        ->where('club_id', $announcement->club_id)
                        ->where('status', 'approved')
                        ->exists()
                );
        } else {
            $authorized = false;
        }

        if (!$authorized) {
            return response()->json([
                'message' => 'Announcement not found.'
            ], 404);
        }

        return response()->json($announcement->load('club'));
    }

    // Update an announcement.
    public function update(Request $request, Announcement $announcement)
    {
        $user = $request->user();

        if (!$this->canManageAnnouncement($user, $announcement)) {
            return response()->json([
                'message' => 'You are not authorized to update this announcement.'
            ], 403);
        }

        $validated = $request->validate([
            'club_id' => $user->role === 'club_advisor'
                ? 'sometimes|required|exists:clubs,id'
                : 'sometimes|nullable|exists:clubs,id',

            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'category' => 'sometimes|nullable|string|max:255',
            'is_published' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
        ]);

        if (
            $user->role === 'club_advisor' &&
            isset($validated['club_id']) &&
            !Club::where('id', $validated['club_id'])
                ->where('advisor_id', $user->id)
                ->exists()
        ) {
            return response()->json([
                'message' => 'You are not assigned to this club.'
            ], 403);
        }

        $announcement->update($validated);

        $this->logActivity(
            'updated',
            'announcements',
            'Updated announcement: ' . $announcement->title
        );

        return response()->json([
            'message' => 'Announcement updated successfully.',
            'data' => $announcement->fresh()->load('club'),
        ]);
    }

    // Delete an announcement.
    public function destroy(Request $request, Announcement $announcement)
    {
        $user = $request->user();

        if (!$this->canManageAnnouncement($user, $announcement)) {
            return response()->json([
                'message' => 'You are not authorized to delete this announcement.'
            ], 403);
        }

        $title = $announcement->title;

        $announcement->delete();

        $this->logActivity(
            'deleted',
            'announcements',
            'Deleted announcement: ' . $title
        );

        return response()->json([
            'message' => 'Announcement deleted successfully.'
        ]);
    }

    // Check whether the user can manage this announcement.
    private function canManageAnnouncement($user, Announcement $announcement)
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'club_advisor') {
            return $announcement->club_id !== null &&
                Club::where('id', $announcement->club_id)
                    ->where('advisor_id', $user->id)
                    ->exists();
        }

        return false;
    }
}
