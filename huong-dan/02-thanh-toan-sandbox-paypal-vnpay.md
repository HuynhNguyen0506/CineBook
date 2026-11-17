# Hướng dẫn 02: Thanh toán online bằng sandbox PayPal hoặc VNPAY

Tài liệu này hướng dẫn cách đưa **thanh toán thử (sandbox)** vào website: khi khách trả tiền thành công thì đơn hàng, vé hoặc hóa đơn chuyển sang trạng thái "đã thanh toán". **Không dùng tiền thật.**

Sandbox là môi trường thử do nhà cung cấp dịch vụ thanh toán mở ra: giao diện và cách hoạt động giống bản thật nhưng tiền và tài khoản đều là giả, nên các nhóm thoải mái thử.

Tài liệu chỉ chỉ ra **hướng đi, cách làm và từ khóa để các nhóm tự tìm hiểu thêm**, không kèm code.

---

## 1. Nhóm nào cần làm

- **CineBook:** đề yêu cầu "xác nhận đặt vé chỉ sau khi thanh toán (dummy)". Nên làm thanh toán bằng sandbox, vì nó thật hơn thanh toán giả và là điểm cộng.
- **ZenStyle:** hóa đơn và thanh toán nhiều phương thức tại quầy. Không bắt buộc, có thể thêm thanh toán online cho phương thức chuyển khoản hoặc ví.
- **LegalEase:** thanh toán nằm ngoài phạm vi theo bản SRS của nhóm, không cần làm.

Chỉ nên làm khi nhóm **đã có đơn hàng** với trạng thái và tổng tiền rõ ràng.

---

## 2. Nên chọn PayPal hay VNPAY?

**VNPAY** phù hợp đề của Việt Nam: thanh toán bằng **VND**, thẻ ATM nội địa. Cách làm là tự tạo một đường link có chữ ký, chuyển khách sang cổng VNPAY, rồi nhận kết quả về qua hai đường (ReturnUrl và IPN). Điểm khó là phải tự ký đúng và nhận IPN, mà IPN cần một địa chỉ HTTPS công khai nên chạy trên `localhost` phải có công cụ đường hầm.

**PayPal** thì khách thanh toán qua nút PayPal. PayPal **không hỗ trợ VND** nên phải đổi sang USD. Bù lại không cần địa chỉ công khai để nhận kết quả, vì chính server của bạn tự gọi PayPal để chốt giao dịch.

**Gợi ý:** giá bằng VND, đề của Việt Nam thì chọn **VNPAY**. Muốn nhanh, ít cấu hình mạng thì chọn **PayPal**. Chỉ cần chọn **một**.

---

## 3. Nguyên tắc chung cho mọi cổng thanh toán

Dù dùng cổng nào cũng phải giữ các nguyên tắc sau:

1. **Tạo đơn trước, thanh toán sau.** Đơn ở trạng thái `pending` (chờ thanh toán) và **tổng tiền do server tính**. Tuyệt đối không lấy số tiền từ trình duyệt gửi lên, vì người dùng có thể sửa.
2. **Chỉ server mới được kết luận "đã thanh toán"**, dựa trên phản hồi **đã được kiểm tra** từ nhà cung cấp. Trang "thanh toán thành công" hiện trên trình duyệt thì chưa đủ, vì ai cũng có thể tự mở đường link đó.
3. **Đối chiếu ba thứ** trước khi đổi trạng thái đơn: đúng đơn hàng, đúng **số tiền**, và nhà cung cấp báo **thành công**.
4. **Xử lý lặp an toàn (idempotent):** cổng có thể gửi cùng một thông báo nhiều lần, nhưng đơn chỉ được cập nhật một lần.
5. **Có hạn thanh toán.** Quá hạn thì đơn chuyển sang hết hạn và nhả lại thứ đang giữ (ghế, khung giờ).
6. **Khóa bí mật** (HashSecret, Client Secret) chỉ để trong file `.env`, không đưa lên Git.

