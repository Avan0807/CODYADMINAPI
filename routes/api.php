<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Appointment;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GetdoctorsController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\TreatmentLogController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiDoctorController;
use App\Http\Controllers\Api\ApiAuthAdminController;
use App\Http\Controllers\Api\ApiAuthController;


// AUTHENTICATION ROUTES
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [RegisterController::class, 'apiRegister']);

// =================== ADMIN DOCTOR MANAGEMENT ===================
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/create-doctor', [ApiDoctorController::class, 'createDoctor']); 
    Route::delete('/delete-doctor/{id}', [ApiDoctorController::class, 'deleteDoctor']);
});

// Login
Route::post('/login', [LoginController::class, 'apiLogin']);
// Doctor login
Route::post('/login/doctor', [LoginController::class, 'apiDoctorLogin']);

// =================== DOCTOR AUTHENTICATION ===================
Route::prefix('doctor')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ApiDoctorController::class, 'doctorLogout']); 
    });
});
// logout users
Route::middleware('auth:sanctum')->post('logout', [ApiAuthController::class, 'logout']);

// admin Logout
Route::post('admin/logout', [ApiAuthAdminController::class, 'logout'])->middleware('auth:sanctum');

// USERS ROUTES
Route::middleware('auth:sanctum')->get('/user/{id}', [UsersController::class, 'apiGetUserById']);
// Upload user avt
Route::post('/user/{userID}/upload-avatar', [UsersController::class, 'apiUploadAvatar']);
Route::get('/user/{userID}/get-avatar', [UsersController::class, 'apiGetAvatarByUserId']);
// Add address
// Add user address
Route::put('/users/{userID}/address', [UsersController::class, 'apiUpdateAddress']);
Route::get('/users/{userID}/getaddress', [UsersController::class, 'apiGetUserByID']);

// Lấy danh sách thông báo của User
Route::middleware('auth:sanctum')->get('/user/{userID}/notifications', [UsersController::class, 'getNotifications']);

// Đánh dấu thông báo đã đọc
Route::middleware('auth:sanctum')->post('/user/notifications/{notificationID}/read', [UsersController::class, 'markNotificationAsRead']);

// Lấy danh sách thông báo chưa đọc
Route::middleware('auth:sanctum')->get('/user/{userID}/notifications/unread', [UsersController::class, 'getUnreadNotifications']);
// Xóa một thông báo
Route::middleware('auth:sanctum')->delete('/user/notifications/{notificationID}', [UsersController::class, 'deleteNotification']);



// DOCTORS ROUTES

// Get list doctor
Route::middleware('auth:sanctum')->get('/doctors', [GetdoctorsController::class, 'apiHome']);
//Get doctors controller
Route::get('/alldoctors', [DoctorsController::class, 'apiGetAllDoctors']);
Route::get('/doctors/{doctorID}', [DoctorsController::class, 'apiGetDoctorsByDoctorId']);
//get user infor by id
Route::middleware('auth:sanctum')->get('/patient-info/{id}', [DoctorsController::class, 'apiGetPatientInfo']);
// Lấy thông báo cho doctor 
Route::middleware('auth:sanctum')->get('/doctor/{doctorID}/notifications', [DoctorsController::class, 'getNotifications']);
// thông báo đã đọc
Route::middleware('auth:sanctum')->post('/doctor/notifications/{notificationID}/read', [DoctorsController::class, 'markNotificationAsRead']);
// thông báo chưa đọc 
Route::middleware('auth:sanctum')->get('/doctor/{doctorID}/notifications/unread', [DoctorsController::class, 'getUnreadNotifications']);
// xóa thông báo
Route::middleware('auth:sanctum')->delete('/doctor/notifications/{notificationID}/delete', [DoctorsController::class, 'deleteNotification']);



// PRODUCT ROUTES
Route::get('/products', [ProductController::class, 'apiGetAllProducts']);
Route::get('/products/{id}', [ProductController::class, 'apiGetProductById']);
Route::post('/productsadd', [ApiProductController::class, 'store']);

// APPOINTMENT ROUTES

//Get all appointment
Route::get('/appointments', [AppointmentsController::class, 'ApiGetAllAppointments']);
// Get appointment by user
Route::get('/appointments/{userID}', [AppointmentsController::class, 'apiGetAppointmentsByUser']);
//Create appointmet by user
Route::post('/appointments/{userID}', [AppointmentsController::class, 'apiCreateAppointment']);
// Get current appointmentappointment
Route::get('/appointments/upcoming/{userID}', [AppointmentsController::class, 'apiGetCurrentAppointments']);
// Get appointment infor by id
Route::middleware('auth:sanctum')->get('/appointment-info/{appointmentID}', [AppointmentsController::class, 'apiGetAppointmentInfo']);


