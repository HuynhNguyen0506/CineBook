# Hướng dẫn 03: Thêm CAPTCHA (Cloudflare Turnstile hoặc reCAPTCHA) cho các form

Tài liệu này hướng dẫn cách thêm một bước **xác minh "tôi là người"** vào các form của website, nhằm chặn **bot** tự động gửi form: đăng ký tài khoản hàng loạt, spam liên hệ, spam đặt lịch, dò mật khẩu.

Tài liệu chỉ chỉ ra **hướng đi, cách làm và từ khóa để các nhóm tự tìm hiểu thêm**, không kèm code. Dành cho các nhóm có form đăng ký, đăng nhập, liên hệ, phản hồi hoặc đặt lịch công khai.

---

## 1. Nên gắn CAPTCHA ở những form nào?

- **Đăng ký tài khoản: nên.** Bot hay tạo tài khoản hàng loạt, và mỗi tài khoản mới còn kéo theo một email xác thực bị gửi đi (xem hướng dẫn 01).
- **Quên mật khẩu, gửi lại link hoặc mã xác thực: nên.** Mỗi lần gửi là một email, rất dễ bị lợi dụng để spam.
- **Liên hệ, phản hồi: nên.** Đây là nơi hay bị spam nội dung nhất.
- **Đăng nhập: nên**, hoặc chỉ hiện CAPTCHA **sau vài lần nhập sai** để không làm phiền người dùng bình thường. Mục đích là chống dò mật khẩu.
- **Đặt lịch, đặt vé của khách chưa đăng nhập: nên**, để chặn việc giữ chỗ giả.
- **Form của người đã đăng nhập (đặt lịch, đánh giá): không bắt buộc**, vì đã có tài khoản và có thể giới hạn tần suất.
- **Trang quản trị của Admin: không cần**, thay bằng giới hạn số lần đăng nhập.

CAPTCHA **không thay thế** việc kiểm tra dữ liệu ở server, giới hạn tần suất và xác thực email. Nó chỉ là một lớp bảo vệ thêm.

---

## 2. Nên chọn loại CAPTCHA nào?

- **Cloudflare Turnstile:** miễn phí, thường tự xác minh nên người dùng ít khi phải giải đố. Dễ cài, có khóa test sẵn. **Khuyến nghị dùng.**
- **Google reCAPTCHA v2:** ô "Tôi không phải người máy", đôi khi phải chọn ảnh. Dễ cài, có khóa test sẵn của Google, tiện nếu muốn demo nhanh.
- **Google reCAPTCHA v3:** không hiện gì với người dùng, nhưng phải tự xử lý điểm số, khó hơn cho đồ án.

Chỉ cần chọn **một**. Phần dưới viết chi tiết cho Turnstile, phần reCAPTCHA v2 ở mục 5 chỉ nêu điểm khác biệt.

---

## 3. Nguyên lý hoạt động

1. Người dùng mở trang có form. Trình duyệt tải script của dịch vụ CAPTCHA, và widget tự xác minh người dùng.
2. Dịch vụ CAPTCHA cấp cho trình duyệt một **token** (dùng một lần, có hạn).
3. Người dùng điền form và bấm Gửi. Trình duyệt gửi dữ liệu form **kèm token**.
4. Server nhận form, **gửi token cùng khóa bí mật (secret key) cho dịch vụ CAPTCHA để xác minh**.
5. Dịch vụ trả kết quả thành công hoặc lỗi.
6. Thành công thì server tiếp tục kiểm tra dữ liệu và xử lý form. Thất bại hoặc thiếu token thì báo lỗi để người dùng thử lại.

**Điểm then chốt:** server **luôn** phải xác minh token. Widget ở trình duyệt chỉ để lấy token. Nếu bỏ bước xác minh ở server thì bot chỉ cần gửi thẳng request, không cần dùng widget.

---

## 4. Cách làm với Cloudflare Turnstile

### 4.1 Hai loại khóa

- **Site key:** dùng trong HTML của form (phía trình duyệt), được phép công khai.
- **Secret key:** dùng ở server khi gọi xác minh, **bí mật**, chỉ để trong `.env`, không commit.

### 4.2 Lấy khóa, làm từng bước

