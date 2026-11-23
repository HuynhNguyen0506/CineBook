<div class="">
    <h3>Xác minh tài khoản</h3>
</div>

<form action="{{route('password.email')}}" method="POST">
@csrf
    <div>
        <label for="">Email tài khoản</label>
        <input type="text" name="email">
    </div>
    <div>
        <button type="submit">Tiếp</button>
        <a href="{{route('login')}}">Hủy</a>
    </div>
</form>