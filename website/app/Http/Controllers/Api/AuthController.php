<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NhanVien;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * API Đăng nhập cho App Flutter
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'password' => 'required|min:6',
        ]);

        $nhanVien = NhanVien::where('phone', $request->phone)->first();

        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'message' => 'Số điện thoại không tồn tại trong hệ thống.'
            ], 404);
        }

        if ($nhanVien->is_active == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'
            ], 403);
        }

        if (!Hash::check($request->password, $nhanVien->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không chính xác.'
            ], 401);
        }

        if ($request->has('fcm_token') && $request->fcm_token != null) {
            $nhanVien->fcm_token = $request->fcm_token;
            $nhanVien->save();
        }

        $token = $nhanVien->createToken('FlutterApp')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $nhanVien->id,
                    'name' => $nhanVien->name,
                    'phone' => $nhanVien->phone,
                    'role' => $nhanVien->role,
                ]
            ]
        ], 200);
    }

    /**
     * API Lấy thông tin user hiện tại (Để Flutter kiểm tra token còn sống không lúc mở app)
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ], 200);
    }

    /**
     * API Đăng xuất (Thu hồi Token)
     */
    public function logout(Request $request)
    {

        $user = $request->user();

        if ($user->fcm_token) {
            $user->fcm_token = null;
            $user->save();
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ], 200);
    }


    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|numeric|unique:nhan_viens,phone',
                'password' => 'required|string|min:6',
            ], [
                'phone.required' => 'Vui lòng nhập số điện thoại.',
                'phone.numeric' => 'Số điện thoại chỉ được chứa chữ số.',
                'phone.unique' => 'Số điện thoại này đã được đăng ký.',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.'
            ]);

            $nhanVien = new NhanVien();
            $nhanVien->name = $request->name;
            $nhanVien->phone = $request->phone;
            $nhanVien->password = Hash::make($request->password);
            
            $nhanVien->role = 3; 
            $nhanVien->is_active = 1; 
            
            $nhanVien->save();

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 400);
        }
    }

    public function deleteAccount(Request $request)
    {
        $nhanVien = $request->user();

        if (!$nhanVien) {
            return response()->json([
                'success' => false, 
                'message' => 'Không tìm thấy thông tin tài khoản.'
            ], 404);
        }

        if ($nhanVien->role == 3) {
            $nhanVien->tokens()->delete();
            
            $nhanVien->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tài khoản của bạn đã được xóa vĩnh viễn.'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tài khoản nội bộ không được phép tự xóa. Vui lòng liên hệ Quản lý xưởng Tâm An.'
        ], 403);
    }

    public function updateProfile(Request $request)
    {
    $nhanVien = $request->user();
    
    $request->validate([
        'name' => 'required|string|max:255',
        'password' => 'nullable|string|min:6',
    ]);

    $nhanVien->name = $request->name;
    if ($request->password) {
        $nhanVien->password = Hash::make($request->password);
    }
    $nhanVien->save();

    return response()->json([
        'success' => true,
        'message' => 'Cập nhật thông tin thành công.',
        'data' => [
            'name' => $nhanVien->name,
            'phone' => $nhanVien->phone,
        ]
    ]);
    }
}
