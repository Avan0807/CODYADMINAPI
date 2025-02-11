<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewContract;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'ASC')->paginate(10);
        return view('backend.users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): ViewContract
    {
        return view('backend.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'string|required|max:30',
                'email' => 'string|required|unique:email',
                'password' => 'string|required',
                'role' => 'required|in:admin,user,doctor',
                'status' => 'required|in:active,inactive',
                'photo' => 'nullable|string',
            ]
        );
        // dd($request->all());
        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        // dd($data);
        $status = User::create($data);
        // dd($status);
        if ($status) {
            request()->session()->flash('success', 'Người dùng đã được thêm thành công');
        } else {
            request()->session()->flash('error', 'Đã xảy ra lỗi khi thêm người dùng');
        }
        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('backend.users.edit')->with('user', $user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate(
            $request,
            [
                'name' => 'string|required|max:30',
                'email' => 'string|required|unique:email',
                'role' => 'required|in:admin,user,doctor',
                'status' => 'required|in:active,inactive',
                'photo' => 'nullable|string',
            ]
        );
        // dd($request->all());
        $data = $request->all();
        // dd($data);

        $status = $user->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Đã cập nhật thành công');
        } else {
            request()->session()->flash('error', 'Đã xảy ra lỗi khi cập nhật');
        }
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = User::findorFail($id);
        $status = $delete->delete();
        if ($status) {
            request()->session()->flash('success', 'Người dùng đã bị xóa thành công');
        } else {
            request()->session()->flash('error', 'Có lỗi khi xóa người dùng');
        }
        return redirect()->route('users.index');
    }

    public function apiGetUserByID($id)
    {
        try {
            // Tìm user theo userID
            $user = User::find($id);

            // Kiểm tra nếu user không tồn tại
            if (!$user) {
                return response()->json([
                    'message' => 'User not found.'
                ], 404);
            }

            return response()->json([
                'message' => 'User retrieved successfully.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiuploadAvatar(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if (!$request->hasFile('photo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng gửi file ảnh.',
                ], 400);
            }

            $file = $request->file('photo');
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Định dạng file không được hỗ trợ.',
                ], 400);
            }

            if ($file->getSize() > 2097152) { // 2MB
                return response()->json([
                    'success' => false,
                    'message' => 'Kích thước file vượt quá 2MB.',
                ], 400);
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = Str::slug($originalName);
            $fileName = $sanitizedName . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads/photos', $fileName, 'public');
            $fileUrl = asset('storage/' . $filePath);

            if ($user->photo) {
                $oldPath = str_replace(asset('storage/'), '', $user->photo);
                Storage::delete('public/' . $oldPath);
            }

            $user->photo = $fileUrl;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật ảnh đại diện thành công.',
                'url' => $fileUrl,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải ảnh.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function apigetAvatarByUserId($id)
    {
        try {
            // Tìm người dùng dựa trên ID
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy người dùng với ID {$id}.",
                ], 404);
            }

            // Kiểm tra nếu người dùng không có ảnh
            if (!$user->photo) {
                return response()->json([
                    'success' => false,
                    'message' => "Người dùng với ID {$id} chưa có ảnh đại diện.",
                ], 404);
            }

            // Trả về URL ảnh đại diện
            return response()->json([
                'success' => true,
                'message' => 'Lấy ảnh đại diện thành công.',
                'photo_url' => $user->photo,
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy ảnh đại diện.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function apiupdateAddress(Request $request, $id)
    {
        try {
            // Xác thực dữ liệu
            $request->validate([
                'address' => 'required|string|max:255',
            ], [
                'address.required' => 'Địa chỉ không được để trống.',
                'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            ]);

            // Tìm user theo userID
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 404);
            }

            // Cập nhật địa chỉ
            $user->address = $request->address;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật địa chỉ thành công.',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật địa chỉ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