1. Đăng ký tài khoản Cloudflare miễn phí tại cloudflare.com và đăng nhập vào trang quản trị (dashboard).
2. Vào mục **Turnstile** và bấm thêm **widget** mới (Add widget).
3. Đặt **tên widget** theo mục đích, ví dụ "Form đăng ký".
4. Nhập **Hostname**, là tên miền sẽ dùng widget. Cloudflare khuyến nghị chỉ cho phép các tên miền do bạn kiểm soát.
5. Chọn **Widget mode** (kiểu hiển thị: tự quyết định, không tương tác hoặc ẩn). Chế độ mặc định hoặc tự quyết định là phù hợp cho đồ án.
6. Chọn **Region** nếu được hỏi. Lưu ý: không đổi được sau khi tạo.
7. Bấm tạo, rồi sao chép **Site key** và **Secret key**.
8. Điền vào `.env`: `TURNSTILE_SITE_KEY` và `TURNSTILE_SECRET_KEY`.

Tên menu, thứ tự các bước và quy tắc về `localhost` có thể khác trên giao diện thật. Tôi chưa xác minh được vị trí chính xác của các mục này, cũng như việc `localhost` có dùng được với khóa thật hay không. Khi phát triển, hãy dùng **khóa test** ở mục 4.5 để không phụ thuộc vào chuyện đó.

### 4.3 Nhúng widget vào form (phía trình duyệt)

1. Thêm script của Turnstile vào trang hoặc layout: địa chỉ `https://challenges.cloudflare.com/turnstile/v0/api.js`, tải bất đồng bộ. **Không** tự lưu file này về hoặc chạy qua proxy.
2. Đặt một khối có class `cf-turnstile` và thuộc tính `data-sitekey` (giá trị là site key) **bên trong thẻ `<form>`**, ở chỗ bạn muốn widget hiện ra.
3. Khi widget nằm trong form, Turnstile **tự thêm** một ô ẩn tên `cf-turnstile-response` chứa token và gửi kèm khi form được submit.

Có thể tùy biến bằng các thuộc tính: `data-theme` (`auto`, `light`, `dark`), `data-size` (`normal`, `flexible`, `compact`), và các hàm gọi lại `data-callback`, `data-error-callback`, `data-expired-callback`.

### 4.4 Xác minh ở server (bắt buộc)

Server gọi **API xác minh (Siteverify)** của Turnstile bằng phương thức POST tới `https://challenges.cloudflare.com/turnstile/v0/siteverify`, gửi kèm:

- `secret`: secret key (bắt buộc).
- `response`: token lấy từ trường `cf-turnstile-response` (bắt buộc).
- `remoteip`: địa chỉ IP của người dùng (tùy chọn).

Kết quả trả về là JSON có `success` (đúng hoặc sai), `error-codes`, `challenge_ts` và `hostname`. **Chỉ cho xử lý form khi `success` là đúng.**

Gợi ý cách tổ chức: viết **một rule kiểm tra dùng chung** (một chỗ duy nhất gọi Siteverify) để mọi form chỉ việc gắn vào, tránh mỗi người viết một kiểu. Đặt thời gian chờ ngắn (khoảng 5 giây) và xử lý trường hợp mạng lỗi.

### 4.5 Những điều cần biết về token

- Token **hết hạn sau 300 giây** (5 phút) và **chỉ dùng được một lần**. Dùng lại hoặc quá hạn sẽ báo lỗi `timeout-or-duplicate`.
- Nếu form bị trả về vì lỗi dữ liệu, trang tải lại sẽ có widget **mới** và người dùng xác minh lại. Với form gửi bằng AJAX thì phải **làm mới widget** sau mỗi lần gửi.
- Nên gọi xác minh **trước** các thao tác tốn kém như gửi mail hoặc ghi cơ sở dữ liệu.
- Cloudflare nhấn mạnh: **phải** xác minh ở server, vì token ở trình duyệt có thể bị giả mạo.

### 4.6 Khóa test (dùng khi phát triển)

Cloudflare cung cấp sẵn các khóa test để thử mà không cần tạo widget.

**Site key test:**
- `1x00000000000000000000AA`: luôn **qua**.
- `2x00000000000000000000AB`: luôn **bị chặn**.
- `1x00000000000000000000BB`: luôn qua, widget ẩn.
- `2x00000000000000000000BB`: luôn bị chặn, widget ẩn.
- `3x00000000000000000000FF`: buộc hiện thử thách tương tác.

