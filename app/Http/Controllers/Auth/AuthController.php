<?php

namespace App\Http\Controllers\Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{

//// ĐĂNG NHẬP TÀI KHOẢN
    public function login(){
        return view('auth.login');
    }

    public function logout(){
        return view('auth.login');
    }

    public function submitLogin(Request $request){
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ], [
            'login.required' => 'Vui lòng nhập email hoặc số diện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = User::where('email', $request->login) -> orWhere('phone', $request->login) -> first();
        if ($user && Hash::check($request->password, $user->password)) {
            $request -> session() -> regenerate();
            session([
                'id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'username' => $user->username,
            ]);
            return redirect()->route('home') -> with('success', 'Đăng nhập thành công!');
        } else {
            return redirect()->route('login') ->with('error', 'Tên đăng nhập/Email hoặc mật khẩu không chính xác.') ->withInput();
        }
    }


//// QUÊN MẬT KHẨU
    public function passRequest(){
        return view('auth.forgot-password');
    }

    public function passEmail(Request $request){
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'Vui lòng nhập email.',
        'email.email' => 'Email không đúng định dạng.',
        'email.exists' => 'Email này chưa được đăng ký.',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

        if ($status === Password::RESET_LINK_SENT) {
        return back()->with(
            'success',
            'Link đặt lại mật khẩu đã được gửi đến email của bạn.'
        );
    }

    return back()->withErrors([
        'email' => 'Không thể gửi email. Vui lòng thử lại.',
    ]);
    
    }

    public function passReset(Request $request, $token){
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function passUpdate(Request $request){
        $request->validate([
            'token' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);
        
        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Đặt lại mật khẩu thành công!');
        }   

        return back()->withErrors([
            'email' => 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
        ]);
    }

////ĐĂNG KÝ TÀI KHOẢN
        public function register(){
        return view('auth.register');
    }

    public function submitRegister(Request $request){
        $validatedData = $request->validate([
            'password' => 'required|string|min:6|confirmed',

            'phone' => [
                'required',
                'string',
                'regex:/^0[0-9]{9}$/',
                'unique:users,phone',
            ],

            'email' => 'required|string|email|max:255|unique:users,email',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
            'email.unique' => 'Email này đã được đăng ký.',
        ]);


        $user = new User();

        $user -> name = $request -> name;
        $user -> phone = $validatedData['phone'];
        $user -> email = $validatedData['email'];
        $user -> role = 'user';
        $user -> password = Hash::make($validatedData['password']);

        $user -> save();

        return redirect()->route('login') ->with('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');

    }
}
