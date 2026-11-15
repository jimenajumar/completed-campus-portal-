<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    // Standard room types and their accepted variations
    private function normalizeRoomType(string $roomType): string
    {
        $key = preg_replace('/[^a-z]/', '', strtolower(trim($roomType)));

        $roomTypes = [
            'aircon' => 'Air Conditioning',
            'airconditioning' => 'Air Conditioning',
            'aiconditioning' => 'Air Conditioning',

            'classroom' => 'Classroom',
            'classroom' => 'Classroom',

            'lectureroom' => 'Lecture Room',
            'lecturehall' => 'Lecture Room',

            'computerlab' => 'Computer Laboratory',
            'computerlaboratory' => 'Computer Laboratory',

            'sciencelab' => 'Science Laboratory',
            'sciencelaboratory' => 'Science Laboratory',

            'conferenceroom' => 'Conference Room',
            'meetingroom' => 'Conference Room',

            'audiovisualroom' => 'Audio Visual Room',
            'avr' => 'Audio Visual Room',

            'library' => 'Library',
            'readingroom' => 'Library',

            'gym' => 'Gymnasium',
            'gymnasium' => 'Gymnasium',

            'multipurposehall' => 'Multipurpose Hall',
            'multipurposeroom' => 'Multipurpose Hall',
        ];

        if (!isset($roomTypes[$key])) {
            throw ValidationException::withMessages([
                'room_type' => ['Please select a valid room type.'],
            ]);
        }

        return $roomTypes[$key];
    }

    // GET: Display all rooms
    public function index()
    {
        $rooms = Room::latest()->get();

        return response()->json([
            'message' => 'Rooms retrieved successfully.',
            'data' => $rooms
        ], 200);
    }

    // POST: Create a room
    public function store(Request $request)
    {
        $request->merge([
            'room_type' => $this->normalizeRoomType(
                $request->input('room_type', '')
            )
        ]);

        $validated = $request->validate([
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'room_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:0',
            'room_type' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $room = Room::create($validated);

        $this->logActivity(
            'created',
            'rooms',
            'Created room: ' . $room->room_number
        );

        return response()->json([
            'message' => 'Room created successfully.',
            'data' => $room
        ], 201);
    }

    // GET: Display one room
    public function show(Room $room)
    {
        return response()->json([
            'message' => 'Room retrieved successfully.',
            'data' => $room
        ], 200);
    }

    // PUT/PATCH: Update a room
    public function update(Request $request, Room $room)
    {
        if ($request->has('room_type')) {
            $request->merge([
                'room_type' => $this->normalizeRoomType(
                    $request->input('room_type')
                )
            ]);
        }

        $validated = $request->validate([
            'room_number' => 'sometimes|required|string|max:255|unique:rooms,room_number,' . $room->id,
            'room_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'sometimes|required|integer|min:0',
            'room_type' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $room->update($validated);

        $this->logActivity(
            'updated',
            'rooms',
            'Updated room: ' . $room->room_number
        );

        return response()->json([
            'message' => 'Room updated successfully.',
            'data' => $room->fresh()
        ], 200);
    }

    // DELETE: Remove a room
    public function destroy(Room $room)
    {
        $roomNumber = $room->room_number;

        $room->delete();

        $this->logActivity(
            'deleted',
            'rooms',
            'Deleted room: ' . $roomNumber
        );

        return response()->json([
            'message' => 'Room deleted successfully.'
        ], 200);
    }
}
