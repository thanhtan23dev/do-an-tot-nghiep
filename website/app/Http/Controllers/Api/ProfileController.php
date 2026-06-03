<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * API Cập nhật thông tin cá nhân / Đổi mật khẩu
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('nhan_viens')->ignore($user->id), // Cho phép giữ nguyên SĐT cũ
            ],
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed', // Phải có trường password_confirmation đi kèm
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng bởi người khác.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.'
        ]);

        // 2. Cập nhật thông tin cơ bản
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->address = $request->address;

        // 3. Nếu người dùng có nhập mật khẩu mới thì mới tiến hành đổi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // 4. Trả về kết quả JSON
        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thông tin cá nhân thành công!',
            'data' => $user
        ], 200);
    }
}
