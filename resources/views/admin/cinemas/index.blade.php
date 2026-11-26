<h3>Rạp phim</h3>
<fieldset>
    <legend>Filter</legend>
    Keyword: <input type="text">
</fieldset>
<table>
    <tr>
        <td>ID</td>
        <td>Slug</td>
        <td>Tên rạp</td>
        <td>Thành phố</td>
        <td>Địa chỉ</td>
        <td>Số điện thoại</td>
        <td>Email</td>
        <td>Trạng thái</td>
        <td>Ngày thay đổi</td>
    </tr>
    @foreach ($cinemas as $c)
    <tr>
        <td>{{$c -> id}}</td>
        <td>{{$c -> slug}}</td>
        <td>{{$c -> name}}</td>
        <td>{{$c -> city}}</td>
        <td>{{$c -> address}}</td>
        <td>{{$c -> phone}}</td>
        <td>{{$c -> email}}</td>
        <td>{{$c -> status}}</td>
        <td>{{$c -> updated_at}}</td>

        <a href="#">Thêm</a>
        <a href="#">Sửa</a>
        <a href="#">Xóa</a>
    </tr>
    @endforeach
</table>