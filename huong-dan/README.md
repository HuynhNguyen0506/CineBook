# Thư mục hướng dẫn kỹ thuật cho các nhóm

Thư mục này gom các tài liệu hướng dẫn kỹ thuật dùng chung cho các nhóm eProject. Mỗi tài liệu là **một file riêng**, viết như một bài hướng dẫn đọc từ đầu đến cuối.

**Cách các bài được viết:** chỉ ra **hướng đi, cách làm, từ khóa để tự tìm hiểu thêm**, **không có code**. Nhóm đọc xong thì tự research theo các từ khóa được nêu rồi tự cài đặt vào dự án của mình. Với phần lấy tài khoản hoặc khóa của dịch vụ bên ngoài (Gmail, PayPal, VNPAY, Cloudflare), bài ghi **từng bước thao tác** cụ thể.

Các bài viết theo **Laravel và MySQL** (công nghệ của đa số nhóm). Nhóm dùng công nghệ khác vẫn áp dụng được phần luồng hoạt động, bảo mật và cách kiểm tra, chỉ thay phần cài đặt.

## Danh sách bài

1. [Xác thực email khi đăng ký (OTP hoặc link)](01-xac-thuc-email-dang-ky.md)
   Dùng khi đăng ký tài khoản có email và cần chắc email là thật. Có hướng dẫn **lấy mật khẩu ứng dụng Gmail từng bước**, cùng cách dùng Mailtrap và Mailpit khi phát triển.

2. [Thanh toán online bằng sandbox PayPal hoặc VNPAY](02-thanh-toan-sandbox-paypal-vnpay.md)
   Dùng khi có đơn hàng, vé hoặc hóa đơn cần thanh toán, nhất là CineBook. Có hướng dẫn lấy khóa sandbox của từng cổng và thẻ test.

3. [CAPTCHA (Cloudflare Turnstile hoặc reCAPTCHA) cho các form](03-captcha-cloudflare-turnstile.md)
   Dùng khi có form đăng ký, đăng nhập, liên hệ, đặt lịch cần chặn bot. Có hướng dẫn lấy khóa và khóa test.

4. [Tác vụ chạy ngầm: nhắc lịch, nhắc sinh nhật và các job tự động](04-tac-vu-chay-ngam-nhac-lich-sinh-nhat.md)
   Dùng khi cần việc tự chạy theo giờ: nhắc lịch trước 24 giờ và 2 giờ, nhắc suất chiếu, chúc mừng sinh nhật, lịch làm việc hằng ngày, cảnh báo kho, quét đơn quá hạn. Có hướng dẫn chạy bộ hẹn giờ trên máy cá nhân, Linux và Windows.

5. [Đưa website lên mạng bằng ngrok để giáo viên xem](05-host-ngrok-cho-giao-vien-xem.md)
   Dùng khi cần demo cho giáo viên mà chưa có hosting, hoặc cần địa chỉ HTTPS công khai để nhận IPN thanh toán và mở link email trên điện thoại. Có hướng dẫn cài ngrok, lấy authtoken, domain cố định, chỉnh cấu hình Laravel, giới hạn gói miễn phí và bảo mật.

## Gợi ý các bài tiếp theo (chưa viết)

- Quên mật khẩu, đặt lại mật khẩu qua email (cả 3 đề, CineBook có yêu cầu riêng)
- Phân quyền theo vai trò (cả 3 đề)
- Chống đặt trùng lịch, giữ chỗ tạm thời (ZenStyle, LegalEase, CineBook chọn ghế)
- Tìm kiếm, lọc, sắp xếp, phân trang (cả 3 đề, nhất là LegalEase)
- Upload và hiển thị ảnh, ví dụ poster, ảnh đại diện (CineBook, LegalEase)
- Báo cáo thống kê và xuất file (cả 3 đề)

## Cấu trúc mỗi bài

Mỗi bài thường gồm: mục tiêu, chọn hướng làm, nguyên lý hoặc luồng hoạt động, các bước làm, **từ khóa nên tìm hiểu**, các điều bảo mật cần giữ, cách kiểm tra, lỗi thường gặp, danh sách việc cần làm, gợi ý chia việc trong nhóm và nguồn tham khảo.

## Lưu ý khi áp dụng

- Tài liệu nộp bài của nhóm **không được chứa source code** theo yêu cầu của đề, chỉ mô tả luồng và cách làm.
- Khóa bí mật (mật khẩu, secret key) để trong `.env`, **không commit** lên Git.
- Những chỗ bài ghi "chưa xác minh" là phần giao diện hoặc đường dẫn của dịch vụ bên ngoài có thể đã thay đổi, nhóm cần tự kiểm tra lại.