// APPOINTMENT BOOKING
Route::post('appointments/{userID}/create', [AppointmentsController::class, 'apicreateAppointment']);
//Update status
Route::put('/appointments/{appointmentID}/confirm', [AppointmentsController::class, 'apiConfirmAppointment']);
Route::put('/appointments/{appointmentID}/complete', [AppointmentsController::class, 'apiCompleteAppointment']);
Route::put('/appointments/{userID}/{appointmentID}/cancel', [AppointmentsController::class, 'apiCancelAppointment']);

//Get appointmet buy DoctorID
Route::get('/appointments/doctor/{doctorID}/all', [AppointmentsController::class, 'apiGetAllAppointmentsByDoctor']);
Route::get('/appointments/doctor/{doctorID}/recent', [AppointmentsController::class, 'apiGetRecentAppointments']);
Route::delete('/appointments/{appointmentID}/reject', [AppointmentsController::class, 'apiDeleteAppointment']);



// CART ROUTES
Route::get('/cart/{userID}', [CartController::class, 'apiGetUserCart']);
// Add more product to cart
Route::middleware('auth:sanctum')->post('/cart/add', [CartController::class, 'apiAddProductToCart']);
//Remove product by useruser
Route::delete('/cart/{userId}/{productId}', [CartController::class, 'apiRemoveFromCartByUser']);
// Update product quantity
Route::put('/cart/{userId}/{productId}', [CartController::class, 'apiUpdateUserCartQuantity']);


// POST ROUTES
//Posts

Route::middleware('auth:sanctum')->post('/posts/create', [PostController::class, 'apiCreatePost']);

Route::get('/posts', [PostController::class, 'apiGetAllPosts']);
Route::get('/posts/{slug}', [PostController::class, 'apiGetPostBySlug']);


//Post comment
Route::get('/comments/{postId}', [PostCommentController::class, 'getCommentsByPostId']);
Route::middleware('auth:sanctum')->post('/comments/post_comments', [PostCommentController::class, 'apiCreateComment']);
Route::get('/posts/{postId}/comments/{commentId}', [PostCommentController::class, 'apiGetCommentById']);
Route::middleware('auth:sanctum')->put('/posts/{postId}/comments/{commentId}', [PostCommentController::class, 'apiUpdateComment']);



// ORDER ROUTES
// Order Routes
Route::middleware(['auth:sanctum'])->get('/orders', [OrderController::class, 'apiGetUserOrders']);
Route::middleware(['auth:sanctum'])->post('/orders/create', [OrderController::class, 'apiCreateOrder']);

// Order status 
Route::middleware(['auth:sanctum'])->get('/orders/{order_id}/status', [OrderController::class, 'apiGetOrderStatus']);
Route::middleware(['auth:sanctum'])->get('/orders/status', [OrderController::class, 'apiGetUserOrdersStatus']);

// MEDICAL RECORD
//Get medical record by it's Id
Route::middleware('auth:sanctum')->get('/medical-records/{id}', [MedicalRecordController::class, 'apiGetMedicalRecordById']);
// by user Id
Route::middleware('auth:sanctum')->get('/medical-records/user/{userId}', [MedicalRecordController::class, 'apiGetAllMedicalRecordsByUser']);
//Create medical record
Route::middleware('auth:sanctum')->post('/medical-records/create', [MedicalRecordController::class, 'apiCreateMedicalRecord']);
// DeleteDelete
Route::middleware('auth:sanctum')->delete('/medical-records/delete/{id}', [MedicalRecordController::class, 'apiDeleteMedicalRecord']);


// TREATMENT

// Get all treatment by medical record id
Route::middleware('auth:sanctum')->get('/treatment-logs/alltreatment/{medical_record_id}', [TreatmentLogController::class, 'apiGetTreatmentLogsByMedicalRecord']);

// Get treatment by it's id
Route::middleware('auth:sanctum')->get('/treatment-logs/{id}', [TreatmentLogController::class, 'apiGetTreatmentLogById']);
// Create
Route::middleware('auth:sanctum')->post('/treatment-logs/{medical_record_id}/create', [TreatmentLogController::class, 'apiCreateTreatmentLog']);
//Delete
Route::middleware('auth:sanctum')->delete('/treatment-logs/{id}', [TreatmentLogController::class, 'apiDeleteTreatmentLog']);


// =================== NOTIFICATION ROUTES ===================


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [ApiNotificationController::class, 'index']); // Lấy tất cả thông báo
    Route::get('/notifications/unread', [ApiNotificationController::class, 'unread']); // Lấy thông báo chưa đọc
    Route::post('/notifications/read/{id}', [ApiNotificationController::class, 'markAsRead']); // Đánh dấu thông báo đã đọc
    Route::post('/notifications/read-all', [ApiNotificationController::class, 'markAllAsRead']); // Đánh dấu tất cả là đã đọc
    Route::delete('/notifications/delete/{id}', [ApiNotificationController::class, 'delete']); // Xóa thông báo
    Route::delete('/notifications/delete-all', [ApiNotificationController::class, 'deleteAll']); // Xóa tất cả thông báo
});

