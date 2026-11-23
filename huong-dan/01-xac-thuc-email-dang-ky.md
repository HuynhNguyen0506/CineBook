# Hướng dẫn 01: Xác thực email khi đăng ký tài khoản

Tài liệu này hướng dẫn cách làm chức năng **xác thực email khi đăng ký**: người dùng đăng ký bằng một địa chỉ email thì phải chứng minh email đó là của mình, bằng cách bấm vào một đường link hoặc nhập một mã OTP được gửi qua email. Chỉ sau khi xác thực xong, tài khoản mới được dùng đầy đủ chức năng.

Tài liệu chỉ chỉ ra **hướng đi, cách làm và từ khóa để các nhóm tự tìm hiểu thêm**, không kèm code. Các nhóm dùng Laravel sẽ thấy phần lớn từ khóa trùng với tên chức năng có sẵn của Laravel.

Chức năng này dành cho khách hàng tự đăng ký trên web. Tài khoản do Admin tạo hộ (lễ tân, thợ, nhân viên) thường không cần bước này.

---

## 1. Nên chọn cách nào: link hay OTP?

Có hai cách phổ biến.

**Cách 1: gửi link xác thực.** Sau khi đăng ký, hệ thống gửi một email có đường link riêng. Người dùng mở email, bấm link, tài khoản được xác thực. Cách này dễ làm nhất vì Laravel đã có sẵn chức năng. Link có chữ ký để không ai sửa được, và có hạn dùng (mặc định khoảng 60 phút). Nhược điểm: nếu người dùng mở email trên điện thoại còn đang đăng ký trên máy tính thì hơi bất tiện.

**Cách 2: gửi mã OTP.** Sau khi đăng ký, hệ thống gửi email có một mã 6 số. Người dùng nhập mã đó vào trang web. Cách này phải tự thiết kế nhiều hơn (bảng lưu mã, kiểm tra hạn, giới hạn số lần nhập sai), nhưng trải nghiệm tốt vì người dùng không phải rời trang, và thường được đánh giá cao hơn về mặt chức năng.

**Gợi ý:** muốn làm nhanh và chắc chắn chạy thì chọn **link**. Muốn có điểm cộng hoặc đề yêu cầu "mã xác thực" thì chọn **OTP**. Chỉ cần chọn **một** cách và làm cho đúng.

---

## 2. Luồng hoạt động

### Luồng với link xác thực

1. Người dùng điền form đăng ký và gửi.
2. Hệ thống kiểm tra dữ liệu (đúng định dạng, email chưa có người dùng). Nếu sai thì báo lỗi và dừng.
3. Hệ thống tạo tài khoản ở trạng thái **chưa xác thực** (cột thời điểm xác thực còn trống).
4. Hệ thống gửi email chứa link xác thực có chữ ký và hạn dùng.
5. Người dùng thấy trang "Vui lòng kiểm tra email".
6. Người dùng mở email và bấm link.
7. Hệ thống kiểm tra link có đúng chữ ký và còn hạn không.
   - Nếu không: báo "link sai hoặc hết hạn" và cho nút **Gửi lại email**.
   - Nếu đúng: ghi lại thời điểm xác thực, tài khoản được dùng đầy đủ.

### Luồng với mã OTP

1. Người dùng điền form đăng ký và gửi, hệ thống kiểm tra dữ liệu như trên.
2. Hệ thống tạo tài khoản chưa xác thực.
3. Hệ thống sinh một mã 6 số ngẫu nhiên, **lưu bản băm của mã** cùng giờ hết hạn (thường 5 đến 10 phút), rồi gửi mã qua email.
4. Người dùng được chuyển tới trang nhập mã.
5. Người dùng nhập mã. Hệ thống kiểm tra mã đúng, còn hạn, và chưa nhập sai quá 5 lần.
   - Nhập sai: tăng số lần sai, báo "mã không đúng". Quá 5 lần thì khóa mã, bắt người dùng yêu cầu mã mới.
   - Nhập đúng: ghi thời điểm xác thực, vô hiệu mã vừa dùng.