**Secret key test:**
- `1x0000000000000000000000000000000AA`: luôn **thành công**.
- `2x0000000000000000000000000000000AA`: luôn **thất bại**.
- `3x0000000000000000000000000000000AA`: báo token đã dùng (`timeout-or-duplicate`).

**Cách ghép để thử từng tình huống:**
- Gửi form thành công: site key `1x…AA` kèm secret `1x…AA`.
- Bị chặn: site key `2x…AB` kèm secret `2x…AA`.
- Token đã dùng hoặc hết hạn: site key `1x…AA` kèm secret `3x…AA`.
- Thử thách tương tác: site key `3x…FF` kèm secret `1x…AA`.

Các khóa test này là công khai của Cloudflare, **không** dùng cho môi trường thật vì ai cũng qua được.

**Từ khóa nên tìm hiểu:** *Cloudflare Turnstile client-side rendering*, *Turnstile implicit vs explicit rendering*, *Turnstile siteverify server-side validation*, *Turnstile token expiry timeout-or-duplicate*, *Turnstile testing sitekey*, *Laravel custom validation rule HTTP request*, *Laravel Http timeout*.

---

## 5. Dùng reCAPTCHA v2 thay cho Turnstile

### 5.1 Lấy khóa, làm từng bước

1. Đăng nhập tài khoản Google và vào trang quản trị reCAPTCHA (từ khóa: *Google reCAPTCHA admin console*).
2. Đăng ký trang web mới: đặt nhãn, chọn loại **reCAPTCHA v2**, kiểu "Tôi không phải người máy" (hộp kiểm).
3. Khai báo **tên miền** (có thể thêm `localhost` khi phát triển, nếu được phép).
4. Gửi đăng ký, nhận **Site key** (công khai) và **Secret key** (bí mật).

Tên mục có thể thay đổi, hãy đối chiếu với tài liệu của Google.

### 5.2 Khác biệt so với Turnstile

- Widget là khối có class `g-recaptcha` và `data-sitekey` (thay cho `cf-turnstile`).
- Trường token trên form tên `g-recaptcha-response` (thay cho `cf-turnstile-response`).
- Xác minh ở server: gọi `https://www.google.com/recaptcha/api/siteverify` với `secret`, `response`, `remoteip`.

Địa chỉ xác minh và tên trường của reCAPTCHA tôi lấy từ cách dùng phổ biến, chưa đối chiếu được với tài liệu chính thức nên nhóm hãy kiểm tra lại khi làm.

### 5.3 Khóa test reCAPTCHA v2

Google công bố cặp khóa dùng cho kiểm thử tự động:
- Site key: `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI`
- Secret key: `6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe`

Với cặp khóa này, widget luôn hiện "No CAPTCHA", mọi xác minh đều thành công và có cảnh báo không dùng cho môi trường thật.

---

## 6. Các lớp bảo vệ nên kết hợp

- **Giới hạn tần suất (rate limiting, throttle):** gắn lên route của form, ví dụ tối đa 5 lần mỗi phút. Chặn gửi dồn dập và dò mật khẩu. Từ khóa: *Laravel throttle middleware RateLimiter*.
- **Ô bẫy (honeypot):** thêm một ô nhập ẩn bằng CSS. Người thật không thấy nên không điền, bot hay điền. Nếu server thấy ô này có giá trị thì từ chối. Từ khóa: *honeypot form spam protection Laravel*.
- **Kiểm tra dữ liệu ở server:** vẫn phải validate đầy đủ và đặt ràng buộc duy nhất cho email. CAPTCHA không thay được việc này.
- **Xác thực email:** chặn tài khoản email giả (xem hướng dẫn 01).
- **CAPTCHA có điều kiện:** với đăng nhập, chỉ hiện CAPTCHA sau N lần nhập sai. Từ khóa: *login throttling conditional captcha*.

---

## 7. Các quyết định cần thống nhất trong nhóm

- **Nếu dịch vụ CAPTCHA bị lỗi hoặc không gọi được thì cho qua hay chặn?** Với form quan trọng (đăng ký, quên mật khẩu) nên **chặn** và báo "thử lại sau", không cho qua im lặng.
- **Gắn ở những form nào:** theo danh sách ở mục 1, đừng gắn tràn lan.
- **Khi xác minh thất bại:** phải có thông báo rõ ràng, không để form trắng.
- **Khả năng tiếp cận:** chọn giao diện và kích thước hợp lý, cân nhắc người khiếm thị nếu CAPTCHA dựa nhiều vào hình ảnh.
- **Môi trường:** dùng **khóa test** khi phát triển và demo trên máy cá nhân. Chỉ dùng khóa thật khi đã có tên miền.