**Các trạng thái đơn gợi ý:**
- `pending`: đã tạo đơn, chưa trả.
- `paid`: đã trả thành công.
- `failed`: cổng báo thất bại, cho khách thử lại bằng giao dịch mới.
- `cancelled`: khách hủy ở cổng, cho thử lại.
- `expired`: quá hạn, nhả ghế hoặc khung giờ.

**Nên có bảng `payments` tách riêng khỏi bảng đơn hàng**, để một đơn có thể thử trả nhiều lần. Bảng này nên có: mã đơn, nhà cung cấp (vnpay hoặc paypal), mã giao dịch phía mình gửi cho cổng (duy nhất), mã giao dịch phía cổng trả về, số tiền, tiền tệ, trạng thái, phản hồi thô để tra lỗi (không chứa khóa bí mật), thời điểm thanh toán.

---

## 4. Cách làm với VNPAY sandbox

### 4.1 Luồng hoạt động

1. Khách bấm **Thanh toán** trên đơn hàng.
2. Server tạo một bản ghi thanh toán `pending` và sinh một mã giao dịch riêng.
3. Server dựng các tham số gửi sang VNPAY, **ký bằng khóa bí mật (HMAC-SHA512)** để ra chữ ký.
4. Server chuyển hướng khách sang địa chỉ cổng sandbox của VNPAY kèm các tham số và chữ ký.
5. Khách chọn ngân hàng, nhập thẻ test và mã OTP trên trang của VNPAY.
6. VNPAY chuyển khách về trang `ReturnUrl` của bạn, kèm kết quả. Trang này **chỉ để hiển thị** kết quả cho khách sau khi kiểm tra chữ ký, **không** cập nhật đơn.
7. Đồng thời máy chủ VNPAY gọi vào địa chỉ **IPN** của bạn. Đây mới là nơi server kiểm tra chữ ký, đối chiếu đơn, số tiền, trạng thái rồi **cập nhật đơn thành đã thanh toán**.
8. Server trả lời VNPAY bằng một đoạn JSON có hai trường `RspCode` và `Message`.

Hai đường nhận kết quả có vai trò khác nhau: **ReturnUrl** là cho khách xem, còn **IPN** là nguồn chính xác để cập nhật đơn.

### 4.2 Lấy thông tin sandbox (TmnCode, HashSecret) và thẻ test

1. Vào cổng developer của VNPAY tại `sandbox.vnpayment.vn`. Ở đó có các mục tài liệu API, trang demo, danh sách ngân hàng và trang liên hệ.
2. **Đăng ký môi trường test** để nhận **TmnCode** (mã website) và **HashSecret** (khóa dùng để tạo chữ ký). Một bài hướng dẫn trên Viblo cho biết trang đăng ký là `sandbox.vnpayment.vn/devreg` và VNPAY gửi hai thông tin này sau khi đăng ký. **Lúc tôi kiểm tra, đường dẫn đó báo lỗi 404.** Vì vậy nhóm hãy tìm lại đường dẫn đăng ký trên trang developer của VNPAY, hoặc dùng mục **Liên hệ** (Contact) để hỏi.
3. Lưu TmnCode và HashSecret vào `.env` của người phụ trách, **không commit**.
4. Nếu cổng quản trị merchant sandbox có yêu cầu khai báo **địa chỉ IPN**, hãy khai báo tại đó. Vị trí cấu hình này tôi chưa xác minh được, hãy xem tài liệu hoặc hỏi VNPAY.
5. Thử cổng bằng **trang demo** của VNPAY trước khi tích hợp: `sandbox.vnpayment.vn/apis/vnpay-demo/`.

Lưu ý:
- **Không dùng** TmnCode hoặc HashSecret "mẫu" có sẵn trong các ví dụ code trên mạng, đó là khóa của người khác.
- Một bình luận cũ (không chính thức) nói sandbox miễn phí có thể không hỗ trợ thanh toán bằng **QR**. Hãy thử bằng thẻ ATM nội địa trước.

