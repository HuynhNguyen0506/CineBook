# Hướng dẫn 04: Tác vụ chạy ngầm (nhắc lịch, nhắc sinh nhật và các job tự động)

Tài liệu này hướng dẫn cách làm các **tác vụ chạy ngầm**: những việc website tự làm theo giờ mà không cần ai bấm nút, ví dụ **nhắc lịch hẹn trước 24 giờ và 2 giờ**, **nhắc suất chiếu**, **chúc mừng sinh nhật khách hàng**, **gửi lịch làm việc mỗi sáng cho nhân viên**, **cảnh báo kho sắp hết**.

Tài liệu chỉ chỉ ra **hướng đi, cách làm và từ khóa để các nhóm tự tìm hiểu thêm**, không kèm code. Viết cho **Laravel 12**, các nhóm khác thay phần cài đặt nhưng giữ nguyên tư duy.

---

## 1. Nhóm nào cần làm và làm những việc gì

- **ZenStyle:** đề yêu cầu nhắc lịch cho khách **trước 24 giờ và 2 giờ**, thông báo lịch làm việc hằng ngày cho nhân viên, cảnh báo tồn kho thấp, hóa đơn chờ xử lý và ca làm sắp bắt đầu. Nhóm có dữ liệu ngày sinh khách hàng nên làm thêm được **nhắc sinh nhật** kèm ưu đãi.
- **CineBook:** đề yêu cầu nhắc suất chiếu **trước 2 giờ** và thông báo khuyến mãi dịp lễ. Ngoài ra cần việc nền quét các đơn thanh toán quá hạn để nhả ghế (xem hướng dẫn 02).
- **LegalEase:** nhắc lịch hẹn tư vấn cho khách và luật sư. Một việc nền rất hữu ích là **tự chuyển lịch hẹn đã qua giờ sang trạng thái hoàn thành**, vì chức năng đánh giá luật sư chỉ cho đánh giá khi lịch đã hoàn thành.

Ngoài ra các nhóm đều có thể dùng việc nền để dọn dữ liệu: xóa mã OTP và token hết hạn (xem hướng dẫn 01), hủy đơn chờ thanh toán quá hạn.

---

## 2. Hiểu đúng "chạy ngầm" là gì

Một request bình thường chạy khi **người dùng bấm** vào trang. Tác vụ chạy ngầm thì **không ai bấm**: có một "chiếc đồng hồ" gọi nó theo giờ. Một hệ thống nhắc việc thường gồm bốn mảnh ghép:

1. **Bộ hẹn giờ (Scheduler):** chiếc đồng hồ. Nó đánh thức hệ thống mỗi phút và hỏi "bây giờ có việc nào đến giờ chạy không?". Trong Laravel đó là **Task Scheduling**.
2. **Việc cần làm (Command hoặc Job):** đoạn xử lý cụ thể, ví dụ "tìm các lịch hẹn cần nhắc rồi gửi". Trong Laravel thường viết thành một **Artisan Command**.
3. **Hàng đợi (Queue), tùy chọn:** nơi xếp các việc nặng như gửi mail để làm dần, không làm chậm việc chính. Nhóm nhỏ, ít mail có thể bỏ qua hàng đợi và gửi trực tiếp.
4. **Kênh gửi thông báo (Notification, Mail):** cách báo cho người dùng, thường là **email** và **thông báo trong website**.

Chỉ cần **một** thứ chạy thật sự liên tục ở máy chủ: cái đồng hồ (một dòng cron, hoặc tiến trình `schedule:work` khi đang phát triển). Mọi việc khác Laravel lo.

---

## 3. Khuôn mẫu chung của mọi job nhắc

Mọi job nhắc (lịch hẹn, sinh nhật, ca làm...) đều đi theo cùng một khuôn:

1. Đến giờ chạy, job **tìm các bản ghi cần nhắc** theo một điều kiện thời gian.
2. **Loại bỏ** những bản ghi đã được nhắc rồi.
3. Với mỗi bản ghi còn lại, **tạo thông báo** (email và thông báo trong web).
4. **Đánh dấu đã nhắc** để lần chạy sau không nhắc lại.
5. **Ghi log** kết quả (gửi bao nhiêu, lỗi bao nhiêu).

