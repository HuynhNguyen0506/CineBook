<<<<<<< HEAD


## 1. Công nghệ sử dụng

| Công nghệ | Phiên bản / Công cụ |
|---|---|
| Backend | Laravel 12 |
| Ngôn ngữ | PHP |
| Database | MySQL 8+ |
| Database Management | phpMyAdmin |
| Local Server | XAMPP |
| Frontend | HTML, CSS, Bootstrap, JavaScript |
| Version Control | Git, GitHub |
| Code Editor | Visual Studio Code |

## 2. Yêu cầu hệ thống

Trước khi cài đặt dự án, cần chuẩn bị:

- PHP tương thích với Laravel 12.
- Composer.
- MySQL 8 trở lên.
- XAMPP.
- Git.
- Visual Studio Code.
- Trình duyệt web.

Kiểm tra PHP:

```bash
php -v
```

Kiểm tra Composer:

```bash
composer -V
```

Kiểm tra Git:

```bash
git --version
```

## 3. Cấu trúc dự án

```text
CineBook/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Admin/
│   │       ├── Auth/
│   │       └── Client/
│   ├── Models/
│   └── Services/
│
├── bootstrap/
├── config/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── public/
│   ├── css/
│   ├── js/
│   └── images/
│
├── resources/
│   └── views/
│       ├── admin/
│       ├── client/
│       ├── auth/
│       ├── components/
│       └── layouts/
│
├── routes/
│   ├── web.php
│   ├── admin.php
│   ├── client.php
│   └── auth.php
│
├── tests/
├── .env.example
├── artisan
├── composer.json
└── README.md
```

*Lưu ý: Một số thư mục và file sẽ được tạo trong quá trình phát triển dự án.*

## 4. Cài đặt dự án trên máy tính

### Bước 1: Clone source code

Mở Terminal và chạy:

```bash
git clone <GITHUB_REPOSITORY_URL>
```

Di chuyển vào thư mục dự án:

```bash
cd CineBook
```

### Bước 2: Cài đặt thư viện

```bash
composer install
```

### Bước 3: Tạo file môi trường

Sao chép `.env.example` thành `.env`:

```bash
copy .env.example .env
```

Tạo APP_KEY:

```bash
php artisan key:generate
```

### Bước 4: Tạo database bằng phpMyAdmin

1. Mở XAMPP Control Panel.
2. Nhấn Start tại MySQL.
3. Truy cập http://localhost/phpmyadmin.
4. Chọn New.
5. Tạo database có tên:

```text
cinebook
```

6. Chọn Collation:

```text
utf8mb4_unicode_ci
```

7. Nhấn Create.

### Bước 5: Cấu hình kết nối MySQL

