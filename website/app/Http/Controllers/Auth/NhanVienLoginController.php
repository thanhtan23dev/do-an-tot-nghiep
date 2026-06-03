<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\NhanVien;

class NhanVienLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('nhan_vien')->check()) {
            return redirect()->route('nhanvien.dashboard');
        }
        return view('auth.nhanvien_login');
    }

public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'password' => 'required|min:6',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.numeric' => 'Số điện thoại không hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
        ]);

        $nhanVien = NhanVien::where('phone', $request->phone)->first();
        if (!$nhanVien) {
            return back()->withErrors([
                'phone' => 'Số điện thoại không tồn tại trong hệ thống.',
            ])->onlyInput('phone');
        }
        if ($nhanVien->is_active == 0) {
            return back()->withErrors([
                'phone' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ])->onlyInput('phone');
        }
        if (!Hash::check($request->password, $nhanVien->password)) {
            return back()->withErrors([
                'password' => 'Mật khẩu không chính xác.',
            ])->onlyInput('phone');
        }
        Auth::guard('nhan_vien')->login($nhanVien, $request->boolean('remember'));
        
        $request->session()->regenerate();

        return redirect()->intended(route('nhanvien.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('nhan_vien')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('nhanvien.login');
    }
}
