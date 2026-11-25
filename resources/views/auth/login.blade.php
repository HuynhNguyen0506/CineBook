<x-alert />
<div class="">
    <h3>Đăng nhập</h3>
    <p>Vui lòng đăng nhập để tiếp tục</p>
</div>

<form action="{{route('submitLogin')}}" method="POST">
@csrf
    <div>
        <label for="">Email hoặc Số điện thoại</label>
        <input type="text" name="login">
    </div>
    <div>
        <label for="">Mật khẩu</label>
        <input type="password" name="password">
    </div>
    <div>
        <a href="{{route('password.request')}}">Quên mật khẩu</a>
    </div>

    <button type="submit">Đăng nhập</button>
    <div>
        Chưa có tài khoản <a href="{{route('register')}}">Đăng ký ngay</a>
    </div>
</form>