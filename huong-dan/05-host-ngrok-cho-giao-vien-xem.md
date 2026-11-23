# Hướng dẫn 05: Đưa website lên mạng bằng ngrok để giáo viên xem

Tài liệu này hướng dẫn cách dùng **ngrok** để biến website đang chạy trên máy của bạn thành một **đường link công khai** (dạng `https://...ngrok-free.app`), để giáo viên hoặc bất kỳ ai ở xa cũng mở xem được mà không phải cài gì.

Tài liệu chỉ chỉ ra **hướng đi, cách làm và từ khóa để các nhóm tự tìm hiểu thêm**, không kèm code. Viết cho dự án **Laravel**, các nhóm dùng công nghệ khác vẫn làm được các bước về ngrok, chỉ khác phần cấu hình ứng dụng ở mục 5.

---

## 1. ngrok là gì và khi nào nên dùng

ngrok tạo một **đường hầm (tunnel)** từ một địa chỉ công khai trên Internet về một cổng trên máy của bạn. Người ngoài truy cập địa chỉ công khai, ngrok chuyển yêu cầu về website đang chạy ở máy bạn.

**Nên dùng khi:**
- Cần **demo cho giáo viên** mà chưa có hosting.
- Cần một địa chỉ **HTTPS công khai** tạm thời để thử các thứ cần gọi từ ngoài vào: nhận IPN của VNPAY (hướng dẫn 02), mở link xác thực email trên điện thoại (hướng dẫn 01).
- Cần cho người khác xem bản đang làm mà không phải gửi code.

**Không phù hợp khi:**
- Cần website chạy **24/7**. ngrok chỉ sống khi **máy bạn bật, website đang chạy và ngrok đang chạy**. Tắt máy là mất.
- Cần nộp URL cuối cùng cho bài chấm điểm. Đề khuyến nghị host website lên một nơi chạy thật, ngrok chỉ là cách tạm. Xem các phương án ở mục 10.

---

## 2. Chuẩn bị trước khi dùng ngrok

ngrok không chạy hộ website. Trước tiên website phải **chạy được ở máy bạn**:

1. Cơ sở dữ liệu đã được tạo và có dữ liệu mẫu (ví dụ import file `.sql` vào MySQL/MariaDB của XAMPP).
2. Thư viện đã cài, file cấu hình `.env` đã điền, khóa ứng dụng đã sinh.
3. Website chạy được ở địa chỉ local, ví dụ `http://127.0.0.1:8000` (nếu dùng lệnh phát triển của Laravel) hoặc `http://localhost/ten-du-an` (nếu dùng XAMPP, cổng 80).
4. Bạn **biết chính xác cổng** website đang dùng. Đây là thông tin ngrok cần.
5. Đã có sẵn tài khoản demo cho từng vai trò (admin, người dùng thường, nhân viên...) với dữ liệu mẫu đủ để xem.

Nên thử đủ các chức năng chính ở local trước, vì lỗi ở local sẽ vẫn lỗi qua ngrok.

---

## 3. Cài ngrok và mở đường hầm, làm từng bước

### 3.1 Tạo tài khoản và lấy authtoken

1. Đăng ký tài khoản miễn phí tại `dashboard.ngrok.com/signup` và đăng nhập.
2. Vào trang lấy **authtoken**: `dashboard.ngrok.com/get-started/your-authtoken`. Sao chép chuỗi authtoken (một chuỗi dài).
3. **Giữ authtoken bí mật**, nó giống như mật khẩu của tài khoản ngrok. Không đưa lên Git, không dán vào nhóm chat chung.

### 3.2 Cài ngrok (Windows)

1. Vào `ngrok.com/download` và tải bản dành cho Windows.
2. Cài đặt hoặc giải nén file tải về (tùy bản bạn chọn). Nếu giải nén thì ghi nhớ thư mục chứa file `ngrok.exe`.
3. Mở cửa sổ dòng lệnh mới và gõ `ngrok version` để kiểm tra. Nếu báo "không tìm thấy lệnh", hoặc thêm thư mục chứa `ngrok.exe` vào biến môi trường `PATH`, hoặc chạy lệnh từ chính thư mục đó.

Trang tài liệu của ngrok không mô tả chi tiết các bước cài trên Windows, nên nếu bạn thấy giao diện tải khác thì tìm theo từ khóa *ngrok install Windows*.

### 3.3 Gắn authtoken (làm một lần)

