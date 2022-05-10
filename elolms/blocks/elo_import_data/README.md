# elolms-2021-report-cn01
Chức năng: Nhập dữ liệu người dùng từ file excel vào hệ thống elolms  
Tên plugin: ELO: Nhập dữ liệu từ file excel  
**Yêu cầu**: Đã cài đặt các trường dữ liệu chưa . Chưa cài thì cài qua link `https://github.com/ELOLMS-PROCDUCTION/elolms-2021-caidatchitietthongtinnguoidung-02` . Nếu chưa cài đặt các trường dữ liệu thì export sẽ bị lỗi. Còn cài đặt rồi thì bỏ qua bước này.  
**Hướng dẫn cài đặt và sử dụng dành cho Tổ IT**  
B1: Cài đặt plugin  

B2: Admin Phân quyền người dùng để cho người dùng có thể hiển thị plugin ra dashboard sử dụng nếu tổ giáo vụ sử dụng plugin này  
    Ví dụ tài khoản cho phép sử dụng chức năng này Trợ giảng (Ngôn ngữ Anh) ELO (trogiangnna_elo@oude.edu.vn)
    ![image](https://user-images.githubusercontent.com/84503105/136724520-8dc53193-4df3-4b12-9a81-a7f13779e926.png)  
    Chọn quản lý các vai trò -> Biên soạn  
    ![image](https://user-images.githubusercontent.com/84503105/136724814-bd0ca639-3684-4dca-a23f-b9aca0e70652.png)  
    Search block/elo_import_data:viewindashboard .Chọn Allow  
    ![image](https://user-images.githubusercontent.com/84503105/136731234-9af9df67-5aa8-4f6c-90c7-2da0bbbf86f1.png)  
    
B3: Hiển thị plugin  
    
B4: Quét chọn dữ liệu từ file excel (file này được tổ giáo vụ gửi) và copy (Ctrl+ C)
![image](https://user-images.githubusercontent.com/84503105/120777890-fb0db580-c54f-11eb-8193-fe5efa4cbc4c.png)

**Lưu ý** : Yêu cầu bắt buộc phải copy các tiêu đề kèm với các dòng dữ liệu như hình bên dưới
![image](https://user-images.githubusercontent.com/84503105/120778324-66f01e00-c550-11eb-9f08-bdc591d1d523.png)


B5: Dán vào giao diện plugin như hình (Ctrl+ V)
![image](https://user-images.githubusercontent.com/84503105/120782833-9a34ac00-c554-11eb-9762-32b715b02b4e.png)

 - Xem trước dữ liệu. Chọn hiển thị bao nhiêu dòng
 
   ![image](https://user-images.githubusercontent.com/84503105/120787463-96efef00-c559-11eb-8b03-347e5ef53d99.png)

 
 - Nhấn vào nút **Tiếp tục** để chuyển sang bước tiếp theo

B6: Hệ thống sẽ hiển thị 1 bảng để xem trước giống như trong file excel như hình bên dưới 

![image](https://user-images.githubusercontent.com/84503105/120778538-a0288e00-c550-11eb-96f7-4ce4b7c82383.png)


B7: Tùy chọn các trường tương ứng ở file excel trùng khớp với các trường dữ liệu cần cập nhật

**Bước chọn này quan trọng vì để xác định user có tồn tại trong cơ sở dữ liệu elolms hay không ? Người dùng đã được tạo trong cơ sở dữ liệu của elolms thì được coi như tồn tại**
**Nếu trong danh sách file excel có 1 user không tồn tại thì sẽ ko cập nhật và hệ thống sẽ đưa ra thông báo cụ thể. Người import sẽ phải quay lại B4**

**Nhận dạng người dùng bởi** (**BẮT BUỘC PHẢI CHỌN**)
- Nối từ : email của file excel ( ví dụ ở đây chọn là MAIL OU)
- Nối đến: Mặc định Chọn thư điện tử (email)

**Các móc nối mục điểm**
Có 2 cột 
 - Cột bên trái là liệt kê theo thứ tự các tiêu đề của file excel. **File excel đặt như thế nào thì sẽ hiển thị y hệt**
 - Cột bên phải là danh sách chọn các trường dữ liệu mình cần import
Ví dụ cụ thể : 
Cột MSSV của file excel tương ứng với mã số sinh viên chúng ta muốn cập nhật. Như hình bên dưới
![image](https://user-images.githubusercontent.com/84503105/120780322-30b39e00-c552-11eb-8642-5792b325c723.png)

- Cột KHÓA sẽ tương ứng với cột khóa 
- Cột NS tương ứng với cột ngày sinh
- Cột Họ và tên đệm tương ứng với cột họ và tên đệm  
- Cột Tên tương ứng với cột tên  
- Cột MÃ LỚP tương ứng với cột tên lớp
- Cột nhóm lớp tương ứng với 
- Cột NGÀNH tương ứng với Ngành học

- Cột VB tương ứng với Loại văn bằng
- Cột SĐT tương ứng với số điện thoại di động

- Cột GT tương ứng với cột giới tính

**Lưu ý : Cột nào không cần cập nhật thì chọn bỏ qua (để mặc định)**
![image](https://user-images.githubusercontent.com/84503105/120781628-70c75080-c553-11eb-9e92-afb8c8aec057.png)

B8: Sau khi kiểm tra mọi thứ thì ta Nhấn vào nút **Cập nhật trường dữ liệu**
 Cập nhật thành công 
![image](https://user-images.githubusercontent.com/84503105/120784052-dfa5a900-c555-11eb-84bc-a457e1b82f9b.png)
 Một số lỗi thường gặp 
  - User không tồn tại : Loại bỏ user khỏi danh sách file excel hoặc kiểm tra email của user trong file exxcel có chính xác hay chưa.
   ![image](https://user-images.githubusercontent.com/84503105/120786713-bfc3b480-c558-11eb-912c-4270d812e719.png)


B9: Kiểm tra random user sinh viên

- Dữ liệu từ file excel 
 ![image](https://user-images.githubusercontent.com/84503105/120784492-5642a680-c556-11eb-8f99-ed8d7d9735f0.png)

- Thông tin sinh viên sau khi được cập nhật 
 ![image](https://user-images.githubusercontent.com/84503105/120784551-678bb300-c556-11eb-9c67-3378087c71d8.png)
 
 Hướng dẫn sử dụng dành cho giáo vụ hoặc giáo viên
 `https://docs.google.com/document/d/1okUkkzrmog5Bh_5x0knzWEN71ZmVykGP2cw-1gAWRK0/edit`