**Thẻ test (ngân hàng NCB, theo trang demo của VNPAY):**
- Số thẻ: `9704198526191432198`
- Tên chủ thẻ: `NGUYEN VAN A`
- Ngày phát hành: `07/15`
- Mật khẩu OTP: `123456`

Thông tin thẻ test có thể thay đổi, hãy kiểm tra lại trên trang demo.

### 4.3 Nhận IPN khi chạy trên `localhost`

IPN do máy chủ VNPAY gọi vào máy của bạn, nên cần một **địa chỉ HTTPS công khai**. Khi đang chạy `localhost`, làm như sau:

1. Tạo tài khoản một dịch vụ đường hầm (từ khóa: *ngrok* hoặc *Cloudflare Tunnel*).
2. Cài công cụ và chạy lệnh trỏ vào **cổng ứng dụng đang chạy** (ví dụ cổng 8000).
3. Công cụ cấp cho bạn một địa chỉ dạng `https://xxxx.ngrok-free.app`.
4. Đặt địa chỉ đó vào `APP_URL`, `ReturnUrl` và **địa chỉ IPN**.
5. Khi thử, mở website bằng đúng địa chỉ HTTPS này.

Địa chỉ miễn phí thường đổi mỗi lần khởi động lại, nhớ cập nhật lại các nơi đã đặt.

### 4.4 Các thông tin phải gửi sang VNPAY

Khi tạo đường link thanh toán, cần gửi các thông tin sau (tên tham số bắt đầu bằng `vnp_`):

- `vnp_Version`: phiên bản API, hiện là `2.1.0`.
- `vnp_Command`: giá trị `pay`.
- `vnp_TmnCode`: mã website do VNPAY cấp.
- `vnp_Amount`: **số tiền nhân 100** (ví dụ 10.000 VND thì gửi 1.000.000).
- `vnp_CreateDate`: thời điểm tạo, định dạng `yyyyMMddHHmmss`, múi giờ GMT+7.
- `vnp_CurrCode`: `VND` (hiện chỉ hỗ trợ VND).
- `vnp_IpAddr`: địa chỉ IP của khách.
- `vnp_Locale`: `vn` hoặc `en`.
- `vnp_OrderInfo`: mô tả đơn, tiếng Việt **không dấu**, không ký tự đặc biệt.
- `vnp_OrderType`: mã danh mục hàng hóa.
- `vnp_ReturnUrl`: địa chỉ nhận kết quả khi khách xong.
- `vnp_ExpireDate`: hạn thanh toán, cùng định dạng với ngày tạo.
- `vnp_TxnRef`: mã giao dịch phía mình, **không được trùng trong ngày**.
- `vnp_BankCode`: không bắt buộc, bỏ trống để khách tự chọn phương thức.
- `vnp_SecureHash`: chữ ký.