Điểm quan trọng nhất của khuôn mẫu này là **bước 2 và 4: chống nhắc trùng**. Bộ hẹn giờ chạy lặp đi lặp lại, nếu không đánh dấu thì khách sẽ nhận hàng chục email giống nhau.

Một điều cần nhớ: **một bản ghi bị lỗi không được làm dừng cả lô.** Nếu gửi mail cho một khách thất bại thì ghi lỗi lại rồi đi tiếp những khách khác.

---

## 4. Nhắc lịch trước 24 giờ và 2 giờ

Đây là việc phổ biến nhất, thường gây rắc rối vì chuyện **cửa sổ thời gian**.

**Cách chọn lịch hẹn cần nhắc.** Đừng tìm các lịch có giờ hẹn "đúng bằng bây giờ cộng 24 giờ", vì job chạy theo phút và có thể trễ nên sẽ bỏ sót. Thay vào đó, tìm các lịch thỏa **cả hai**: giờ hẹn nằm trong khoảng "từ bây giờ đến bây giờ cộng 24 giờ" **và** chưa được nhắc mốc 24 giờ. Nghĩa là "đã đến mốc nhưng chưa nhắc", job trễ vài phút vẫn nhắc được.

**Chống nhắc trùng bằng cột đánh dấu.** Mỗi mốc nhắc có một cột thời điểm đã nhắc riêng, ví dụ một cột cho mốc 24 giờ và một cột cho mốc 2 giờ. Nhắc xong thì ghi thời điểm vào cột. Bảng lịch hẹn của nhóm ZenStyle đã có sẵn các cột kiểu này (nhắc 24h, nhắc 2h, gửi xác nhận), nhóm khác nên thêm tương tự.

**Chỉ nhắc lịch hợp lệ.** Chỉ nhắc lịch đang ở trạng thái đã xác nhận. Không nhắc lịch đã hủy, đã hoàn thành hoặc khách vắng mặt.

**Các tình huống đặc biệt cần nghĩ trước:**
- Khách đặt lịch **sát giờ**, ví dụ đặt trước 1 giờ. Mốc 24 giờ đã qua từ lâu, nên **bỏ qua** mốc này, đừng gửi một mail "còn 24 giờ" vô lý.
- Lịch bị **dời** sang giờ khác: phải **xóa các cột đã nhắc** để nhắc lại theo giờ mới.
- Lịch bị **hủy**: không nhắc nữa.
- Khách chưa xác thực email hoặc chưa có email: bỏ qua và ghi log.

**Từ khóa nên tìm hiểu:** *appointment reminder window query*, *Laravel Carbon addHours between*, *idempotent reminder sent_at flag*.

---

## 5. Nhắc sinh nhật

Khác với nhắc lịch, nhắc sinh nhật chỉ cần chạy **một lần mỗi ngày** (ví dụ 8 giờ sáng).

**Cách chọn khách có sinh nhật hôm nay.** So sánh **tháng và ngày** của ngày sinh với tháng và ngày của hôm nay, **bỏ qua năm**. Không so cả ngày đầy đủ vì năm sinh luôn khác năm hiện tại.

**Các điểm dễ sai:**
- **Sinh ngày 29/2:** ở năm không nhuận không có ngày này. Nhóm chọn một quy ước (nhắc vào 28/2 hoặc 1/3) và ghi vào phần giả định.
- **Chống nhắc trùng trong năm:** cùng một khách có thể bị nhắc hai lần nếu job chạy lại trong ngày. Lưu **năm đã nhắc** hoặc ghi vào một bảng log để kiểm tra.
- **Múi giờ:** đặt múi giờ của ứng dụng là `Asia/Ho_Chi_Minh` để "hôm nay" là ngày của Việt Nam, không phải giờ quốc tế.
- **Đừng để lộ tuổi** trong nội dung mail.
- **Chỉ gửi cho khách đã xác thực email**, còn hoạt động.
- Cho khách **tắt nhận thông báo** (cờ đồng ý nhận), nhất là nếu kèm quảng cáo.

**Gắn với ưu đãi (tùy chọn).** Có thể tự tạo một **phiếu giảm giá** có hạn dùng ngắn cho khách đúng dịp sinh nhật và đưa mã vào email. Nhóm có bảng phiếu giảm giá và điểm thưởng (ZenStyle) nên làm phần này vì nó nối được với chức năng khách thân thiết.