Chạy lệnh gắn authtoken của ngrok với chuỗi bạn vừa sao chép (lệnh là `ngrok config add-authtoken` theo sau là authtoken). Ngrok lưu vào file cấu hình trên máy bạn, các lần sau không phải nhập lại. Có cách khác là đặt biến môi trường `NGROK_AUTHTOKEN`, biến này sẽ ghi đè file cấu hình.

### 3.4 Mở đường hầm tới website

1. Đảm bảo website **đang chạy** và mở được ở địa chỉ local.
2. Mở **một cửa sổ dòng lệnh mới** (để website vẫn chạy ở cửa sổ cũ).
3. Chạy lệnh `ngrok http` kèm theo **số cổng** của website. Ví dụ website chạy ở cổng 8000 thì dùng `ngrok http 8000`. Với XAMPP chạy cổng 80 thì dùng `ngrok http 80`.
4. Màn hình ngrok hiện ra một địa chỉ công khai, dạng `https://tên-ngẫu-nhiên.ngrok-free.app`, kèm dòng cho biết nó đang chuyển về cổng nào ở máy bạn.
5. Mở địa chỉ đó trên điện thoại (tắt Wi-Fi, dùng 4G) để chắc chắn người ngoài truy cập được.

Mỗi lần mở đường hầm, ngrok chạy liên tục trong cửa sổ đó. **Tắt cửa sổ là đường hầm mất.**

### 3.5 Địa chỉ cố định (domain tĩnh)

Mặc định, mỗi lần mở ngrok có thể nhận địa chỉ khác, làm giáo viên giữ link cũ bị hỏng. Theo tài liệu giới hạn gói miễn phí, mỗi tài khoản có **một dev domain được cấp sẵn và không đổi khi khởi động lại**.

1. Xem domain của bạn tại `dashboard.ngrok.com/domains`.
2. Khi mở đường hầm, chỉ định đúng domain đó (từ khóa tìm cách làm: *ngrok http --url dev domain*, *ngrok static domain free*).
3. Gửi cho giáo viên **một đường link cố định** dùng suốt các buổi demo.

### 3.6 Giao diện theo dõi của ngrok

Khi ngrok chạy, agent mở một cổng theo dõi ở máy bạn tại địa chỉ `127.0.0.1:4040`. Có thể dùng để xem các yêu cầu vừa đi qua đường hầm và tìm lỗi. Tôi chưa xác minh được chi tiết giao diện web này từ tài liệu, nếu cần hãy tìm theo từ khóa *ngrok inspector localhost:4040*.

---

## 4. Những giới hạn của gói miễn phí cần biết

Theo tài liệu giới hạn của ngrok:

- **Dữ liệu truyền ra:** 1 GB mỗi tháng.
- **Số yêu cầu HTTP:** 20.000 mỗi tháng. Mỗi trang web tải nhiều file (ảnh, CSS, JS) nên mỗi lần xem có thể tính vài chục yêu cầu.
- **Tốc độ:** tối đa 4.000 yêu cầu mỗi phút.
- **Đồng thời:** tối đa 3 endpoint và 3 agent cùng lúc.
- **Domain:** chỉ **một** domain được cấp sẵn, không có domain riêng.
- **Thời gian sống:** đường hầm miễn phí **không bị hết hạn** theo thời gian, chạy bao lâu cũng được.

Đủ dùng cho demo cho giáo viên và vài người xem. Không đủ cho nhiều người dùng liên tục.

**Trang cảnh báo của ngrok (rất quan trọng khi demo):** với gói miễn phí, lần đầu người xem mở link bằng trình duyệt sẽ thấy một **trang cảnh báo trung gian** của ngrok trước khi vào website của bạn. Người xem phải bấm nút **Visit Site**. Sau khi bấm, một cookie giúp bỏ qua trang này trong **7 ngày** cho domain đó. Hãy **báo trước cho giáo viên** để thầy cô không nghĩ link bị lỗi hoặc nguy hiểm. Các yêu cầu từ ứng dụng hay API (không phải trình duyệt) không bị trang này chặn, và gói trả phí thì không có trang này.

---

## 5. Cấu hình ứng dụng Laravel để chạy tốt qua ngrok

Website vốn chỉ biết mình đang ở `localhost`. Khi đi qua ngrok, địa chỉ bên ngoài khác hẳn, nên phải chỉnh một số thứ, nếu không sẽ gặp lỗi như trang mất CSS, nút bấm chuyển về `localhost` hoặc báo trang hết hạn.

