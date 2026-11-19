# TÀI LIỆU ĐẶC TẢ YÊU CẦU PHẦN MỀM (SRS / SPECS)
**Dự án:** CineBook – Hệ thống Đặt vé Xem phim Trực tuyến  
**Phiên bản:** 1.0  
**Nền tảng công nghệ:** PHP (Laravel Framework), MySQL 8.0+, HTML5/CSS3/Bootstrap/JavaScript  
**Trạng thái:** Bản hoàn thiện đặc tả nghiệp vụ & chức năng

---

## MỤC LỤC
1. [Tổng quan & Bài toán (Problem Statement)](#1-tổng-quan--bài-toán-problem-statement)
2. [Đối tượng sử dụng (Actors & Personas)](#2-đối-tượng-sử-dụng-actors--personas)
3. [Quy tắc & Luồng nghiệp vụ (Business Rules & Workflows)](#3-quy-tắc--luồng-nghiệp-vụ-business-rules--workflows)
4. [Đặc tả Chức năng chi tiết (Functional Requirements)](#4-đặc-tả-chức-năng-chi-tiết-functional-requirements)
5. [Các câu chuyện người dùng (User Stories & Acceptance Criteria)](#5-các-câu-chuyện-người-dùng-user-stories--acceptance-criteria)
6. [Yêu cầu Phi chức năng (Non-Functional Requirements)](#6-yêu-cầu-phi-chức-năng-non-functional-requirements)
7. [Giả định, Ràng buộc & Yêu cầu Hệ thống](#7-giả-định-ràng-buộc--yêu-cầu-hệ-thống)

---

## 1. Tổng quan & Bài toán (Problem Statement)

### 1.1 Bối cảnh & Vấn đề thực tế
Điện ảnh là một trong những hình thức giải trí phổ biến nhất hiện nay. Tuy nhiên, quy trình đặt vé truyền thống đang tồn tại nhiều bất cập gây giảm trải nghiệm của khán giả:
* Khách hàng phải xếp hàng dài chờ đợi tại quầy vé, đặc biệt vào các khung giờ cao điểm hoặc dịp cuối tuần.
* Thường xuyên gặp tình trạng hết vé hoặc không còn vị trí ghế đẹp vào phút chót.
* Phải truy cập và chuyển đổi giữa nhiều trang web khác nhau để xem trailer, đọc đánh giá phim (review) và tra cứu lịch chiếu thực tế tại từng rạp.

### 1.2 Giải pháp (CineBook)
**CineBook** là một ứng dụng Web hợp nhất giúp giải quyết triệt để các vấn đề trên. Hệ thống cho phép người dùng khám phá phim đang chiếu/sắp chiếu, xem trailer, tham khảo đánh giá từ cộng đồng, theo dõi lịch chiếu theo thời gian thực và đặt vé trực tuyến tức thì. Đồng thời, hệ thống cung cấp bộ công cụ quản trị mạnh mẽ cho nhà quản lý rạp (Admin) để điều phối suất chiếu, áp dụng cơ chế định giá vé linh hoạt và theo dõi báo cáo doanh thu.

### 1.3 Mục tiêu & Phạm vi dự án
* **Mục tiêu:** Xây dựng ứng dụng web hoàn chỉnh, trực quan, dễ sử dụng, hoạt động ổn định và đáp ứng đầy đủ quy trình từ tìm kiếm phim đến xuất vé điện tử (E-ticket).
* **Phạm vi (In-Scope):**
  * Giao diện người dùng (Client Portal): Trang chủ, Sitemap, Tìm kiếm/Lọc phim, Chi tiết phim, Chọn ghế & Đặt vé, Thanh toán giả lập (Dummy Payment), Vé điện tử, Đánh giá/Bình luận, Quản lý tài khoản và Thông báo.
  * Giao diện quản trị (Admin Portal): Quản lý Rạp, Phim, Suất chiếu, Cấu hình giá vé động, Kiểm duyệt bình luận và Thống kê báo cáo.
* **Ngoài phạm vi (Out-of-Scope):** Tích hợp cổng thanh toán ngân hàng thật (chỉ sử dụng thanh toán giả lập), bán bắp nước/combo đồ ăn (có thể mở rộng ở giai đoạn sau), ứng dụng di động (iOS/Android).

---

## 2. Đối tượng sử dụng (Actors & Personas)

| Vai trò (Actor) | Mô tả đối tượng | Quyền hạn chính trong hệ thống |
| :--- | :--- | :--- |
| **Khách vãng lai (Guest)** | Người dùng chưa đăng nhập hoặc chưa có tài khoản. | Xem trang chủ, Sitemap, tìm kiếm/lọc phim, xem chi tiết phim, trailer, đọc đánh giá, đăng ký tài khoản mới. |
| **Khách hàng thành viên (Registered User)** | Khán giả xem phim thường xuyên hoặc người đam mê điện ảnh đã có tài khoản. | Toàn bộ quyền của Guest + Cập nhật hồ sơ, đặt vé trực tuyến, xem vé điện tử (E-ticket), xem lịch sử đặt vé, đánh giá sao (1-5) & viết review, nhận thông báo. |
| **Quản trị viên (Admin)** | Nhân sự quản lý vận hành hệ thống rạp chiếu phim. | Đăng nhập trang quản trị, CRUD (Thêm/Sửa/Xóa) Rạp, Phim, Suất chiếu; cấu hình giá vé động; kiểm duyệt/xóa đánh giá vi phạm; xem & xuất báo cáo doanh thu, lượng vé. |

---

## 3. Quy tắc & Luồng nghiệp vụ (Business Rules & Workflows)

### 3.1 Quy tắc nghiệp vụ cốt lõi (Business Rules)

* **BR-01 (Xác thực khi đặt vé & đánh giá):** Chỉ người dùng đã đăng nhập (Registered User) mới được phép tiến hành giữ ghế, thanh toán đặt vé và gửi đánh giá/xếp hạng phim.
* **BR-02 (Hạng ghế & Chọn nhiều ghế):** Hệ thống hỗ trợ 3 hạng ghế tiêu chuẩn: **Gold**, **Platinum**, và **Box**. Người dùng có thể chọn cùng lúc nhiều ghế trong một giao dịch duy nhất, miễn là các ghế đó đang ở trạng thái "Trống" (Available).
* **BR-03 (Định giá động - Dynamic Pricing):** Giá vé cuối cùng của một ghế được tính linh hoạt dựa trên 3 yếu tố: Ngày chiếu (Ngày thường vs. Cuối tuần), Khung giờ (Giờ thường vs. Giờ cao điểm) và Hạng ghế.
  * Công thức tham khảo:
    $$P_{\text{final}} = P_{\text{base}} \times K_{\text{day}} \times K_{\text{time}} + \Delta_{\text{seat}}$$
    *(Trong đó: $P_{\text{base}}$ là giá cơ bản của suất chiếu, $K_{\text{day}}$ là hệ số cuối tuần, $K_{\text{time}}$ là hệ số giờ cao điểm, $\Delta_{\text{seat}}$ là phụ thu hạng ghế Gold/Platinum/Box).*
* **BR-04 (Xác nhận đặt vé & Thanh toán giả lập):** Đơn đặt vé (Booking) chỉ chuyển sang trạng thái **Thành công (Confirmed)** và sinh mã **Vé điện tử (E-ticket)** sau khi bước thanh toán giả lập (Dummy Payment) trả về kết quả thành công. Nếu thất bại hoặc hủy, trạng thái ghế phải được giải phóng ngay lập tức.
* **BR-05 (Kiểm duyệt nội dung):** Đánh giá của người dùng phải nằm trong thang điểm từ $1$ đến $5$ sao. Admin có toàn quyền ẩn hoặc xóa các bình luận chứa ngôn từ không phù hợp/spam.
* **BR-06 (Thông báo nhắc lịch):** Hệ thống tự động gửi thông báo nhắc lịch xem phim cho người dùng trước giờ chiếu **2 tiếng**.

### 3.2 Luồng nghiệp vụ tổng quát (End-to-End Booking Flow)
1. **Khám phá:** Người dùng vào Trang chủ $\rightarrow$ Tìm kiếm/Lọc phim theo Thành phố, Thể loại, Ngôn ngữ, Đánh giá hoặc Ngày chiếu.
2. **Xem chi tiết:** Chọn phim $\rightarrow$ Xem Trailer, Tóm tắt, Diễn viên/Đạo diễn và Đánh giá từ người xem trước.
3. **Chọn suất chiếu:** Chọn Rạp $\rightarrow$ Chọn Ngày & Giờ chiếu.
4. **Chọn ghế:** Hệ thống tải Sơ đồ ghế theo thời gian thực $\rightarrow$ Người dùng chọn 1 hoặc nhiều ghế (Gold/Platinum/Box) $\rightarrow$ Hệ thống tự động tính tổng tiền theo quy tắc giá động.
5. **Thanh toán (Dummy):** Người dùng xác nhận thông tin $\rightarrow$ Chuyển sang màn hình thanh toán giả lập $\rightarrow$ Chọn xác nhận thanh toán.
6. **Xuất vé & Thông báo:** Hệ thống lưu Booking, khóa ghế vĩnh viễn cho suất chiếu đó, tạo E-ticket (kèm Booking ID), lưu vào Lịch sử đặt vé trong Hồ sơ cá nhân và gửi thông báo đặt vé thành công.

---

## 4. Đặc tả Chức năng chi tiết (Functional Requirements)

| Mã YC | Phân hệ | Tên chức năng | Mô tả chi tiết yêu cầu |
| :--- | :--- | :--- | :--- |
| **FR-01** | **Trang chủ (Home Page)** | Hiển thị & Điều hướng | - Hiển thị danh sách Phim nổi bật/thịnh hành (Đang chiếu & Sắp chiếu) kèm poster và trailer.<br>- Thanh tìm kiếm thông minh & bộ lọc nhanh (theo phim, rạp, thành phố, thể loại).<br>- Khu vực Khuyến mãi & Thông báo (ưu đãi, giảm giá, sự kiện lễ hội).<br>- Tích hợp **Sitemap** ngay trên trang chủ để người dùng nắm bắt cấu trúc web. |
| **FR-02** | **Tài khoản & Xác thực** | Đăng ký, Đăng nhập & Hồ sơ | - Đăng ký tài khoản mới bằng Email và Số điện thoại.<br>- Đăng nhập bảo mật bằng mật khẩu.<br>- Cơ chế Quên mật khẩu / Đổi mật khẩu.<br>- Cập nhật hồ sơ cá nhân: Họ tên, Tuổi, Ngôn ngữ ưa thích, Thành phố ưa thích. |
| **FR-03** | **Duyệt & Tìm kiếm Phim** | Lọc, Tìm kiếm & Chi tiết phim | - Hiển thị danh sách phim đang chiếu và sắp chiếu.<br>- Bộ lọc đa tiêu chí: Thành phố, Thể loại (Hành động, Tâm lý, Hài...), Ngôn ngữ (Tiếng Việt, Anh, Hàn...), Điểm đánh giá, Ngày chiếu (hiện tại & tương lai).<br>- Tìm kiếm theo Tên phim, Diễn viên hoặc Đạo diễn.<br>- Trang Chi tiết phim: Trailer, Tóm tắt nội dung (Synopsis), Dàn diễn viên & ekip (Cast & crew), Đánh giá & xếp hạng (sắp xếp theo mới nhất hoặc điểm cao nhất). |
| **FR-04** | **Đặt vé (Ticket Booking)** | Chọn suất & Sơ đồ ghế | - Cho phép chọn: Địa điểm rạp, Phim, Ngày & giờ chiếu, Hạng ghế (Gold, Platinum, Box).<br>- Hiển thị sơ đồ ghế trực quan phân biệt rõ ghế trống, ghế đang chọn và ghế đã đặt.<br>- Cho phép chọn nhiều ghế trong 1 giao dịch.<br>- Xác nhận đặt vé sau khi thanh toán giả lập (dummy payment) thành công. |
| **FR-05** | **Vé điện tử (E-Ticketing)** | Tạo & Quản lý vé | - Tự động xuất E-ticket sau khi thanh toán thành công.<br>- Thông tin trên E-ticket: Tên phim, Rạp chiếu, Suất chiếu, Số ghế & Hạng ghế, Mã đặt vé (Booking ID).<br>- Lưu trữ để người dùng xem lại các vé đã xem (quá khứ) và vé sắp chiếu trong Hồ sơ cá nhân. |
| **FR-06** | **Đánh giá & Xếp hạng** | Viết Review & Kiểm duyệt | - Người dùng đã đăng nhập có thể chấm điểm từ $1$ đến $5$ sao và viết nhận xét.<br>- Hỗ trợ sắp xếp danh sách đánh giá theo: Mới nhất hoặc Điểm cao nhất.<br>- Admin có quyền kiểm duyệt/xóa các đánh giá không phù hợp. |
| **FR-07** | **Quản trị (Admin Management)** | Quản lý CRUD, Giá động & Báo cáo | - Đăng nhập riêng biệt cho Admin.<br>- **CRUD Rạp chiếu:** Thành phố, địa chỉ, sức chứa ghế.<br>- **CRUD Phim:** Tên phim, poster, thể loại, thời lượng, link trailer.<br>- **CRUD Suất chiếu:** Ngày, giờ, mức giá theo từng hạng ghế.<br>- **Cấu hình giá động:** Theo cuối tuần/ngày thường, giờ cao điểm/giờ thường, hạng ghế.<br>- **Báo cáo thống kê:** Doanh số bán vé (ngày/tuần/tháng), doanh thu theo từng rạp, danh sách phim phổ biến nhất, thống kê người dùng đăng ký và các đơn đặt vé đang hoạt động. |
| **FR-08** | **Thông báo (Notifications)** | Gửi thông báo hệ thống | - Gửi thông báo trạng thái Đặt vé thành công / thất bại.<br>- Gửi nhắc nhở suất chiếu sắp diễn ra (trước 2 giờ).<br>- Gửi thông báo chương trình khuyến mãi (ưu đãi dịp lễ, giảm giá). |

---

## 5. Các câu chuyện người dùng (User Stories & Acceptance Criteria)

Phần này mô tả chi tiết các kịch bản thực tế dưới góc nhìn của từng nhóm người dùng để đội ngũ lập trình (Dev) và kiểm thử (QA) bám sát khi triển khai.

### Epic 1: Khám phá phim & Trải nghiệm Trang chủ
* **US-01: Tìm kiếm và lọc phim nhanh chóng**
  * **Câu chuyện:** *Là một Khách vãng lai, tôi muốn lọc danh sách phim theo Thành phố, Thể loại, Ngôn ngữ và Ngày chiếu, cũng như tìm theo tên diễn viên/đạo diễn, để tôi nhanh chóng chọn được bộ phim phù hợp với sở thích của mình.*
  * **Tiêu chí chấp nhận (AC):**
    * Nhập từ khóa tên phim, diễn viên hoặc đạo diễn vào thanh tìm kiếm trả về kết quả khớp chính xác trong dưới 2 giây.
    * Kết hợp nhiều bộ lọc cùng lúc (VD: Thành phố = TP.HCM + Thể loại = Hành động + Ngôn ngữ = Tiếng Anh) hiển thị đúng danh sách phim tương ứng.
    * Nếu không có phim phù hợp, hiển thị thông báo thân thiện thay vì trang trắng.

* **US-02: Xem chi tiết phim trước khi quyết định đặt vé**
  * **Câu chuyện:** *Là một Khách xem phim, tôi muốn xem trailer, nội dung tóm tắt, danh sách diễn viên và đọc đánh giá từ người khác trên cùng một trang, để tôi không phải mở nhiều website khác nhau.*
  * **Tiêu chí chấp nhận (AC):**
    * Trang chi tiết phim phát được trailer nhúng (YouTube/Video).
    * Hiển thị đầy đủ tóm tắt, ekip và điểm đánh giá trung bình.
    * Cho phép chuyển đổi sắp xếp review theo "Mới nhất" và "Đánh giá cao nhất".

### Epic 2: Tài khoản & Hồ sơ cá nhân
* **US-03: Đăng ký & Đăng nhập tài khoản**
  * **Câu chuyện:** *Là một Người dùng mới, tôi muốn đăng ký tài khoản bằng Email và Số điện thoại để có thể đặt vé trực tuyến và lưu lại lịch sử xem phim.*
  * **Tiêu chí chấp nhận (AC):**
    * Hệ thống kiểm tra định dạng Email hợp lệ, không cho phép đăng ký trùng Email đã tồn tại.
    * Mật khẩu phải được mã hóa trước khi lưu vào cơ sở dữ liệu.
    * Hỗ trợ tính năng khôi phục/đổi mật khẩu khi người dùng quên hoặc muốn cập nhật.

* **US-04: Cá nhân hóa hồ sơ**
  * **Câu chuyện:** *Là một Thành viên, tôi muốn cập nhật tên, tuổi, ngôn ngữ và thành phố ưa thích trong hồ sơ, để hệ thống hiển thị thông tin phù hợp với khu vực tôi đang sống.*
  * **Tiêu chí chấp nhận (AC):**
    * Thông tin cập nhật được lưu thành công vào DB và hiển thị lại đúng sau khi tải lại trang.

### Epic 3: Đặt vé, Thanh toán & Vé điện tử
* **US-05: Chọn ghế trực quan và tính giá tự động**
  * **Câu chuyện:** *Là một Thành viên, tôi muốn xem sơ đồ ghế trống theo thời gian thực và chọn cùng lúc nhiều ghế (Gold, Platinum, Box) cho nhóm bạn của mình, đồng thời thấy ngay tổng số tiền cần trả.*
  * **Tiêu chí chấp nhận (AC):**
    * Các ghế đã có người đặt bị vô hiệu hóa (không thể click chọn).
    * Khi chọn/bỏ chọn ghế, danh sách số ghế và tổng tiền cập nhật tức thì theo đúng quy tắc giá động (cuối tuần/giờ cao điểm/hạng ghế).

* **US-06: Thanh toán giả lập và nhận Vé điện tử (E-Ticket)**
  * **Câu chuyện:** *Là một Thành viên, sau khi chọn ghế xong, tôi muốn thực hiện thanh toán giả lập và nhận ngay vé điện tử chứa đầy đủ thông tin để sử dụng khi đến rạp.*
  * **Tiêu chí chấp nhận (AC):**
    * Khi bấm "Thanh toán thành công" (Dummy), hệ thống tạo ra một mã Booking ID duy nhất.
    * Màn hình E-ticket hiển thị rõ: Tên phim, Rạp, Giờ chiếu, Số ghế, Hạng ghế và Booking ID.
    * Vé mới đặt xuất hiện ngay trong mục "Vé sắp chiếu" ở trang Hồ sơ cá nhân và người dùng nhận được thông báo đặt vé thành công.

### Epic 4: Đánh giá & Tương tác
* **US-07: Chấm điểm và viết nhận xét phim**
  * **Câu chuyện:** *Là một Thành viên, tôi muốn chấm điểm từ 1-5 sao và viết cảm nhận về bộ phim để chia sẻ trải nghiệm với cộng đồng.*
  * **Tiêu chí chấp nhận (AC):**
    * Khách chưa đăng nhập khi bấm viết đánh giá sẽ được điều hướng sang trang Đăng nhập.
    * Đánh giá sau khi gửi hiển thị ngay trên trang chi tiết phim và cập nhật lại điểm sao trung bình.

### Epic 5: Quản trị hệ thống (Admin)
* **US-08: Quản lý danh mục Rạp, Phim và Suất chiếu**
  * **Câu chuyện:** *Là một Quản trị viên, tôi muốn thêm, sửa, xóa thông tin Rạp chiếu, Phim và lên lịch Suất chiếu để luôn cập nhật lịch chiếu mới nhất cho khách hàng.*
  * **Tiêu chí chấp nhận (AC):**
    * Các thao tác Thêm/Sửa/Xóa có bước kiểm tra dữ liệu đầu vào (validation) và thông báo kết quả rõ ràng.
    * Thay đổi trên trang Admin được đồng bộ ngay lập tức ra trang Khách hàng.

* **US-09: Cấu hình giá vé động & Xem báo cáo kinh doanh**
  * **Câu chuyện:** *Là một Quản trị viên, tôi muốn thiết lập mức giá riêng cho cuối tuần, giờ cao điểm, từng hạng ghế và xem báo cáo doanh thu theo ngày/tuần/tháng để tối ưu hóa lợi nhuận cho hệ thống rạp.*
  * **Tiêu chí chấp nhận (AC):**
    * Cấu hình giá mới áp dụng chính xác cho các giao dịch đặt vé phát sinh sau thời điểm cập nhật.
    * Bảng báo cáo thống kê chính xác: Doanh thu theo từng rạp, lượng vé bán ra theo ngày/tuần/tháng, top phim phổ biến nhất và số lượng người dùng/đơn đặt vé đang hoạt động.

* **US-10: Kiểm duyệt đánh giá**
  * **Câu chuyện:** *Là một Quản trị viên, tôi muốn xóa các bình luận mang tính xúc phạm hoặc spam để giữ môi trường đánh giá phim lành mạnh.*
  * **Tiêu chí chấp nhận (AC):**
    * Sau khi Admin xóa đánh giá, bình luận đó biến mất khỏi trang chi tiết phim và điểm trung bình của phim được tính lại.

---

## 6. Yêu cầu Phi chức năng (Non-Functional Requirements)

| Tiêu chí | Yêu cầu cụ thể |
| :--- | :--- |
| **An toàn sử dụng (Safe to use)** | Ứng dụng web tuyệt đối không chứa mã độc, không tự động tải xuống các tệp tin không mong muốn hoặc độc hại về máy người dùng. |
| **Khả năng tiếp cận (Accessible)** | Phông chữ rõ ràng, dễ đọc, độ tương phản tốt; các thành phần giao diện (UI) và thanh điều hướng (navigation) trực quan. |
| **Thân thiện người dùng (User-friendly)** | Bố cục khoa học, menu rõ ràng, quy trình đặt vé tối giản số bước giúp cả người dùng phổ thông dễ dàng thao tác. |
| **Khả năng vận hành (Operability)** | Hệ thống vận hành ổn định, xử lý nghiệp vụ chính xác, không phát sinh lỗi treo trang trong quá trình sử dụng thông thường. |
| **Hiệu suất (Performance)** | Tốc độ tải trang nhanh, chuyển trang mượt mà, truy vấn tìm kiếm phim và tải sơ đồ ghế có độ trễ thấp. |
| **Bảo mật (Security)** | Áp dụng cơ chế xác thực (Authentication) và phân quyền (Authorization) chặt chẽ giữa Guest, User và Admin; bảo vệ mật khẩu và dữ liệu người dùng. |
| **Tính sẵn sàng (Availability)** | Hệ thống được thiết kế để hoạt động liên tục $24/7$ với thời gian gián đoạn (downtime) ở mức tối thiểu. |

---

## 7. Giả định, Ràng buộc & Yêu cầu Hệ thống

### 7.1 Giả định (Assumptions)
* Chức năng thanh toán trong phạm vi dự án là **thanh toán giả lập (Dummy Payment)**, không kết nối với cổng thanh toán ngân hàng thực tế.
* Tính năng gửi thông báo (đặt vé thành công, nhắc lịch trước 2 tiếng, khuyến mãi) có thể được hiển thị dưới dạng thông báo trong ứng dụng (In-app Notification) hoặc gửi qua Email giả lập (Mailtrap/SMTP test).
* Dữ liệu phim, rạp chiếu và tài khoản mẫu (Admin, User) sẽ được chuẩn bị sẵn trong file kịch bản cơ sở dữ liệu (`.sql`) để phục vụ kiểm thử.

### 7.2 Yêu cầu Phần cứng & Phần mềm (Hardware / Software)
* **Phần cứng tối thiểu:** Vi xử lý Intel Core i5/i7 trở lên, RAM tối thiểu 8 GB, Màn hình màu SVGA, Ổ cứng trống 500 GB, Chuột và Bàn phím.
* **Phần mềm & Công nghệ đề xuất:**
  * **Frontend:** HTML5, CSS3, Bootstrap, JavaScript, jQuery (hoặc ReactJS 18+).
  * **Backend:** PHP 7.2+ với Laravel Framework.
  * **Cơ sở dữ liệu:** MySQL 8.0+.

### 7.3 Sản phẩm bàn giao (Deliverables)
Khi hoàn tất dự án, nhóm cần đóng gói đầy đủ các thành phần sau (lưu ý: *tài liệu báo cáo không chứa mã nguồn*):
1. **Tài liệu báo cáo dự án (Project Report):** Bao gồm Định nghĩa bài toán, Đặc tả thiết kế, Sơ đồ luồng (Flowcharts, DFD), Thiết kế Cơ sở dữ liệu (ERD), Dữ liệu kiểm thử, Hướng dẫn cài đặt và Danh sách tài khoản đăng nhập cho từng vai trò.
2. **Tệp nén dự án (`.zip`):** Chứa mã nguồn hoàn chỉnh, tệp `ReadMe.doc` (liệt kê các giả định của nhóm) và các tệp kịch bản cơ sở dữ liệu (`.sql`).
3. **Video demo (`.mp4`):** Quay lại toàn bộ quá trình hoạt động thực tế của ứng dụng web.
4. **Đường dẫn triển khai (URL):** Khuyến khích host ứng dụng lên môi trường web để giảng viên đánh giá trực tiếp.