**Từ khóa nên tìm hiểu:** *Laravel query whereMonth whereDay birthday*, *leap day birthday handling*, *birthday email campaign opt-out*.

---

## 6. Các việc nền khác nên cân nhắc

- **Lịch làm việc hằng ngày gửi cho nhân viên:** chạy một lần mỗi sáng sớm, gom các lịch hẹn được giao trong ngày và gửi cho từng thợ.
- **Cảnh báo kho thấp:** chạy mỗi sáng hoặc mỗi giờ. Để không bị spam, chỉ cảnh báo khi hàng **vừa xuống dưới ngưỡng** hoặc chỉ nhắc lại sau một khoảng (ví dụ một lần mỗi ngày), đừng báo mỗi lần chạy.
- **Hóa đơn chờ xử lý:** nhắc lễ tân hoặc Admin về hóa đơn chưa thanh toán sau một thời gian.
- **Ca làm sắp bắt đầu:** nhắc nhân viên trước giờ vào ca.
- **Đơn thanh toán quá hạn:** quét đơn đang chờ thanh toán đã quá hạn, chuyển sang hết hạn và **nhả ghế hoặc khung giờ** (xem hướng dẫn 02).
- **Dọn dữ liệu hết hạn:** xóa mã OTP, token đặt lại mật khẩu đã hết hạn.
- **Tự đánh dấu lịch hẹn đã qua giờ:** chuyển lịch đã xác nhận mà đã qua giờ kết thúc sang trạng thái hoàn thành (hoặc vắng mặt). Việc này cần thiết nếu có chức năng đánh giá dựa trên trạng thái hoàn thành.
- **Thông báo khuyến mãi dịp lễ** (CineBook): chạy theo ngày cụ thể.

---

## 7. Cách làm trong Laravel, từng bước

### Bước 1. Tạo Artisan Command cho từng việc

Mỗi việc nền là một **Command** riêng (lệnh tạo: `make:command`), đặt tên rõ nghĩa, ví dụ lệnh nhắc lịch, lệnh nhắc sinh nhật, lệnh cảnh báo kho. Mỗi lệnh chứa đúng một việc theo khuôn mẫu ở mục 3. Ưu điểm của Command là **chạy tay được** để thử, không phải đợi tới giờ.

### Bước 2. Dùng Notification để gửi

Mỗi loại thông báo là một **Notification** (lệnh tạo: `make:notification`), ví dụ "nhắc lịch 24h", "chúc mừng sinh nhật". Một Notification có thể gửi qua nhiều kênh cùng lúc:
- Kênh **mail:** gửi email, dùng cấu hình `MAIL_*` ở hướng dẫn 01.
- Kênh **database:** lưu thông báo vào bảng `notifications` để hiện trong website (chuông thông báo). Lệnh tạo bảng: `make:notifications-table`, sau đó chạy migration. Mỗi thông báo có trạng thái chưa đọc và đã đọc.

Gửi cho một người bằng `notify`; gửi cho nhiều người bằng cách gọi gửi theo danh sách.

### Bước 3. Đăng ký lịch chạy

Trong Laravel 12, khai báo lịch chạy trong file `routes/console.php` bằng chức năng **Schedule**. Với mỗi Command, bạn chọn tần suất. Một số tần suất hay dùng: mỗi phút (`everyMinute`), mỗi 5 phút (`everyFiveMinutes`), mỗi giờ (`hourly`), hằng ngày vào giờ cụ thể (`dailyAt`), hằng tuần vào thứ cụ thể (`weeklyOn`). Có thể giới hạn theo giờ trong ngày (`between`) để không gửi mail lúc nửa đêm.

Gợi ý tần suất:
- Nhắc lịch 24h và 2h: mỗi 5 đến 10 phút.
- Nhắc sinh nhật, lịch ngày cho nhân viên: một lần mỗi sáng (ví dụ 7 đến 8 giờ).
- Cảnh báo kho: mỗi sáng hoặc mỗi giờ.
- Quét đơn quá hạn: mỗi 1 đến 5 phút (do hạn giữ chỗ ngắn).
- Dọn dữ liệu: một lần mỗi đêm.