---

## 8. Cách kiểm tra chức năng

Nhóm nên thử:

1. Điền đúng, widget xác minh xong, gửi: form xử lý thành công.
2. Gửi form khi chưa xác minh (không có token): bị từ chối, báo lỗi.
3. Gửi request trực tiếp bằng công cụ như Postman, không có token: bị từ chối.
4. Gửi token giả: bị từ chối.
5. Dùng lại token đã dùng: bị từ chối.
6. Để trang mở quá 5 phút rồi mới gửi: báo hết hạn, xác minh lại.
7. Dùng khóa test "luôn chặn": form luôn báo lỗi và giao diện hiển thị rõ.
8. Dịch vụ CAPTCHA không trả lời (mất mạng): báo lỗi tạm thời, không làm hỏng form.
9. Điền ô honeypot: bị từ chối.
10. Gửi liên tục vượt giới hạn: bị chặn bởi throttle.

---

## 9. Lỗi thường gặp và cách xử lý

- **Widget không hiện:** chưa nhúng script, hoặc khối widget đặt **ngoài** thẻ `<form>`, hoặc sai site key. Kiểm tra lại ba thứ này.
- **Form luôn báo xác minh thất bại:** sai secret key, hoặc đang ghép khóa test với khóa thật. Hãy dùng cặp khóa cùng loại.
- **Lỗi `timeout-or-duplicate`:** token đã dùng hoặc quá 300 giây. Làm mới widget và xác minh lại.
- **Thiếu trường `cf-turnstile-response`:** widget không nằm trong form, hoặc gửi bằng AJAX mà không lấy token. Đặt widget trong form, hoặc lấy token thủ công và gửi kèm.
- **Dùng khóa thật nhưng báo lỗi trên máy cá nhân:** tên miền chưa được khai báo cho widget. Thêm hostname trong cài đặt widget hoặc dùng khóa test.
- **Server chậm khi xác minh:** chưa đặt thời gian chờ. Đặt timeout ngắn và xử lý lỗi mạng.
- **Sửa `.env` không có tác dụng:** cấu hình bị cache, xóa cache cấu hình.
- **Lộ secret key:** do commit `.env` hoặc dán vào mã giao diện. Đổi khóa ngay và để `.env.example` trống giá trị.

---

## 10. Danh sách việc cần làm

**Chuẩn bị**
- [ ] Chọn loại CAPTCHA (khuyến nghị Turnstile)
- [ ] Lập danh sách form cần gắn
- [ ] Có khóa test (hoặc khóa thật) trong `.env`, `.env` không bị commit

**Cài đặt**
- [ ] Nhúng script và widget **bên trong** thẻ `<form>`
- [ ] Viết rule xác minh dùng chung ở server, gắn vào validate của từng form
- [ ] Thông báo lỗi dễ hiểu khi xác minh thất bại
- [ ] Giới hạn tần suất (throttle) cho các form đó
- [ ] (Tùy chọn) honeypot, CAPTCHA đăng nhập chỉ hiện sau vài lần sai

**Kiểm tra và tài liệu**
- [ ] Chạy đủ các tình huống ở mục 8
- [ ] Ghi vào tài liệu: loại CAPTCHA dùng, form nào có, cách thử bằng khóa test

---

## 11. Gợi ý chia việc trong nhóm

Một người lấy khóa và cấu hình `.env` trước tiên. Một người (thường là người làm phần nền tảng) viết rule xác minh dùng chung **một lần**. Sau đó mỗi người tự gắn widget vào form thuộc module của mình, dùng lại rule chung. Cuối cùng, một người **khác** với người viết code kiểm tra theo mục 8.

---

## Nguồn tham khảo

- Turnstile, xác minh ở server: https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
- Turnstile, nhúng widget phía trình duyệt: https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/
- Turnstile, khóa dùng để test: https://developers.cloudflare.com/turnstile/troubleshooting/testing/
- Turnstile, bắt đầu: https://developers.cloudflare.com/turnstile/get-started/
- reCAPTCHA, câu hỏi thường gặp và khóa test: https://developers.google.com/recaptcha/docs/faq