6. Người dùng có thể bấm **Gửi lại mã**, nhưng phải chờ ít nhất khoảng 60 giây giữa hai lần.

### Các trạng thái của tài khoản

- **Chưa xác thực:** chỉ được vào trang xác thực, gửi lại link/mã, đăng xuất. **Không** được đặt vé, đặt lịch, đánh giá.
- **Đã xác thực:** dùng đầy đủ theo vai trò.
- **Bị vô hiệu hóa:** không đăng nhập được, độc lập với chuyện đã xác thực email hay chưa.

Điều quan trọng: phải **chặn thật** các chức năng quan trọng với tài khoản chưa xác thực, không phải chỉ hiện một dòng thông báo.

---

## 3. Chuẩn bị: làm cho website gửi được email

Trước khi làm xác thực, nhóm phải gửi được email từ dự án. Có vài cách, dùng cái nào tùy giai đoạn:

- **Mailpit** (chạy trên máy cá nhân) hoặc **Mailtrap** (hộp thư giả trên mạng): mail không đi đâu cả, chỉ hiện trong một hộp thư thử. Dùng khi **đang phát triển**.
- **Gmail SMTP với mật khẩu ứng dụng:** mail được gửi thật tới Gmail của người nhận. Dùng khi **demo**.
- **Chế độ ghi log:** chỉ ghi nội dung mail vào file log, dùng khi chỉ cần kiểm tra mail có được tạo ra không.
- Dịch vụ gửi mail chuyên nghiệp (SendGrid, Brevo, Resend, Amazon SES): dùng khi chạy thật, không cần cho đồ án.

**Khuyến nghị:** lúc phát triển dùng Mailpit hoặc Mailtrap, đến lúc demo mới chuyển sang Gmail.

### 3.1 Cách lấy mật khẩu ứng dụng Gmail (App Password), làm từng bước

**Vì sao cần:** Gmail không cho một ứng dụng bên ngoài đăng nhập bằng mật khẩu Gmail thông thường. Bạn phải tạo một **mật khẩu ứng dụng**, là một chuỗi 16 ký tự dành riêng cho website của bạn. Mật khẩu này **chỉ tạo được khi tài khoản đã bật Xác minh 2 bước**.

**Bước 0. Chuẩn bị tài khoản Gmail**

- Nên tạo **một Gmail riêng cho dự án** (ví dụ tên dự án thêm chữ "noreply"), không dùng Gmail cá nhân chính của bạn.
- **Không dùng** email do trường hoặc công ty cấp (dạng `...@truong.edu.vn`). Tài khoản tổ chức có thể bị quản trị viên chặn tính năng này.

**Bước 1. Bật Xác minh 2 bước (2-Step Verification)**

1. Mở trang https://myaccount.google.com/ và đăng nhập.
2. Ở menu bên trái, chọn **Bảo mật** (Security).
3. Tìm phần "Cách bạn đăng nhập vào Google", chọn **Xác minh 2 bước** (2-Step Verification), rồi bấm **Bắt đầu** (Get started).
4. Đăng nhập lại nếu được yêu cầu.
5. Làm theo hướng dẫn trên màn hình: nhập **số điện thoại**, nhận mã xác minh qua tin nhắn, nhập mã đó, rồi bấm **Bật** (Turn on).
6. Kiểm tra: trang Bảo mật hiện trạng thái Xác minh 2 bước đang bật.

Lưu ý:
- Nếu bạn vừa thêm số điện thoại mới, Google có thể cần vài ngày để tin cậy số đó.
- Nếu bạn chỉ dùng **khóa bảo mật** (security key) làm bước thứ hai thì sẽ không tạo được mật khẩu ứng dụng. Hãy thêm số điện thoại hoặc ứng dụng xác thực.

**Bước 2. Tạo mật khẩu ứng dụng**