**Các tùy chọn nên biết:**
- `withoutOverlapping`: nếu lần chạy trước chưa xong thì không chạy chồng lên. Rất nên dùng cho việc gửi mail.
- `onOneServer`: chỉ chạy trên một máy chủ khi có nhiều máy (đồ án thường không cần).
- `runInBackground`: chạy nền để không chặn các việc khác cùng giờ.
- Múi giờ: đặt `APP_TIMEZONE` là `Asia/Ho_Chi_Minh` và có thể đặt thêm tùy chọn múi giờ riêng cho lịch chạy (`schedule_timezone`). Laravel khuyến nghị tránh lập lịch theo múi giờ phức tạp khi có thể, vì chuyện giờ mùa hè. Việt Nam không có giờ mùa hè nên đơn giản hơn.

Lệnh `php artisan schedule:list` cho xem danh sách việc đã đăng ký và lần chạy tiếp theo. Nên dùng để kiểm tra đã khai báo đúng chưa.

### Bước 4. Chạy thử bằng tay trước

Trước khi trông cậy vào đồng hồ, hãy **chạy từng Command bằng tay** (`php artisan` kèm tên lệnh) với dữ liệu mẫu, xem có gửi đúng người, đúng nội dung, và có đánh dấu đã nhắc không.

### Bước 5. Chạy bộ hẹn giờ

Bộ hẹn giờ không tự chạy, phải có thứ gì đó gọi nó mỗi phút.

- **Khi đang phát triển trên máy cá nhân:** mở một cửa sổ dòng lệnh và chạy `php artisan schedule:work`. Lệnh này chạy liên tục, mỗi phút đánh thức bộ hẹn giờ một lần cho đến khi bạn tắt. **Tắt cửa sổ là việc nền ngừng.** Khi demo nhớ bật nó lên.
- **Trên máy chủ Linux:** thêm **một** mục cron chạy **mỗi phút**, đi vào thư mục dự án và chạy `php artisan schedule:run`. Chỉ cần một dòng cho tất cả các việc, vì Laravel tự quyết việc nào đến giờ.
- **Trên Windows:** dùng **Task Scheduler** (Trình lập lịch tác vụ). Tạo một tác vụ chạy lặp **mỗi 1 phút**, hành động là chạy chương trình `php.exe` với tham số `artisan schedule:run`, và đặt "Start in" là thư mục dự án. Giao diện Task Scheduler có thể khác tùy bản Windows, nếu lạc hãy tìm theo từ khóa *Windows Task Scheduler run every minute*.
- **Hosting không cho đặt cron:** dùng một dịch vụ gọi địa chỉ web định kỳ (từ khóa: *cron-job.org*, *external cron trigger Laravel*) để gọi một route đặc biệt chạy bộ hẹn giờ. Route này **bắt buộc được bảo vệ bằng token bí mật**, nếu không ai cũng gọi được.

### Bước 6 (tùy chọn). Dùng hàng đợi cho việc gửi mail

Nếu mỗi lần gửi cho hàng trăm người, nên xếp việc gửi vào **hàng đợi** để không treo bộ hẹn giờ. Các việc cần làm:

1. Cấu hình `QUEUE_CONNECTION` trong `.env`. Lựa chọn thường dùng là `database` (lưu hàng đợi trong cơ sở dữ liệu). Khi thử có thể dùng `sync` (chạy ngay, như không có hàng đợi).
2. Tạo bảng `jobs` (ứng dụng Laravel mới thường có sẵn migration; nếu thiếu thì dùng `make:queue-table`) và bảng `failed_jobs` để lưu việc lỗi.
3. Cho Notification thực thi giao diện `ShouldQueue`. Khi đó mỗi lần gửi sẽ tự vào hàng đợi.
4. **Chạy tiến trình xử lý hàng đợi** bằng `php artisan queue:work`. Nếu quên chạy thì mail nằm yên trong hàng đợi mà không gửi.
5. Cấu hình số lần thử lại khi lỗi. Việc lỗi được lưu để xem lại và thử lại bằng `queue:failed`, `queue:retry`.
6. **Sau mỗi lần sửa code, khởi động lại worker** bằng `php artisan queue:restart`. Worker là tiến trình chạy lâu và giữ code cũ trong bộ nhớ.

