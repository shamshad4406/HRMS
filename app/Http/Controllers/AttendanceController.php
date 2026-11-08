<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\PersonalInformation;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendances.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        
        // Role-based access control
        if ($user->role === 'admin') {
            $attendances = Attendance::with('personalInformation')->orderBy('attendance_date', 'desc')->paginate(10);
            $totalAttendance = Attendance::count();
            $totalLate = Attendance::where('status', 'late')->count();
        } else {
            // HR and Employee can only see their own attendance
            if ($user->personal_information_id) {
                $attendances = Attendance::with('personalInformation')
                    ->where('personal_information_id', $user->personal_information_id)
                    ->orderBy('attendance_date', 'desc')
                    ->paginate(10);
                $totalAttendance = Attendance::where('personal_information_id', $user->personal_information_id)->count();
                $totalLate = Attendance::where('personal_information_id', $user->personal_information_id)->where('status', 'late')->count();
            } else {
                // User not linked to any employee record
                $attendances = Attendance::where('id', 0)->paginate(10); // Empty result
                $totalAttendance = 0;
                $totalLate = 0;
            }
        }
        
        // Calculate average working hours
        $attendancesWithCheckout = Attendance::whereNotNull('check_in')->whereNotNull('check_out');
        if ($user->role !== 'admin') {
            // Non-admins only ever see their own numbers (0 when not linked to an employee record)
            $attendancesWithCheckout = $attendancesWithCheckout->where('personal_information_id', $user->personal_information_id ?? 0);
        }
        $attendancesWithCheckout = $attendancesWithCheckout->get();
        
        $totalHours = 0;
        $count = 0;
        
        foreach ($attendancesWithCheckout as $attendance) {
            $checkIn = strtotime($attendance->check_in);
            $checkOut = strtotime($attendance->check_out);
            $diff = ($checkOut - $checkIn) / 3600; // Convert to hours
            $totalHours += $diff;
            $count++;
        }
        
        $averageHours = $count > 0 ? round($totalHours / $count, 2) : 0;
        
        return view('attendance_list', compact('attendances', 'totalAttendance', 'totalLate', 'averageHours'));
    }

    /**
     * Show the form for creating a new attendance.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = auth()->user();
        
        // Role-based access control
        if ($user->role === 'admin') {
            $employees = PersonalInformation::all();
        } else {
            // HR and Employee can only see their own record
            $employees = PersonalInformation::where('id', $user->personal_information_id)->get();
        }
        
        return view('add_attendance', compact('employees'));
    }

    /**
     * Store a newly created attendance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Validate that non-admin users can only mark attendance for themselves
        if ($user->role !== 'admin') {
            if ($request->personal_information_id != $user->personal_information_id) {
                return redirect()->back()->with('error', 'You can only mark attendance for yourself.');
            }
        }

        $validated = $request->validate([
            'personal_information_id' => 'required|exists:personal_information,id',
            'attendance_date' => 'nullable|date',
            'status' => 'nullable|in:present,absent,late,half-day,leave',
            'captured_face' => 'nullable|string',
            'face_match_score' => 'nullable|numeric',
        ]);

        // Auto-set check-in time to current time
        $currentTime = now()->format('H:i:s');

        // Auto-set attendance date to today
        $attendanceDate = now()->format('Y-m-d');

        // Auto-set status based on current time if not provided
        if (empty($validated['status']) || !in_array($validated['status'], ['absent', 'leave'])) {
            $currentHour = now()->hour;
            $currentMinute = now()->minute;
            $totalMinutes = ($currentHour * 60) + $currentMinute;
            $thresholdMinutes = 11 * 60; // 11:00 AM in minutes

            if ($totalMinutes < $thresholdMinutes) {
                $validated['status'] = 'present';
            } else {
                $validated['status'] = 'late';
            }
        }

        // Validate face match score if provided
        if (isset($validated['face_match_score']) && $validated['face_match_score'] < 30) {
            return redirect()->back()->with('error', 'Face verification failed. Match score is below 30%. Please try again.');
        }

        // Only one check-in per employee per day
        if (Attendance::where('personal_information_id', $validated['personal_information_id'])
            ->whereDate('attendance_date', $attendanceDate)
            ->exists()) {
            return redirect()->route('attendances.index')->with('error', 'Attendance is already marked for today.');
        }

        Attendance::create([
            'personal_information_id' => $validated['personal_information_id'],
            'attendance_date' => $attendanceDate,
            'check_in' => $currentTime,
            'check_out' => null,
            'status' => $validated['status'],
            'captured_face' => $validated['captured_face'] ?? null,
            'face_match_score' => $validated['face_match_score'] ?? null,
        ]);

        return redirect()->route('attendances.index')->with('success', 'Attendance recorded successfully!');
    }

    /**
     * Check out the specified attendance.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout($id)
    {
        $attendance = Attendance::find($id);
        if ($attendance) {
            $user = auth()->user();
            if ($user->role !== 'admin' && (int) $attendance->personal_information_id !== (int) $user->personal_information_id) {
                abort(403, 'Unauthorized action.');
            }

            // Check if already checked out
            if ($attendance->check_out) {
                return redirect()->route('attendances.index')->with('error', 'Employee already checked out.');
            }
            
            // Set check-out time to current time
            $attendance->check_out = now()->format('H:i:s');
            $attendance->save();
            
            return redirect()->route('attendances.index')->with('success', 'Check out recorded successfully!');
        }
        return redirect()->route('attendances.index')->with('error', 'Attendance not found.');
    }

    /**
     * Remove the specified attendance from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $attendance = Attendance::find($id);
        if ($attendance) {
            $attendance->delete();
            return redirect()->route('attendances.index')->with('success', 'Attendance deleted successfully!');
        }
        return redirect()->route('attendances.index')->with('error', 'Attendance not found.');
    }

    /**
     * Get employee photo for face verification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeePhoto($id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && (int) $id !== (int) $user->personal_information_id) {
            abort(403, 'Unauthorized action.');
        }

        $employee = PersonalInformation::find($id);
        if (!$employee || !$employee->photo) {
            return response()->json(['photo' => null], 404);
        }
        return response()->json(['photo' => asset('storage/' . $employee->photo)]);
    }
}