1. Mở thẳng đường dẫn https://myaccount.google.com/apppasswords và đăng nhập lại nếu Google hỏi. Nếu không thấy mục này trong menu thì đó là bình thường, cứ dùng đường dẫn trực tiếp.
2. Ở ô **Tên ứng dụng** (App name), gõ một tên dễ nhớ, ví dụ `ZenStyle Laravel` hoặc `CineBook mail`.
3. Bấm **Tạo** (Create).
4. Google hiện một hộp có **mật khẩu 16 ký tự**, thường chia thành 4 nhóm, mỗi nhóm 4 ký tự.
5. **Sao chép ngay** và lưu ở nơi an toàn. Khi dán vào cấu hình, **bỏ hết khoảng trắng** giữa các nhóm.
6. Bấm **Xong** (Done). Nếu lỡ làm mất mật khẩu thì không xem lại được, hãy xóa mục đó và tạo cái mới.

**Bước 3. Điền vào cấu hình dự án**

Mở file `.env` của dự án và đặt các giá trị sau (chỉ tên biến và giá trị):

- `MAIL_MAILER` là `smtp`
- `MAIL_HOST` là `smtp.gmail.com`
- `MAIL_PORT` là `587`
- `MAIL_ENCRYPTION` là `tls` (hoặc dùng cổng `465` với `ssl`, chỉ cần đúng cặp)
- `MAIL_USERNAME` là địa chỉ Gmail đầy đủ, ví dụ `tenduan@gmail.com`
- `MAIL_PASSWORD` là **mật khẩu ứng dụng 16 ký tự, không có khoảng trắng** (không phải mật khẩu đăng nhập Gmail)
- `MAIL_FROM_ADDRESS` là cùng địa chỉ Gmail ở trên (Gmail thường tự thay địa chỉ người gửi)
- `MAIL_FROM_NAME` là tên hiển thị, ví dụ tên dự án
- `APP_URL` là địa chỉ website đang chạy (xem mục 3.4)

**Bước 4. Thử gửi**

Dùng công cụ gửi mail thử của framework (từ khóa tìm hiểu: *Laravel send test email tinker*) để gửi tới một hộp thư khác. Nếu không thấy mail, kiểm tra cả mục **Thư rác** (Spam), rồi xem phần "Lỗi thường gặp" ở cuối bài.

**Khi không tạo được mật khẩu ứng dụng**, hãy kiểm tra lần lượt:

- Google báo "cài đặt bạn tìm kiếm không khả dụng": gần như chắc chắn là **chưa bật Xác minh 2 bước**, làm Bước 1 trước.
- Đã bật rồi mà vẫn không được: có thể bạn chỉ dùng khóa bảo mật, hoặc đã bật chế độ **Bảo vệ nâng cao** (Advanced Protection). Hãy thêm số điện thoại hoặc dùng Gmail khác.
- Dùng tài khoản của trường hoặc công ty: quản trị viên có thể đã chặn. Dùng một Gmail cá nhân hoặc Gmail riêng cho dự án.
- Vừa bật 2 bước xong mà chưa thấy: đăng xuất, đăng nhập lại và thử sau vài phút.

**Thu hồi và giữ bí mật mật khẩu ứng dụng**

- Muốn thu hồi, vào lại trang mật khẩu ứng dụng, chọn mục cần xóa và bấm **Xóa** (Remove). Website lập tức không gửi mail được nữa.
- Nếu bạn **đổi mật khẩu Gmail** thì **tất cả** mật khẩu ứng dụng bị thu hồi, mail sẽ ngừng gửi. Lúc đó tạo lại và cập nhật `.env`.
- Mật khẩu ứng dụng cho phép gửi mail nhân danh tài khoản của bạn. **Không đưa lên Git, không dán vào nhóm chat chung.** Chỉ để trong `.env` trên máy của người cần dùng, còn file `.env.example` thì để trống giá trị.
- Gmail có giới hạn số mail gửi mỗi ngày. Đủ cho đồ án nhưng không dùng để gửi hàng loạt.

### 3.2 Dùng Mailtrap (hộp thư giả trên mạng)