Đồ án nhỏ có thể bỏ qua hàng đợi và gửi trực tiếp trong Command, đơn giản và ít lỗi hơn. Chỉ thêm hàng đợi khi cần.

**Từ khóa nên tìm hiểu:** *Laravel Task Scheduling*, *Laravel Artisan command make:command*, *schedule:work schedule:run*, *Laravel Notification mail database*, *Laravel queue database driver*, *queue:work supervisor*, *withoutOverlapping*, *Windows Task Scheduler cron alternative*.

---

## 8. Làm sao để demo được việc nhắc khi "chưa đến giờ"

Việc nhắc trước 24 giờ không thể ngồi chờ khi demo. Cách xử lý:

- **Tạo dữ liệu mẫu sát mốc:** tạo lịch hẹn có giờ hẹn cách bây giờ khoảng 23 giờ 55 phút hoặc 1 giờ 55 phút, rồi chạy Command bằng tay.
- **Đưa mốc nhắc vào cấu hình**, để khi demo có thể đổi nhanh (ví dụ nhắc trước 1 phút thay vì 2 giờ).
- **Bật cả `schedule:work`** trong lúc demo để cho thấy chính bộ hẹn giờ tự gọi, không chỉ chạy tay.
- Dùng hộp thư giả (Mailpit, Mailtrap) để thấy mail ngay.
- Khi viết kiểm thử tự động, dùng công cụ **giả lập thời gian** của Carbon (từ khóa: *Carbon setTestNow*, *Laravel travel testing*).
- Chuẩn bị sẵn danh sách dữ liệu mẫu và các bước demo để không bị bối rối.

---

## 9. Độ tin cậy và an toàn

- **Chống nhắc trùng** bằng cột đánh dấu, luôn kiểm tra trước khi gửi.
- **Một lỗi không làm dừng cả lô.** Bọc từng bản ghi, ghi lỗi rồi đi tiếp.
- **Giới hạn số lượng** mỗi lần chạy nếu dữ liệu lớn.
- **Không chạy chồng nhau:** dùng `withoutOverlapping`.
- **Ghi log đủ để tra lỗi**: Command nào chạy, lúc mấy giờ, gửi bao nhiêu, lỗi gì. Không ghi nội dung nhạy cảm.
- **Không cho người dùng bị spam:** nhắc theo mốc cố định, không nhắc lặp.
- **Tôn trọng việc từ chối nhận thông báo** (cờ đồng ý nhận).
- **Đặt đúng múi giờ** cho ứng dụng và cơ sở dữ liệu; kiểm tra giờ hệ thống của máy chủ.
- Nếu có route kích hoạt bộ hẹn giờ qua mạng, **bắt buộc bảo vệ bằng token**.
- Gửi mail **sau khi** dữ liệu nghiệp vụ đã được lưu.

---

## 10. Cách kiểm tra chức năng

Nhóm nên tự thử và ghi lại:

1. Chạy Command bằng tay với lịch hẹn sát mốc 24 giờ: nhận mail, cột đánh dấu được ghi.
2. Chạy lại ngay lần nữa: **không** nhắc trùng.
3. Lịch hẹn ở mốc 2 giờ: nhận mail mốc 2 giờ, không nhận lại mốc 24 giờ.
4. Lịch đặt sát giờ (đặt trước 1 giờ): không gửi mốc 24 giờ.
5. Lịch đã hủy: không nhắc.
6. Dời lịch sang giờ khác: được nhắc lại theo giờ mới.
7. Khách có sinh nhật hôm nay: nhận mail sinh nhật, và không nhận lần hai trong ngày.
8. Khách sinh ngày 29/2: xử lý đúng theo quy ước của nhóm ở năm không nhuận.
9. Khách chưa xác thực email hoặc đã tắt nhận thông báo: không gửi.
10. Một khách bị lỗi gửi (email sai): các khách khác vẫn nhận bình thường.
11. Bật `schedule:work` và để chạy qua một phút: Command tự chạy, không cần bấm tay.
12. Tắt `schedule:work`: việc nền ngừng, bật lại thì chạy tiếp, không nhắc trùng.
13. (Nếu dùng hàng đợi) Không chạy worker: mail nằm trong hàng đợi, chạy worker thì được gửi.
14. Việc gửi lỗi: có trong danh sách việc lỗi và thử lại được.

