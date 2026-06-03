# Các quy tắc tuyệt đối KHÔNG vi phạm

```
1.  KHÔNG hard delete bất kỳ PHI record nào — chỉ soft delete hoặc logical deactivate
2.  KHÔNG log PHI vào application logs (names, DOB, MRN, phone, email, IP)
3.  KHÔNG gửi PHI trong URL/query string — dùng POST body hoặc encrypted path param
4.  KHÔNG share user accounts — mỗi nhân viên một account với MFA
5.  KHÔNG lưu plain text password hoặc credentials trong code/config/log
6.  KHÔNG để CDS alert block việc đặt lệnh — CDS là advisory, không bao giờ interrupt
7.  KHÔNG random split data cho analytics — dùng temporal split
8.  KHÔNG cache PHI response không có expiry ngắn (max 5 phút)
9.  KHÔNG skip audit log cho bất kỳ PHI access nào dù là internal job
10. KHÔNG hard-code terminology codes (ICD, LOINC) trong code — dùng lookup table
```