Mở file `.env` và cấu hình:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cinebook
DB_USERNAME=root
DB_PASSWORD=
```

Nếu MySQL trên máy có mật khẩu, hãy nhập mật khẩu thực tế vào `DB_PASSWORD`.

### Bước 6: Làm mới cấu hình

```bash
php artisan config:clear
```

### Bước 7: Chạy Migration

```bash
php artisan migrate
```

Migration sẽ tự động tạo các bảng trong database `cinebook`.

Nếu dự án đã có Seeder:

```bash
php artisan db:seed
```

### Bước 8: Chạy dự án

```bash
php artisan serve
```

Truy cập:

http://127.0.0.1:8000

---

## 5. Database Design

CineBook sử dụng 13 bảng nghiệp vụ chính.

| STT | Tên bảng | Chức năng |
|---|---|---|
| 1 | users | Quản lý tài khoản và phân quyền |
| 2 | movies | Quản lý phim |
| 3 | cinemas | Quản lý rạp chiếu |
| 4 | screens | Quản lý phòng chiếu |
| 5 | seats | Quản lý ghế |
| 6 | showtimes | Quản lý suất chiếu |
| 7 | showtime_seats | Trạng thái ghế theo suất chiếu |
| 8 | pricing_rules | Quy tắc tính giá vé |
| 9 | bookings | Quản lý đơn đặt vé |
| 10 | booking_details | Chi tiết ghế trong đơn |
| 11 | tickets | Vé điện tử |
| 12 | reviews | Đánh giá phim |
| 13 | notifications | Thông báo người dùng |

### Quy tắc database

- Một rạp có nhiều phòng chiếu.
- Một phòng chiếu có nhiều ghế.
- Một phòng chiếu có nhiều suất chiếu theo thời gian.
- Một suất chiếu có nhiều ghế được mở bán.
- Một người dùng có thể tạo nhiều đơn đặt vé.
- Một đơn đặt vé có thể chứa nhiều ghế.
- Mỗi chi tiết ghế trong đơn có thể tạo một vé điện tử.
- Một người dùng có thể đánh giá nhiều phim.
- Trạng thái ghế được quản lý theo từng suất chiếu, không lưu trực tiếp trạng thái đặt vé trong bảng `seats`.

### Lưu ý khi thay đổi database

Không tự ý sửa hoặc xóa Migration đã được các thành viên chạy.

Khi cần thêm cột hoặc thay đổi cấu trúc bảng, hãy tạo Migration mới:

```bash
php artisan make:migration add_column_name_to_table_name
```

Ví dụ:

```bash
php artisan make:migration add_country_to_movies_table
```

Sau khi tạo Migration, chạy:

```bash
php artisan migrate
```

---

## 6. Quy trình làm việc với GitHub

### Các nhánh chính

| Branch | Mục đích |
|---|---|
| main | Phiên bản ổn định |
| develop | Nhánh tích hợp |
| feature/auth | Đăng nhập, đăng ký |
| feature/movies | Quản lý phim |
| feature/cinemas-showtimes | Rạp và suất chiếu |
| feature/booking | Đặt vé và thanh toán |
| feature/reviews-profile | Đánh giá và hồ sơ |

### Lấy code mới nhất

Trước khi bắt đầu làm việc:

```bash
git checkout develop
git pull origin develop
```

### Tạo nhánh chức năng

Ví dụ thành viên phụ trách phim:

```bash
git checkout -b feature/movies
```

### Lưu thay đổi

```bash
git add .
git commit -m "Add movie management"
```

### Đẩy code lên GitHub

```bash
git push origin feature/movies
```

Sau đó tạo Pull Request để Leader kiểm tra và hợp nhất vào `develop`.

### Nguyên tắc Git

1. Không làm việc trực tiếp trên `main`.
2. Không tự ý push code trực tiếp vào `develop` nếu nhóm yêu cầu review.
3. Mỗi chức năng nên làm trên một nhánh riêng.
4. Commit thường xuyên, nội dung commit rõ ràng.
5. Pull code mới nhất trước khi bắt đầu công việc.
6. Không commit file `.env`.
7. Không tự ý xóa hoặc sửa Migration đã được nhóm sử dụng.

---

## 7. Quy tắc làm việc với Database

Mỗi thành viên sử dụng một database MySQL riêng trên máy tính cá nhân:

```text
Database: cinebook
Host: 127.0.0.1
Port: 3306
Username: root
```

Tất cả thành viên sử dụng chung bộ Migration và Seeder trên GitHub.

Khi nhận Migration mới từ nhóm:

```bash
git pull origin develop
php artisan migrate
```

Khi cần cập nhật dữ liệu mẫu:

```bash
php artisan db:seed
```

**Cảnh báo:** Không tự ý sử dụng lệnh dưới đây trên database có dữ liệu cần giữ:

```bash
php artisan migrate:fresh
```

Lệnh này sẽ xóa toàn bộ bảng và tạo lại từ đầu.

---

## 8. Phân công công việc nhóm

Dự án gồm 4 thành viên.

| Thành viên | Công việc |
|---|---|
| Thành viên 1 – Leader | Database, Authentication, phân quyền, tích hợp hệ thống |
| Thành viên 2 | Trang chủ, danh sách phim, tìm kiếm và chi tiết phim |
| Thành viên 3 | Quản lý rạp, phòng chiếu, ghế và suất chiếu |
| Thành viên 4 | Đặt vé, thanh toán, vé điện tử, đánh giá và thông báo |

Các thành viên cần trao đổi và thống nhất tên bảng, tên cột, tên route và giao diện trước khi tích hợp.

---

## 9. Các chức năng dự kiến

### Khách hàng

- Xem danh sách phim.
- Tìm kiếm và lọc phim.
- Xem chi tiết phim và trailer.
- Xem rạp và suất chiếu.
- Chọn ghế.
- Đặt nhiều ghế trong một đơn.
- Thanh toán giả lập.
- Nhận vé điện tử.
- Xem lịch sử đặt vé.
- Đánh giá phim.
- Nhận thông báo nhắc lịch chiếu trước 2 giờ.

### Quản trị viên

- Quản lý phim.
- Quản lý rạp chiếu.
- Quản lý phòng chiếu.
- Quản lý ghế.
- Quản lý suất chiếu.
- Quản lý giá vé.
- Kiểm duyệt đánh giá.
- Theo dõi báo cáo.

---

## 10. Một số lệnh Laravel thường dùng

| Lệnh | Chức năng |
|---|---|
| `php artisan serve` | Chạy website |
| `php artisan migrate` | Chạy Migration |
| `php artisan migrate:status` | Kiểm tra trạng thái Migration |
| `php artisan migrate:rollback` | Hoàn tác Migration gần nhất |
| `php artisan db:seed` | Chạy Seeder |
| `php artisan make:model Movie -m` | Tạo Model và Migration |
| `php artisan make:controller MovieController` | Tạo Controller |
| `php artisan route:list` | Xem danh sách Route |
| `php artisan config:clear` | Xóa cache cấu hình |
| `php artisan cache:clear` | Xóa cache ứng dụng |

---

## 11. Lưu ý quan trọng

- Mỗi thành viên phải cấu hình `.env` trên máy của mình.
- Không chia sẻ file `.env` lên GitHub.
- Database phải được tạo bằng Migration để đảm bảo đồng nhất.
- Sử dụng Seeder để tạo dữ liệu mẫu.
- Kiểm tra khóa ngoại trước khi tích hợp các chức năng.
- Các thao tác giữ ghế và đặt vé phải có Database Transaction để tránh đặt trùng ghế.
- Chỉ phát hành vé sau khi thanh toán thành công.
- Không xóa cứng dữ liệu đơn đặt vé và vé đã phát hành tùy tiện.

---

## 12. Thông tin dự án

**Tên dự án:** CineBook

**Framework:** Laravel 12

**Database:** MySQL 8+

**Nhóm phát triển:** 4 thành viên

**Mục tiêu:** Xây dựng hệ thống đặt vé xem phim trực tuyến với giao diện thân thiện, quy trình đặt vé thuận tiện và hệ thống quản lý dành cho quản trị viên.

---

*README này là tài liệu hướng dẫn cài đặt và quy trình làm việc chung của nhóm CineBook. Các thành viên cần đọc và thực hiện đúng hướng dẫn trước khi bắt đầu phát triển chức năng.*
=======
# CineBook
>>>>>>> origin/main