1. **Đặt `APP_URL` bằng địa chỉ ngrok.** Biến này dùng để tạo mọi đường link, nhất là link trong email xác thực. Sau khi sửa `.env` hãy **xóa cache cấu hình** (từ khóa: *Laravel config:clear*).
2. **Cho ứng dụng tin cậy proxy.** ngrok là một proxy đứng trước website, báo cho website biết người dùng đang dùng HTTPS qua các thông tin chuyển tiếp. Nếu website không tin cậy proxy thì nó tưởng mình đang chạy HTTP, và tạo ra link CSS, JS dạng HTTP trên trang HTTPS, trình duyệt sẽ chặn (lỗi **mixed content**), trang trắng hoặc mất giao diện. Từ khóa: *Laravel trust proxies*, *X-Forwarded-Proto*.
3. **Cân nhắc ép dùng HTTPS** cho các đường link do ứng dụng tạo ra khi chạy qua ngrok (từ khóa: *Laravel force https URL scheme*).
4. **Dùng file giao diện đã build, không dùng máy chủ Vite chế độ dev.** Nếu dự án dùng Vite, hãy build file sẵn (từ khóa: *npm run build*) vì máy chủ dev của Vite không đi qua ngrok được. Nhóm dùng CDN và file tĩnh sẵn thì không gặp vấn đề này.
5. **Tắt chế độ gỡ lỗi khi mở công khai.** Đặt `APP_DEBUG` là `false`. Nếu để bật, khi có lỗi website sẽ in ra thông tin nội bộ cho mọi người xem.
6. **Xử lý việc chạy nhiều người xem cùng lúc.** Máy chủ phát triển của Laravel mặc định xử lý từng yêu cầu một nên dễ chậm khi nhiều người xem. Có thể bật nhiều tiến trình cho nó (từ khóa: *PHP_CLI_SERVER_WORKERS*), hoặc dùng Apache của XAMPP và cho ngrok trỏ vào cổng 80, ổn định hơn.
7. **Xử lý email:** khi demo cho giáo viên không nhất thiết gửi mail thật. Dùng chế độ ghi log, Mailtrap hoặc Mailpit (hướng dẫn 01). Nếu muốn mail thật tới người xem thì cấu hình Gmail như hướng dẫn 01.
8. **Phiên đăng nhập (session) và cookie:** nếu gặp lỗi "trang đã hết hạn" (mã 419) hoặc đăng nhập xong lại bị đẩy ra, kiểm tra `APP_URL`, cấu hình cookie của session và việc đã tin cậy proxy ở bước 2.

Các nhóm có thanh toán sandbox (hướng dẫn 02): đây chính là chỗ ngrok phát huy, vì **địa chỉ IPN của VNPAY và ReturnUrl phải là địa chỉ ngrok** (HTTPS công khai). Mỗi lần địa chỉ ngrok đổi phải cập nhật lại, dùng domain tĩnh ở mục 3.5 để khỏi phải sửa.

---

## 6. Chuẩn bị gửi cho giáo viên

Gửi một **thông điệp ngắn, đủ thông tin** thay vì chỉ gửi link:

- **Đường link** website (domain tĩnh của ngrok).
- **Lưu ý về trang cảnh báo** của ngrok và cách bấm **Visit Site**.
- **Tài khoản demo cho từng vai trò** (tên đăng nhập và mật khẩu) để thầy cô tự thử. Ví dụ quản trị viên, người dùng, nhân viên.
- **Các chức năng chính** nên xem và theo thứ tự nào (một kịch bản demo ngắn, 5 đến 10 bước).
- **Khoảng thời gian** website sẽ mở (vì máy bạn phải bật), và số điện thoại hoặc cách liên lạc nếu link không vào được.
- Nếu cần, gửi kèm video quay màn hình demo như **phương án dự phòng**.

Nên **thử mở link bằng tài khoản demo trên một máy khác** (hoặc điện thoại dùng 4G) trước khi gửi.

---

## 7. Giữ cho buổi demo ổn định