Địa chỉ cổng sandbox: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`, gửi bằng phương thức GET.

### 4.5 Cách tạo chữ ký

Chữ ký là điểm dễ sai nhất. Các bước:

1. Sắp xếp tất cả tham số `vnp_*` (trừ chữ ký) theo **tên tăng dần**.
2. Mã hóa URL (URL-encode) cả tên lẫn giá trị rồi nối thành chuỗi dạng `ten=giatri&ten=giatri...`.
3. Tính **HMAC-SHA512** của chuỗi đó, dùng **HashSecret** làm khóa.
4. Gắn kết quả vào cuối địa chỉ dưới dạng tham số `vnp_SecureHash`.

Khi kiểm tra chữ ký của kết quả trả về thì làm ngược lại: bỏ `vnp_SecureHash` (và `vnp_SecureHashType` nếu có), sắp xếp, ký lại cùng cách, rồi so với chữ ký nhận được.

**Từ khóa nên tìm hiểu:** *HMAC SHA512*, *VNPAY vnp_SecureHash*, *urlencode vs rawurlencode PHP*, *VNPAY vnp_Amount*.

### 4.6 Xử lý ReturnUrl và IPN

**ReturnUrl:** chỉ kiểm tra chữ ký và hiển thị kết quả theo `vnp_ResponseCode`. Chữ ký sai thì báo "dữ liệu không hợp lệ". **Không** đổi trạng thái đơn ở đây.

**IPN:** kiểm tra theo đúng thứ tự dưới đây, bước nào sai thì trả mã tương ứng cho VNPAY:

1. Chữ ký hợp lệ. Sai thì trả `RspCode 97`.
2. Tìm thấy đơn theo `vnp_TxnRef`. Không thấy thì trả `01`.
3. Số tiền `vnp_Amount` chia 100 bằng đúng số tiền của đơn. Sai thì trả `04`.
4. Đơn còn ở trạng thái `pending`. Nếu đã xử lý rồi thì trả `02` (đã xác nhận trước đó).
5. Nếu **cả** `vnp_ResponseCode` **và** `vnp_TransactionStatus` đều là `00` thì đánh dấu đã thanh toán, ngược lại đánh dấu thất bại.
6. Cập nhật xong thì trả `RspCode 00` kèm `Message` là `Confirm Success`.

Lưu ý quan trọng:
- Mã `00` và `02` kết thúc luồng. Nếu bạn trả `01`, `04`, `97`, `99` hoặc **quá thời gian chờ** thì VNPAY sẽ **gọi lại** (tối đa 10 lần, mỗi lần cách 5 phút). Vì vậy xử lý phải **an toàn khi bị gọi lặp**.
- Nên bọc bước cập nhật đơn trong một **giao dịch cơ sở dữ liệu** và khóa dòng, để hai lần IPN tới cùng lúc không cùng đổi trạng thái.

**Ý nghĩa một số mã kết quả:** `vnp_ResponseCode` bằng `00` là thành công; `24` là khách hủy giao dịch; `51` là không đủ số dư; `11` là hết hạn chờ thanh toán; `99` là lỗi khác. `vnp_TransactionStatus` bằng `00` là thành công, `01` là chưa hoàn tất, `02` là lỗi, `04` là giao dịch đảo, `07` là nghi ngờ gian lận. Hãy kiểm tra **cả hai** trường trước khi kết luận thành công.

**Từ khóa nên tìm hiểu:** *VNPAY IPN RspCode*, *idempotent payment webhook*, *Laravel DB transaction lockForUpdate*, *ngrok laravel*, *Laravel scheduler*.

---

## 5. Cách làm với PayPal sandbox

### 5.1 Luồng hoạt động

1. Khách mở trang thanh toán và thấy nút PayPal (do thư viện JavaScript của PayPal vẽ ra).
2. Khách bấm nút. Trình duyệt gọi về server của bạn, **chỉ gửi mã đơn của mình, không gửi số tiền**.
3. Server tính tiền từ cơ sở dữ liệu, đổi sang USD.
4. Server xin access token từ PayPal rồi gọi API **tạo order** với `intent` là `CAPTURE`. PayPal trả về mã order.
5. Server trả mã order cho trình duyệt.
6. Trình duyệt mở cửa sổ PayPal, khách đăng nhập tài khoản **sandbox** và chấp nhận thanh toán.
7. Khi khách chấp nhận xong, trình duyệt gọi về server kèm mã order PayPal.
8. Server gọi API **capture** để thực sự thu tiền.
9. Server **kiểm tra kết quả capture** (đã hoàn tất, đúng số tiền, đúng tiền tệ, đúng đơn của mình) rồi mới cập nhật đơn thành đã thanh toán.

### 5.2 Lấy khóa và tài khoản sandbox, làm từng bước

1. Vào `developer.paypal.com` và đăng nhập bằng tài khoản PayPal, hoặc tạo tài khoản mới (cần xác minh email).
2. Mở **Developer Dashboard**, vào **Testing Tools**, rồi chọn **Sandbox Accounts**.
3. Theo mặc định đã có sẵn **hai tài khoản sandbox**: một tài khoản **Personal** (người mua) và một tài khoản **Business** (người bán). Cần thêm thì bấm **Create account**.
4. Muốn xem email và mật khẩu của một tài khoản sandbox, bấm biểu tượng **ba chấm (⋮)** cạnh tài khoản đó rồi chọn **View/Edit Account**. Dùng tài khoản **Personal** để đăng nhập khi thanh toán thử.
5. Vào **Apps & Credentials**. Có thể dùng **Default Application** trong danh sách REST API apps, hoặc bấm **Create App** để tạo ứng dụng riêng.
6. Đảm bảo bạn đang ở chế độ **Sandbox** (không phải Live) khi lấy khóa. Vị trí công tắc Sandbox/Live tôi chưa xác minh được trên giao diện hiện tại, hãy tự kiểm tra.
7. Sao chép **Client ID** và **Client Secret** (bấm để hiện Secret). Lưu vào `.env`, **không commit**.
8. Để xem giao dịch thử: đăng nhập `sandbox.paypal.com` bằng tài khoản **Business** sandbox và xem mục **Recent Activity**.

Các biến cấu hình gợi ý: `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_BASE_URL` (địa chỉ sandbox là `https://api-m.sandbox.paypal.com`), `PAYPAL_CURRENCY` (USD) và `VND_PER_USD` (tỷ giá cố định).

