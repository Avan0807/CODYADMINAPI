<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TreatmentLog;
use App\Models\MedicalRecord;
use App\Models\Doctor;


class TreatmentLogController extends Controller
{
    public function apiGetTreatmentLogsByMedicalRecord($medical_record_id, Request $request)
    {
        // Kiểm tra nếu người dùng đã đăng nhập và lấy thông tin người dùng
        $user = $request->user(); // Lấy thông tin người dùng từ token (cả doctor và user)

        // Kiểm tra xem người dùng có phải bác sĩ hay bệnh nhân
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải đăng nhập để xem thông tin bệnh án!',
            ], 401); // Lỗi 401 - Unauthorized
        }

        // Tìm tất cả bản ghi điều trị cho medical_record_id
        $treatmentLogs = TreatmentLog::where('medical_record_id', $medical_record_id)
            ->orderBy('date', 'desc')
            ->get();

        // Kiểm tra nếu không tìm thấy bản ghi điều trị
        if ($treatmentLogs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi điều trị cho bệnh án này.',
            ], 404); // Lỗi 404 - Not Found
        }

        // Trả về danh sách điều trị
        return response()->json([
            'success' => true,
            'data' => $treatmentLogs,
            'message' => 'Danh sách điều trị được lấy thành công!',
        ], 200); // Mã 200 - OK
    }

    public function apiGetTreatmentLogById($id)
    {

        $treatmentLog = TreatmentLog::find($id);

        // Kiểm tra nếu không tìm thấy bản ghi điều trị
        if (!$treatmentLog) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi điều trị này.',
            ], 404); // Lỗi 404 - Not Found
        }

        // Trả về thông tin chi tiết bản ghi điều trị
        return response()->json([
            'success' => true,
            'data' => $treatmentLog,
            'message' => 'Thông tin bản ghi điều trị được lấy thành công!',
        ], 200); // Mã 200 - OK
    }


    public function apiCreateTreatmentLog(Request $request, $medical_record_id)
    {
        // Lấy thông tin bác sĩ từ token (đã đăng nhập với Sanctum)
        $doctor = $request->user();  // Lấy thông tin bác sĩ từ token

        // Kiểm tra nếu không có thông tin bác sĩ
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ bác sĩ mới có quyền thêm log điều trị!',
            ], 403); // Lỗi 403 - Forbidden
        }

        // Kiểm tra nếu doctorID trong bảng Doctors trùng với ID của người đăng nhập
        $doctorRecord = Doctor::find($doctor->doctorID);

        // Nếu không tìm thấy bác sĩ trong bảng Doctors hoặc ID không khớp
        if (!$doctorRecord || $doctorRecord->doctorID != $doctor->doctorID) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc bác sĩ không có quyền thao tác!',
            ], 403); // Lỗi 403 - Forbidden
        }

        // Kiểm tra xem bệnh án có tồn tại không
        $medicalRecord = MedicalRecord::find($medical_record_id);
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bệnh án này!',
            ], 404); // Lỗi 404 - Not Found
        }

        // Xác thực dữ liệu đầu vào từ request
        $validated = $request->validate([
            'description' => 'required|string|max:1000', // Mô tả điều trị là bắt buộc
            'date' => 'required|date', // Ngày điều trị là bắt buộc
        ]);

        // Tạo log điều trị mới
        try {
            $treatmentLog = TreatmentLog::create([
                'medical_record_id' => $medical_record_id, // Lưu medical_record_id
                'description' => $validated['description'], // Mô tả điều trị
                'date' => $validated['date'], // Ngày điều trị
            ]);

            return response()->json([
                'success' => true,
                'data' => $treatmentLog,
                'message' => 'Log điều trị đã được tạo thành công!',
            ], 201); // Mã 201 - Created
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo log điều trị!',
                'error' => $e->getMessage(),
            ], 500); // Lỗi 500 - Server Error
        }
    }


    public function apiDeleteTreatmentLog($id, Request $request)
    {
        // Lấy thông tin bác sĩ từ token
        $doctor = $request->user();

        // Kiểm tra nếu không có thông tin bác sĩ
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ bác sĩ mới có quyền xóa log điều trị!',
            ], 403); // Lỗi 403 - Forbidden
        }

        // Tìm bản ghi log điều trị theo ID
        $treatmentLog = TreatmentLog::find($id);

        // Kiểm tra nếu không tìm thấy bản ghi log điều trị
        if (!$treatmentLog) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi điều trị này.',
            ], 404); // Lỗi 404 - Not Found
        }

        // Kiểm tra xem bệnh án (medical_record) có tồn tại và được tạo bởi bác sĩ này không
        $medicalRecord = MedicalRecord::find($treatmentLog->medical_record_id);

        // Kiểm tra nếu không tìm thấy bệnh án
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bệnh án này!',
            ], 404); // Lỗi 404 - Not Found
        }

        // Kiểm tra nếu bác sĩ không phải là người tạo bệnh án này
        if ($medicalRecord->doctor_id != $doctor->doctorID) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa log điều trị này!',
            ], 403); // Lỗi 403 - Forbidden
        }

        // Xóa bản ghi log điều trị
        try {
            $treatmentLog->delete();

            return response()->json([
                'success' => true,
                'message' => 'Log điều trị đã được xóa thành công!',
            ], 200); // Mã 200 - OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa log điều trị!',
                'error' => $e->getMessage(),
            ], 500); // Lỗi 500 - Server Error
        }
    }
}