- **Máy phải bật và không ngủ:** vào cài đặt nguồn, tắt chế độ ngủ và tự tắt màn hình khi đang cắm điện.
- **Hai cửa sổ cùng chạy:** một cho website, một cho ngrok. Không tắt cái nào.
- **Mạng ổn định:** dùng dây mạng hoặc Wi-Fi mạnh. Mạng yếu làm người xem thấy trang chậm.
- **Dữ liệu mẫu ổn định:** tạo sẵn dữ liệu để các chức năng chính đều có gì đó để xem, không để trang trống.
- **Khởi động lại đúng thứ tự** nếu có sự cố: website trước, ngrok sau.
- **Dự phòng:** quay sẵn video demo, có ảnh chụp màn hình các chức năng chính.
- **Thử trước giờ demo** ít nhất một lần, tốt nhất vào đúng giờ sẽ demo.

---

## 8. Bảo mật: những điều không được bỏ

Mở ngrok nghĩa là **bất kỳ ai có link đều truy cập được website của bạn**, không chỉ giáo viên.

- **Chỉ mở khi cần, tắt khi xong.** Đóng cửa sổ ngrok sau buổi demo.
- **Không dùng dữ liệu thật** (tên, số điện thoại, email của người thật) trong cơ sở dữ liệu mẫu.
- **Tắt chế độ gỡ lỗi** (`APP_DEBUG` là `false`).
- **Tài khoản demo** thường có mật khẩu đơn giản. Với tài khoản **quản trị**, đổi sang mật khẩu mạnh hơn khi mở công khai, hoặc chỉ đưa tài khoản quản trị cho giáo viên.
- **Chỉ mở đường hầm cho cổng website**, không bao giờ mở cổng của cơ sở dữ liệu (3306) hay phpMyAdmin.
- **Không để lộ authtoken:** không commit, không dán vào nhóm chat. Nếu lỡ lộ, vào dashboard ngrok tạo lại authtoken.
- **Cân nhắc thêm lớp bảo vệ** cho đường hầm (từ khóa: *ngrok basic auth*, *ngrok traffic policy*, *ngrok IP restrictions*), để chỉ người biết tài khoản vào được. Một số tính năng có thể thuộc gói trả phí, hãy kiểm tra trên trang của ngrok.
- **Cẩn thận chức năng tải file lên:** người lạ có thể gửi file bậy. Có thể tạm tắt khi mở công khai.
- Đừng quên gắn **CAPTCHA** cho các form (hướng dẫn 03) nếu để link mở lâu.

---

## 9. Cách kiểm tra chức năng

Nhóm nên tự thử và ghi lại:

1. Mở link ngrok trên máy khác: thấy trang cảnh báo, bấm **Visit Site** vào được website.
2. Mở link bằng điện thoại dùng 4G: vẫn vào được.
3. Giao diện hiển thị đầy đủ: có CSS, JS, ảnh, không bị mất hoặc báo mixed content.
4. Đăng nhập bằng từng tài khoản demo: vào đúng giao diện theo vai trò.
5. Các nút, link, form chuyển trang không bị đưa về `localhost`.
6. Gửi một form (ví dụ đăng ký, đặt lịch): không báo trang hết hạn (419).
7. Link trong email (xác thực, đặt lại mật khẩu) mở được và đúng địa chỉ ngrok.
8. Mở bằng 3 đến 5 thiết bị cùng lúc: website không bị chậm bất thường.
9. Tắt ngrok: link báo không truy cập được. Mở lại bằng domain tĩnh: link cũ dùng lại được.
10. Ngắt website nhưng để ngrok chạy: link báo lỗi không kết nối được tới website.
11. Tắt máy, bật lại, khởi động theo đúng thứ tự: vẫn chạy được.

---

## 10. Phương án khác khi ngrok không đủ

- **Cloudflare Tunnel (quick tunnel, `trycloudflare.com`):** cũng tạo địa chỉ công khai từ máy cá nhân, không cần mở cổng. Từ khóa: *cloudflared quick tunnel*.
- **Các công cụ đường hầm khác:** *localtunnel*, *Tailscale Funnel*.
- **Hosting miễn phí hoặc giá rẻ chạy 24/7 (nên dùng cho bản nộp bài):** từ khóa *free PHP Laravel hosting*, *Render*, *Railway*, *InfinityFree*, *Azure for Students*, *VPS giá rẻ*. Nhóm cần tự cài cơ sở dữ liệu MySQL, đặt biến môi trường, chạy bộ hẹn giờ (hướng dẫn 04).
- Các cách trên đều cần xem lại giới hạn từng nơi, vì chính sách miễn phí thay đổi thường xuyên.

---

## 11. Lỗi thường gặp và cách xử lý