### 5.3 Chuyện tiền tệ

PayPal **không hỗ trợ VND**, nên đơn tính bằng VND phải đổi sang USD (hoặc tiền tệ PayPal hỗ trợ) trước khi tạo order.

- Dùng **tỷ giá cố định trong cấu hình** và ghi tỷ giá này vào **phần giả định** của tài liệu dự án.
- Làm tròn 2 chữ số thập phân, gửi số tiền dưới dạng chuỗi (ví dụ "10.00").
- Lưu cả số tiền VND, số tiền USD và tỷ giá đã dùng trong bản ghi thanh toán.

### 5.4 Các kỹ thuật cần biết

- **Lấy access token (OAuth2 client credentials):** gọi địa chỉ token của PayPal bằng Basic Auth với `Client ID:Secret` và nội dung `grant_type=client_credentials`. Token có hạn (hàng giờ), hết hạn thì xin lại. Gửi token trong header `Authorization: Bearer`.
- **Orders API v2:** API tạo và xử lý đơn thanh toán. Tạo order (`POST /v2/checkout/orders`), sau đó capture (`POST /v2/checkout/orders/{id}/capture`).
- **`intent: CAPTURE`:** thu tiền ngay khi khách chấp nhận (khác với `AUTHORIZE` là chỉ giữ tiền trước).
- **`purchase_units`:** phần mô tả mục thanh toán, gồm số tiền (`amount` với `currency_code` và `value`) và mã đơn của mình (`reference_id`).
- **JS SDK Buttons:** nhúng thư viện JavaScript của PayPal bằng **Client ID** (đây là khóa công khai, được để ở trang web). Hai hàm chính: `createOrder` (gọi server tạo order) và `onApprove` (gọi server capture sau khi khách chấp nhận).
- **Kiểm tra kết quả capture:** so trạng thái hoàn tất, số tiền, tiền tệ và `reference_id` với đơn của mình.
- **`PayPal-Request-Id`:** header giúp chống gửi trùng khi gọi API lặp.
- **Webhook (tùy chọn):** PayPal báo sự kiện (ví dụ capture hoàn tất) vào server của bạn, dùng để đối chiếu khi trình duyệt bị đóng giữa chừng. Cần địa chỉ công khai như IPN của VNPAY. Với đồ án có thể bỏ qua.

**Từ khóa nên tìm hiểu:** *PayPal REST API authentication client credentials*, *PayPal Orders v2 create order capture*, *PayPal JavaScript SDK Buttons createOrder onApprove*, *PayPal capture status COMPLETED verify amount*, *PayPal webhooks PAYMENT.CAPTURE.COMPLETED*, *Laravel PayPal package srmklive*.