1. Đăng ký tài khoản miễn phí tại mailtrap.io.
2. Vào mục **Email Testing**, chọn **Inboxes**, mở hộp thư mặc định (hoặc tạo hộp mới cho dự án).
3. Mở phần **SMTP Settings** hoặc **Integrations** và chọn **Laravel**. Trang sẽ hiện sẵn các giá trị host, port, username, password.
4. Chép các giá trị đó vào `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, và đặt `MAIL_MAILER` là `smtp`.
5. Gửi thử: mail xuất hiện trong hộp thư Mailtrap, không đến hộp thư thật.

Giao diện Mailtrap có thể thay đổi. Nếu khác với mô tả, tìm theo từ khóa *Mailtrap Laravel SMTP settings*.

### 3.3 Dùng Mailpit (chạy ngay trên máy)

1. Tải Mailpit cho hệ điều hành của bạn (từ khóa: *Mailpit download release*) và chạy nó.
2. Mailpit nhận thư ở **cổng 1025** và cho xem thư trên trình duyệt ở **cổng 8025** (mở `http://localhost:8025`).
3. Trong `.env`: `MAIL_MAILER` là `smtp`, `MAIL_HOST` là `127.0.0.1`, `MAIL_PORT` là `1025`, không cần tài khoản, không cần mã hóa.
4. Mọi mail website gửi ra sẽ hiện trong giao diện Mailpit.

### 3.4 Biến `APP_URL` và việc demo trên nhiều máy

Link xác thực được tạo ra từ `APP_URL`. Nếu `APP_URL` sai địa chỉ hoặc sai cổng thì link mở không được, hoặc báo **chữ ký không hợp lệ**.

Khi demo mà người xem mở link bằng điện thoại hoặc máy khác, địa chỉ `localhost` sẽ **không** truy cập được. Hãy dùng địa chỉ IP mạng nội bộ, hoặc dùng công cụ tạo đường hầm (từ khóa: *ngrok*, *Cloudflare Tunnel*) rồi đặt `APP_URL` theo địa chỉ đó. Sau khi sửa `.env`, nhớ xóa cache cấu hình (từ khóa: *Laravel config:clear*).

---

## 4. Cách làm với link xác thực (dùng chức năng có sẵn của Laravel)

Laravel đã làm sẵn gần hết, nhóm chỉ cần nối các phần lại. Các việc cần làm, theo thứ tự:

1. **Cho model `User` thực thi giao diện `MustVerifyEmail`.** Đây là cách báo cho Laravel biết user này cần xác thực email.
2. **Phát sự kiện `Registered` ngay sau khi tạo user** trong chức năng đăng ký. Laravel có sẵn bộ lắng nghe sự kiện này để gửi email xác thực.
3. **Khai báo 3 route có tên cố định:** `verification.notice` (trang nhắc kiểm tra email), `verification.verify` (đường link trong email trỏ vào đây, có chữ ký), `verification.send` (gửi lại email, có giới hạn số lần). Tên route phải đúng vì Laravel dựa vào chúng để tạo link.
4. **Dùng middleware `verified`** để bọc các route quan trọng (đặt vé, đặt lịch, đánh giá). Người chưa xác thực sẽ bị đưa về trang nhắc.
5. **Làm giao diện trang nhắc xác thực:** có câu thông báo, nút **Gửi lại email**, nút **Đăng xuất**.
6. **Tùy chọn:** đổi tiêu đề và lời chào của email cho đúng tên dự án.

Thời gian sống của link được cấu hình trong file cấu hình xác thực của Laravel, mặc định khoảng 60 phút.

**Từ khóa nên tìm hiểu:** *Laravel email verification*, *MustVerifyEmail*, *Registered event*, *signed URL / temporarySignedRoute*, *verification.notice verification.verify*, *verified middleware*, *throttle middleware*, *customize VerifyEmail notification*.

---

## 5. Cách làm với mã OTP (tự thiết kế)

Cách này không có sẵn trong Laravel nên nhóm phải tự thiết kế. Các việc cần làm:

1. **Tạo một bảng lưu mã OTP.** Nên có các cột: id, mã người dùng (liên kết bảng `users`), **bản băm của mã** (không lưu mã gốc), giờ hết hạn, số lần đã nhập sai, giờ gửi gần nhất, giờ đã dùng (để trống nếu chưa dùng).
2. **Mỗi người dùng chỉ có một mã đang hiệu lực.** Khi gửi mã mới, mã cũ phải mất hiệu lực.
3. **Viết hàm gửi mã.** Hàm này kiểm tra lần gửi gần nhất (chưa quá 60 giây thì từ chối), sinh mã 6 số bằng bộ sinh số ngẫu nhiên an toàn, lưu bản băm cùng giờ hết hạn (5 đến 10 phút), đặt lại số lần sai về 0, rồi gửi email.
4. **Viết email chứa mã.** Nên ghi rõ thời gian hết hạn và dòng "Nếu bạn không đăng ký, hãy bỏ qua email này".
5. **Làm trang nhập mã và xử lý.** Khi người dùng gửi mã: lấy mã mới nhất, chưa dùng, chưa hết hạn; nếu đã sai từ 5 lần trở lên thì từ chối; so sánh mã nhập vào với bản băm; sai thì tăng số lần sai; đúng thì đánh dấu đã dùng và ghi thời điểm xác thực cho user.
6. **Làm nút Gửi lại mã**, có giới hạn tần suất.
7. **Vẫn nên cho `User` thực thi `MustVerifyEmail`** và trỏ trang nhắc xác thực tới trang nhập mã, để dùng lại middleware `verified` chặn chức năng cho tài khoản chưa xác thực.

**Từ khóa nên tìm hiểu:** *email OTP verification flow*, *PHP random_int / CSPRNG*, *Laravel Hash::make Hash::check*, *OTP expiry TTL best practice*, *OTP brute force protection*, *resend OTP cooldown*, *Laravel Mailable*, *Laravel Notification mail*, *user enumeration prevention*.

---

## 6. Những điều về bảo mật không được bỏ

- Luôn **băm mật khẩu** khi tạo user, không lưu mật khẩu thô.
- Link xác thực phải có **chữ ký** và **hạn dùng**.
- Với OTP: lưu **bản băm**, hạn 5 đến 10 phút, **tối đa 5 lần thử**, và dùng xong thì vô hiệu mã.
- **Giới hạn gửi lại** (chờ ít nhất 60 giây) để không bị lợi dụng spam email.
- Chỉ gửi mail **sau khi** đã lưu dữ liệu thành công. Nếu gửi mail lỗi thì tài khoản vẫn tồn tại và người dùng bấm **Gửi lại** được.
- Dùng **thông báo chung** cho các trường hợp sai, đừng để lộ email nào đã đăng ký.
- Email phải **duy nhất** ở cả tầng ứng dụng và trong cơ sở dữ liệu.
- Không đưa thông tin nhạy cảm vào nội dung mail.
- Mật khẩu ứng dụng và file `.env` **không commit lên Git**.
- Nên gắn CAPTCHA cho form đăng ký và nút gửi lại (xem hướng dẫn 03) để chặn bot spam email.

---

## 7. Cách kiểm tra chức năng

Nhóm nên tự thử các tình huống sau và ghi lại kết quả:

1. Đăng ký email mới, dữ liệu hợp lệ: tạo được tài khoản, gửi mail, chuyển tới trang nhắc xác thực.
2. Đăng ký email đã tồn tại: báo lỗi, không tạo thêm tài khoản.
3. Chưa xác thực mà vào trang đặt vé hoặc đặt lịch: bị chuyển về trang nhắc.
4. Bấm link đúng (hoặc nhập đúng mã): xác thực thành công, dùng được chức năng.
5. Link hoặc mã đã hết hạn: báo hết hạn, có nút gửi lại.
6. Sửa tham số trong link: bị từ chối.
7. (OTP) Nhập sai 5 lần: khóa mã, bắt gửi mã mới.
8. (OTP) Dùng lại mã đã dùng: bị từ chối.
9. Bấm Gửi lại liên tục: bị giới hạn.
10. Bấm lại link cũ sau khi đã xác thực: không lỗi, báo đã xác thực.
11. Tắt dịch vụ mail: tài khoản vẫn được tạo, báo lỗi gửi mail, cho phép gửi lại.

