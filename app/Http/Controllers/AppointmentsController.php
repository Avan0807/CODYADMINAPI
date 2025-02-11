<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;


use Illuminate\Support\Facades\Validator;


class AppointmentsController extends Controller
{

    public function apiCreateAppointment(Request $request, $DoctorID)
    {
        $validator = Validator::make($request->all(), [
            'doctorID' => 'required|exists:Doctors,DoctorID',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'consultation_type' => 'required|in:Online,Offline',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $appointment = Appointment::create([
                'userID' => $userID,
                'doctorID' => $request->doctorID,
                'date' => $request->date,
                'time' => $request->time,
                'consultation_type' => $request->consultation_type,
                'note' => $request->note,
                'status' => 'Chờ duyệt',
                'approval_status' => 'Chờ duyệt',
                'workflow_stage' => 'Requested',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu đặt lịch khám đã được gửi.',
                'appointment' => $appointment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi yêu cầu đặt lịch khám.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Lấy danh sách tất cả các cuộc hẹn.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAllAppointments()
    {
        try {
            // Lấy danh sách tất cả các cuộc hẹn
            $appointments = Appointment::all();

            return response()->json([
                'success' => true,
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách cuộc hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function apiGetAppointmentsByUser($userID)
    {
        try {
            $appointments = Appointment::where('userID', $userID)->get();

            if ($appointments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy cuộc hẹn nào cho userID này.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách cuộc hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiGetCurrentAppointments($userID)
    {
        try {
            // Lấy 5 lịch khám gần nhất, sắp xếp theo ngày và giờ
            $appointments = Appointment::where('userID', $userID)
                ->whereDate('date', '<=', now()->format('Y-m-d')) // Lọc lịch khám từ hôm nay
                ->orderBy('date', 'asc') // Sắp xếp theo ngày tăng dần
                ->orderBy('time', 'asc') // Sắp xếp theo giờ tăng dần nếu cùng ngày
                ->limit(5) // Lấy tối đa 5 lịch khám
                ->get();

            if ($appointments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy lịch khám sắp tới nào cho user này.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách lịch khám sắp tới thành công.',
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách lịch khám sắp tới.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function apiCancelAppointment(Request $request, $userID, $appointmentID)
    {
        try {
            // Tìm lịch khám dựa trên userID và appointmentID
            $appointment = Appointment::where('userID', $userID)
                ->where('appointmentID', $appointmentID)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy lịch khám.',
                ], 404);
            }

            // Cập nhật trạng thái thành "Cancelled"
            $appointment->status = 'Đã hủy';
            $appointment->save();

            return response()->json([
                'success' => true,
                'message' => 'Lịch khám đã được hủy thành công.',
                'appointment' => $appointment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy lịch khám.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Xác nhận lịch hẹn
    public function apiConfirmAppointment($appointmentID, Request $request)
    {
        try {
            $appointment = Appointment::where('appointmentID', $appointmentID)->firstOrFail();

            if ($appointment->status !== 'Chờ duyệt' || $appointment->approval_status !== 'Chờ duyệt') {
                return response()->json(['message' => 'Lịch hẹn không ở trạng thái chờ duyệt.'], 400);
            }

            $appointment->update([
                'status' => 'Sắp tới',
                'approval_status' => 'Chấp nhận'
            ]);

            return response()->json([
                'message' => 'Lịch hẹn đã được xác nhận thành công.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xác nhận lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // Hoàn thành lịch hẹn
    public function apiCompleteAppointment($appointmentID, Request $request)
    {
        try {
            $appointment = Appointment::where('appointmentID', $appointmentID)->firstOrFail();

            if ($appointment->status !== 'Sắp tới') {
                return response()->json(['message' => 'Lịch hẹn không ở trạng thái sắp tới.'], 400);
            }

            $appointment->update(['status' => 'Hoàn thành']);

            return response()->json([
                'message' => 'Lịch hẹn đã được hoàn thành.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi hoàn thành lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // Lấy 5 lịch hẹn gần nhất của bác sĩ
    public function apiGetRecentAppointments($doctorID)
    {
        try {
            $appointments = Appointment::where('doctorID', $doctorID)
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->limit(5)
                ->get();

            return response()->json([
                'message' => 'Lấy 5 lịch hẹn gần nhất thành công.',
                'appointments' => $appointments
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi lấy lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // Lấy toàn bộ lịch hẹn của bác sĩ
    public function apiGetAllAppointmentsByDoctor($doctorID)
    {
        try {
            $appointments = Appointment::where('doctorID', $doctorID)
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            return response()->json([
                'message' => 'Lấy tất cả lịch hẹn thành công.',
                'appointments' => $appointments
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi lấy lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // Xóa lịch hẹn
    public function apiDeleteAppointment($appointmentID)
    {
        try {
            $appointment = Appointment::where('appointmentID', $appointmentID)->first();

            if (!$appointment) {
                return response()->json(['message' => 'Lịch hẹn không tồn tại.'], 404);
            }

            $appointment->delete();

            return response()->json(['message' => 'Lịch hẹn đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // index ra danh sách bệnh nhân
    public function getAllAppointmentsForDoctors()
    {
        try {
            // Lấy ID của bác sĩ đang đăng nhập
            $doctorID = auth()->user()->id;

            // Lọc lịch hẹn chỉ dành cho bác sĩ này
            $appointments = Appointment::where('doctorID', $doctorID)
                ->with(['user', 'doctor']) // Load thêm thông tin bệnh nhân & bác sĩ
                ->orderBy('date', 'asc') // Sắp xếp theo ngày
                ->orderBy('time', 'asc')
                ->paginate(10);

            return view('doctor.patients.index', compact('appointments'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Không thể lấy danh sách lịch hẹn.');
        }
    }

    public function apiUpdateStatus(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            // Chỉ cập nhật approval_status
            $appointment->update([
                'approval_status' => $request->approval_status
            ]);

            return redirect()->back()->with('success', 'Trạng thái phê duyệt đã được cập nhật!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái.');
        }
    }

    public function apiGetAppointmentInfo($appointmentID, Request $request)
    {

        $user = $request->user();

        $appointment = Appointment::find($appointmentID);
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch khám này.',
            ], 404); // Lỗi 404 - Not Found
        }

        // . Kiểm tra quyền truy cập:
        // - Nếu là bác sĩ: kiểm tra xem bác sĩ này có trong lịch khám không (so sánh doctor_id)
        // - Nếu là bệnh nhân: kiểm tra xem bệnh nhân này có trong lịch khám không (so sánh user_id)
        if ($appointment->doctor_id) {
            // Kiểm tra xem người dùng hiện tại có phải là bác sĩ hay không
            $doctor = Doctor::where('doctorID', $user->id)->first();  // Lấy thông tin bác sĩ từ bảng Doctors

            if ($doctor) {
                // Bác sĩ chỉ có thể xem lịch khám của bệnh nhân nếu doctor_id trong bảng appointments trùng với doctor_id của bác sĩ
                if ($appointment->doctor_id !== $doctor->doctorID) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không có quyền truy cập thông tin lịch khám này!',
                    ], 403);
                }
            }
        }
        if ($user->id === $appointment->user_id) {
            // Bệnh nhân chỉ có thể xem lịch khám của chính mình
            return response()->json([
                'success' => true,
                'data' => $appointment,
                'message' => 'Thông tin lịch khám đã được lấy thành công!',
            ], 200); // Mã 200 - OK
        }

        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền truy cập thông tin lịch khám này!',
        ], 403); // Lỗi 403 - Forbidden
    }
}