Về nguyên tắc ở trình duyệt: **không bao giờ gửi số tiền từ trình duyệt**, chỉ gửi mã đơn. Client ID công khai được, còn Secret thì tuyệt đối không được lộ ra giao diện.

---

## 6. Giữ chỗ khi thanh toán (đặt vé, đặt lịch)

Nếu thanh toán gắn với ghế hoặc khung giờ, cần **giữ chỗ có thời hạn**:

1. Khi tạo đơn `pending`, đánh dấu ghế hoặc khung giờ là **đang giữ**, kèm giờ hết hạn giữ (ví dụ 10 phút).
2. Hạn thanh toán của cổng nên **bằng hoặc ngắn hơn** hạn giữ chỗ.
3. Thanh toán thành công thì chuyển chỗ sang **đã bán / đã đặt**, cùng lúc với việc đơn thành đã thanh toán.
4. Thất bại, hủy hoặc quá hạn thì **nhả chỗ**. Dùng tác vụ chạy định kỳ để quét các đơn `pending` đã quá hạn.
5. Khi cập nhật đã thanh toán, kiểm tra chỗ đó **vẫn đang thuộc về đơn này**, tránh hai người cùng mua một chỗ.

**Từ khóa nên tìm hiểu:** *seat reservation hold timeout*, *race condition double booking*, *pessimistic locking*, *Laravel scheduler*.

---

## 7. Những điều về bảo mật không được bỏ

- Khóa bí mật chỉ nằm trong `.env`, không commit. Lộ khóa thì người khác giả mạo được giao dịch.
- Với VNPAY, **luôn kiểm tra chữ ký** của mọi phản hồi.
- **Số tiền lấy từ cơ sở dữ liệu**, không nhận từ trình duyệt.
- Đối chiếu đơn, số tiền, trạng thái trước khi đánh dấu đã thanh toán.
- Xử lý IPN hoặc capture **an toàn khi bị lặp** (giao dịch cơ sở dữ liệu, kiểm tra trạng thái).
- Không tin trang "thành công" ở trình duyệt.
- Ghi log để tra lỗi nhưng **không ghi khóa và không ghi thông tin thẻ**.
- **Không bao giờ lưu số thẻ.** Thẻ được nhập ở trang của cổng, không đi qua website của bạn.

---

## 8. Cách kiểm tra chức năng

Nhóm nên thử các tình huống sau:

1. Thanh toán thành công bằng thẻ test (VNPAY) hoặc tài khoản sandbox (PayPal): đơn thành đã thanh toán.
2. Khách hủy ở cổng: đơn không thành đã thanh toán, cho thử lại.
3. Nhập sai OTP hoặc thẻ (VNPAY): đơn báo thất bại, thông báo rõ.
4. Để quá hạn không trả: đơn hết hạn, nhả chỗ.
5. Sửa một tham số trên đường link ReturnUrl: chữ ký sai, báo dữ liệu không hợp lệ.
6. Gọi IPN hai lần cùng kết quả: chỉ cập nhật một lần, lần sau trả mã `02`.
7. IPN có số tiền khác đơn: bị từ chối.
8. Đóng trình duyệt sau khi trả tiền: đơn vẫn được ghi nhận nhờ IPN (VNPAY) hoặc đối chiếu lại (PayPal).
9. Hai người cùng chọn một ghế hoặc khung giờ: chỉ một người thanh toán được.
10. Thanh toán lại đơn đã thanh toán: bị chặn.

---

## 9. Lỗi thường gặp và cách xử lý