- **Link ngrok báo "tunnel không tồn tại" hoặc "endpoint offline":** cửa sổ ngrok đã tắt, hoặc máy đã tắt. Mở lại ngrok.
- **Link ngrok báo ngrok không kết nối được tới website (thường gặp mã ERR_NGROK_8012):** website local không chạy, hoặc ngrok trỏ nhầm cổng. Kiểm tra website ở local và số cổng trong lệnh ngrok.
- **ngrok báo authtoken sai hoặc thiếu:** chưa gắn authtoken, hoặc sao chép thiếu. Gắn lại ở mục 3.3.
- **Giáo viên thấy trang cảnh báo lạ:** đó là trang cảnh báo của gói miễn phí, bấm **Visit Site**. Hãy báo trước.
- **Mất CSS, trang trắng, có lỗi mixed content trong console:** website chưa tin cậy proxy hoặc tạo link HTTP. Làm mục 5 bước 2 và 3.
- **Bấm nút bị đưa về `localhost`:** `APP_URL` sai. Sửa và xóa cache cấu hình.
- **Lỗi 419 "Page Expired" khi gửi form:** vấn đề session và cookie qua proxy. Kiểm tra `APP_URL`, tin cậy proxy, xóa cookie cũ.
- **Link xác thực email báo chữ ký không hợp lệ:** `APP_URL` không khớp với địa chỉ đang mở. Dùng đúng địa chỉ ngrok.
- **Địa chỉ ngrok đổi mỗi lần:** chưa dùng domain tĩnh (mục 3.5).
- **Trang chậm khi nhiều người xem:** máy chủ phát triển xử lý từng yêu cầu một. Bật nhiều tiến trình hoặc dùng Apache.
- **Báo quá giới hạn của gói miễn phí:** đã hết hạn mức yêu cầu hoặc dữ liệu trong tháng. Chờ chu kỳ mới, dùng tài khoản khác hoặc chuyển phương án ở mục 10.
- **Phần mềm diệt virus hoặc tường lửa chặn ngrok:** cho phép `ngrok.exe` trong tường lửa.

---

## 12. Danh sách việc cần làm

**Chuẩn bị**
- [ ] Website chạy ổn ở local, biết chính xác số cổng
- [ ] Cơ sở dữ liệu mẫu và tài khoản demo cho từng vai trò
- [ ] Tài khoản ngrok, đã cài và đã gắn authtoken (authtoken không bị commit)
- [ ] Đã có domain tĩnh của ngrok

**Cấu hình**
- [ ] `APP_URL` bằng địa chỉ ngrok, đã xóa cache cấu hình
- [ ] Tin cậy proxy, ép HTTPS cho link nếu cần
- [ ] Giao diện đã build sẵn nếu dùng Vite
- [ ] `APP_DEBUG` là `false`
- [ ] Mail ở chế độ phù hợp (log, Mailtrap hoặc Gmail)

**Trước buổi demo**
- [ ] Chạy đủ các tình huống ở mục 9, kể cả thử trên điện thoại dùng 4G
- [ ] Soạn thông điệp gửi giáo viên (link, lưu ý trang cảnh báo, tài khoản demo, kịch bản)
- [ ] Máy không bị ngủ, mạng ổn định, có video demo dự phòng

**Sau buổi demo**
- [ ] Tắt ngrok, đổi lại mật khẩu quản trị nếu cần
- [ ] Ghi vào tài liệu cách host bằng ngrok và các giả định

---

## 13. Gợi ý chia việc trong nhóm

Một người phụ trách ngrok: tạo tài khoản, lấy authtoken, lấy domain tĩnh, và là người **bật máy** trong buổi demo. Một người chỉnh cấu hình ứng dụng (mục 5) và kiểm tra chạy qua ngrok. Một người chuẩn bị dữ liệu mẫu, tài khoản demo và kịch bản demo. Cuối cùng một người **khác** thử bằng thiết bị khác theo mục 9 và soạn thông điệp gửi giáo viên.

Nên thử qua ngrok **sớm** (không chờ tới sát buổi demo), vì các lỗi proxy và `APP_URL` thường mất thời gian xử lý.

---

## Nguồn tham khảo

- ngrok, bắt đầu: https://ngrok.com/docs/getting-started/
- ngrok, giới hạn gói miễn phí: https://ngrok.com/docs/pricing-limits/free-plan-limits/
- Tải ngrok: https://ngrok.com/download
- Lấy authtoken: https://dashboard.ngrok.com/get-started/your-authtoken
- Domain của tài khoản: https://dashboard.ngrok.com/domains