---

## 11. Lỗi thường gặp và cách xử lý

- **Việc nền không bao giờ chạy:** chưa có cron, hoặc `schedule:work` đã tắt, hoặc Task Scheduler chưa tạo. Kiểm tra bằng `schedule:list` rồi chạy `schedule:work` thử.
- **Mail không gửi dù Command báo xong:** đang dùng hàng đợi mà chưa chạy `queue:work`, hoặc cấu hình `MAIL_*` sai (xem hướng dẫn 01).
- **Nhắc sai giờ:** sai múi giờ ứng dụng hoặc cơ sở dữ liệu. Đặt `APP_TIMEZONE` là `Asia/Ho_Chi_Minh` và kiểm tra giờ máy chủ.
- **Nhắc trùng:** thiếu cột đánh dấu, hoặc quên ghi dấu sau khi gửi, hoặc hai bộ hẹn giờ cùng chạy (ví dụ vừa `schedule:work` vừa cron).
- **Không nhắc gì:** cửa sổ thời gian quá hẹp (khớp đúng phút) hoặc điều kiện trạng thái lịch sai. Dùng điều kiện "đã đến mốc mà chưa nhắc".
- **Sửa code mà worker vẫn chạy bản cũ:** chưa chạy `queue:restart`.
- **Việc bị kẹt, không chạy nữa:** khóa của `withoutOverlapping` bị kẹt sau một lần sập. Xóa khóa bằng `schedule:clear-cache`.
- **Sửa `.env` không có tác dụng:** cấu hình bị cache, xóa cache cấu hình.
- **Việc lỗi biến mất:** chưa tạo bảng `failed_jobs`, nên không biết việc nào lỗi.
- **Bảng `notifications` không có:** chưa tạo bằng `make:notifications-table` và chạy migration.

---

## 12. Danh sách việc cần làm

**Chuẩn bị**
- [ ] Liệt kê các việc nền nhóm cần (bám đề: nhắc lịch, nhắc suất chiếu, lịch nhân viên, kho thấp...)
- [ ] Gửi được email (hướng dẫn 01), có hộp thư giả để thử
- [ ] `APP_TIMEZONE` là `Asia/Ho_Chi_Minh`
- [ ] Bảng có cột đánh dấu đã nhắc cho từng mốc

**Cài đặt**
- [ ] Mỗi việc là một Command riêng, chạy tay được
- [ ] Notification gửi qua mail và lưu vào bảng `notifications`
- [ ] Khai báo lịch chạy trong `routes/console.php`, dùng `withoutOverlapping`
- [ ] Chạy thử bằng tay, rồi bằng `schedule:work`
- [ ] Xử lý các tình huống đặc biệt: đặt sát giờ, dời lịch, hủy lịch, 29/2
- [ ] (Tùy chọn) hàng đợi và tiến trình xử lý hàng đợi

**Kiểm tra và tài liệu**
- [ ] Chạy đủ các tình huống ở mục 10
- [ ] Chuẩn bị dữ liệu mẫu và các bước demo việc nhắc
- [ ] Ghi vào tài liệu: danh sách việc nền, tần suất, cách chạy bộ hẹn giờ, quy ước 29/2 và các giả định

---

## 13. Gợi ý chia việc trong nhóm

Một người dựng khung bộ hẹn giờ trước tiên (khai báo lịch, chạy được `schedule:work`) và thống nhất cách đánh dấu đã nhắc. Sau đó mỗi người viết Command cho **module của mình** (người làm lịch hẹn viết nhắc lịch, người làm khách hàng viết sinh nhật, người làm kho viết cảnh báo kho). Một người làm Notification và giao diện chuông thông báo trong web. Cuối cùng một người **khác** với người viết code kiểm tra theo mục 10 và chuẩn bị kịch bản demo.

Nên làm hướng dẫn này **sau** khi đã gửi được email (hướng dẫn 01) và đã có dữ liệu lịch hẹn.

---

## Nguồn tham khảo

- Laravel 12, Task Scheduling: https://laravel.com/docs/12.x/scheduling
- Laravel 12, Queues: https://laravel.com/docs/12.x/queues
- Laravel 12, Notifications: https://laravel.com/docs/12.x/notifications
