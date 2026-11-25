<x-alert />
<div class="">
    <h3>Đăng ký</h3>
    <p>Tạo tài khoản mới</p>
</div>

<form action="{{route('submitRegister')}}" method="POST">
@csrf
    <div>
        <label for="">Họ và tên</label>
        <input type="text" name="name">
    </div>
    <div>
        <label for="">Số điện thoại</label>
        <input type="text" name="phone">
    </div>
    <div>
        <label for="">Email</label>
        <input type="email" name="email">
    </div>
    <div>
        <label for="">Mật khẩu</label>
        <input type="password" name="password">
    </div>
    <div>
        <label for="">Xác nhận mật khẩu</label>
        <input type="password" name="password_confirmation">
    </div>

    <button type="submit">Đăng ký</button>
    <div>
        Đã có tài khoản <a href="{{route('login')}}">Đăng nhập ngay</a>
    </div>
</form>