Khi thử, dùng Mailpit hoặc Mailtrap để thấy mail ngay. Nên chuẩn bị sẵn một tài khoản mẫu **đã xác thực** để cả nhóm thử các chức năng khác.

---

## 8. Lỗi thường gặp và cách xử lý

- **Không thấy mail:** kiểm tra lại các biến `MAIL_*`. Nếu đang dùng hàng đợi (queue) thì phải chạy tiến trình xử lý hàng đợi, hoặc tạm đặt chế độ đồng bộ khi thử.
- **Sửa `.env` mà không có tác dụng:** cấu hình đang bị cache, xóa cache cấu hình.
- **Gmail báo lỗi đăng nhập (mã 535, "Username and Password not accepted"):** thường do dùng mật khẩu Gmail thường, hoặc dán mật khẩu ứng dụng còn khoảng trắng, hoặc mật khẩu ứng dụng đã bị thu hồi. Tạo lại và dán không có khoảng trắng.
- **Gmail không cho tạo mật khẩu ứng dụng:** xem phần "Khi không tạo được" ở mục 3.1.
- **Mail vào thư rác:** do tên miền gửi chưa cấu hình SPF, DKIM, DMARC. Với đồ án có thể chấp nhận, khi demo dùng hộp thư giả.
- **Link báo "403 Invalid signature":** `APP_URL` sai địa chỉ hoặc cổng, hoặc mở link bằng một địa chỉ khác. Đặt đúng `APP_URL` và mở bằng đúng địa chỉ đó.
- **Link mở ra `localhost` trong khi người xem ở máy khác:** dùng địa chỉ IP nội bộ hoặc đường hầm khi demo.
- **Middleware `verified` không chặn được:** model `User` chưa thực thi `MustVerifyEmail`.
- **Cột thời điểm xác thực không được ghi:** bảng thiếu cột, hoặc cột không cho gán hàng loạt. Thêm cột, hoặc tìm cách gán trực tiếp của framework.

---

## 9. Danh sách việc cần làm

**Chuẩn bị**
- [ ] Chọn cách xác thực: link hay OTP
- [ ] Có cách gửi mail dùng được (Mailpit hoặc Mailtrap cho phát triển, Gmail với mật khẩu ứng dụng cho demo)
- [ ] `.env` đúng, `APP_URL` đúng, khóa bí mật không bị commit
- [ ] Bảng `users` có email duy nhất và cột thời điểm xác thực

**Cài đặt**
- [ ] Đăng ký tạo tài khoản ở trạng thái chưa xác thực
- [ ] Gửi mail sau khi lưu tài khoản
- [ ] Trang nhắc xác thực (và trang nhập mã nếu dùng OTP)
- [ ] Xử lý các trường hợp thành công, hết hạn, sai
- [ ] Nút gửi lại có giới hạn
- [ ] Chặn chức năng quan trọng với tài khoản chưa xác thực

**Kiểm tra và tài liệu**
- [ ] Chạy đủ các tình huống ở mục 7
- [ ] Có tài khoản mẫu đã xác thực
- [ ] Ghi vào tài liệu: cách xác thực, cách gửi mail, các giả định (không ghi khóa bí mật)

---

## 10. Gợi ý chia việc trong nhóm

Một người cấu hình việc gửi mail (và tạo mật khẩu ứng dụng cho buổi demo) trước tiên. Sau đó một người làm đăng ký, gửi mail, route, middleware (thường là người làm phần đăng nhập). Một người làm giao diện trang nhắc xác thực hoặc nhập mã song song. Cuối cùng, một người **khác** với người viết code đứng ra kiểm tra theo mục 7.

Nên làm chức năng này ngay sau đăng ký và đăng nhập, trước các chức năng cần chặn người dùng chưa xác thực.

---

## Nguồn tham khảo

- Tạo và dùng mật khẩu ứng dụng Google: https://support.google.com/accounts/answer/185833
- Bật Xác minh 2 bước của Google: https://support.google.com/accounts/answer/185839
- Tài liệu Laravel: tìm *Laravel Email Verification*, *Laravel Mail*, *Laravel Signed URLs* trên laravel.com/docs
