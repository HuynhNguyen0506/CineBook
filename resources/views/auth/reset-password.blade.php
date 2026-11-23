<div class="">
    <h3>Đặt lại mật khẩu</h3>
</div>
<form action="{{route('password.update')}}" method="POST">
@csrf

    <input
        type="hidden"
        name="token"
        value="{{ $token }}"
    >
    <div>
        <input
            type="email"
            name="email"
            value="{{ old('email', $email) }}"
            readonly
            hidden
        >
    </div>
    <div>
        <label for="">Mật khẩu mới</label>
        <input type="password" name="password">
    </div>
    <div>
        <label for="">Nhập lại mật khẩu mới</label>
        <input type="password" name="password_confirmation">
    </div>
    <div>
        <button type="submit">Lưu</button>
        <a href="{{route('login')}}">Hủy</a>
    </div>
</form>