- **VNPAY báo sai chữ ký:** thường do không sắp xếp tham số, encode khác nhau giữa lúc ký và lúc gửi, hoặc sai HashSecret. Ký trên đúng chuỗi đã sắp xếp và encode, kiểm tra lại HashSecret.
- **VNPAY báo sai số tiền:** quên nhân 100 khi gửi.
- **Báo trùng mã giao dịch:** `vnp_TxnRef` bị lặp trong ngày, hãy sinh mã mới mỗi lần thử.
- **Trả tiền xong nhưng đơn vẫn `pending`:** IPN không tới được, thường do đang chạy `localhost`, địa chỉ IPN sai hoặc không phải HTTPS. Dùng đường hầm và khai báo lại địa chỉ IPN.
- **`vnp_OrderInfo` bị lỗi:** có dấu tiếng Việt hoặc ký tự đặc biệt, hãy bỏ dấu và bỏ ký tự đặc biệt.
- **PayPal báo sai tiền tệ:** đang gửi VND, đổi sang USD.
- **PayPal lỗi 401 khi tạo order:** token hết hạn, sai Client ID hoặc Secret, hoặc dùng khóa Live với địa chỉ sandbox. Lấy token mới, dùng khóa Sandbox với địa chỉ sandbox.
- **Nút PayPal không hiện:** sai Client ID trong thư viện hoặc sai tiền tệ.
- **Sửa `.env` không có tác dụng:** cấu hình bị cache, hãy xóa cache.
- **Đơn bị đánh dấu đã thanh toán hai lần:** thiếu giao dịch cơ sở dữ liệu hoặc thiếu kiểm tra trạng thái trước khi cập nhật.

---

## 10. Danh sách việc cần làm

**Chuẩn bị**
- [ ] Đã có đơn hàng với trạng thái và tổng tiền do server tính
- [ ] Chọn cổng: VNPAY hoặc PayPal
- [ ] Đã lấy khóa sandbox, để trong `.env`, không commit
- [ ] (VNPAY, chạy local) có đường hầm HTTPS để nhận IPN

**Cài đặt**
- [ ] Bảng thanh toán và các trạng thái đơn
- [ ] Tạo thanh toán và chuyển hướng (VNPAY) hoặc hiện nút (PayPal)
- [ ] Xử lý kết quả (IPN hoặc capture) có kiểm tra chữ ký, số tiền, trạng thái
- [ ] Trang kết quả cho khách
- [ ] Hạn thanh toán và nhả chỗ khi quá hạn

**Kiểm tra và tài liệu**
- [ ] Chạy đủ các tình huống ở mục 8
- [ ] Ghi vào tài liệu: cổng dùng, tỷ giá (nếu PayPal), thẻ hoặc tài khoản test, các giả định
- [ ] Đưa tài khoản và thẻ **sandbox** vào phần tài khoản test, **không** đưa khóa bí mật

---

## 11. Gợi ý chia việc trong nhóm

Một người đăng ký sandbox, lấy khóa và dựng đường hầm HTTPS trước tiên. Một người làm bảng thanh toán, trạng thái đơn và phần giữ, nhả chỗ (thường là người làm đặt vé hoặc hóa đơn). Một người làm phần tạo thanh toán và xử lý IPN hoặc capture. Một người làm trang thanh toán và trang kết quả song song. Cuối cùng, một người **khác** với người viết code kiểm tra theo mục 8.

---

## Nguồn tham khảo

- Tài liệu thanh toán VNPAY (PAY): https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html
- Trang demo và thẻ test VNPAY: https://sandbox.vnpayment.vn/apis/vnpay-demo/
- Bài hướng dẫn tích hợp VNPAY với Laravel trên Viblo (nguồn nhắc tới trang đăng ký sandbox): https://viblo.asia/p/tich-hop-cong-thanh-toan-vnpay-voi-laravel-Az45bGD6KxY
- Orders API v2 của PayPal: https://developer.paypal.com/docs/api/orders/v2/
- PayPal, lấy access token: https://developer.paypal.com/api/rest/authentication/
- PayPal, tài khoản sandbox và Client ID/Secret: https://developer.paypal.com/api/rest/
- PayPal, tiền tệ hỗ trợ (không có VND): https://developer.paypal.com/docs/reports/reference/paypal-supported-currencies/
