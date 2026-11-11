#!/usr/bin/env bash
# Kiểm thử đầu-cuối theo checklist CLAUDE.md mục 12 (task #36 / #39).
#   bash tests/e2e.sh                          (dùng php trong PATH)
#   PHP=/a/xampp/php/php.exe bash tests/e2e.sh (Git Bash + XAMPP)
# Tự bật máy chủ PHP tích hợp ở cổng 8799, SAO LƯU data/*.json trước khi chạy và KHÔI PHỤC khi xong.
ROOTD="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP="${PHP:-php}"
B=http://127.0.0.1:8799
S="$(mktemp -d)"
# Git Bash: curl.exe / php.exe không hiểu đường dẫn /tmp/... → đổi sang dạng C:/...
if command -v cygpath >/dev/null 2>&1; then S="$(cygpath -m "$S")"; ROOTD="$(cygpath -m "$ROOTD")"; fi
D="$ROOTD/data"; F="$S/files"
mkdir -p "${S:?}/files" "${S:?}/bak"
cp "${D:?}"/*.json "${S:?}/bak/"; touch "${S:?}/marker"; echo '[]' > "${D:?}/throttle.json"
"$PHP" -S 127.0.0.1:8799 -t "$ROOTD" "$ROOTD/router.php" > "${S:?}/server.log" 2>&1 & SRV=$!
cleanup() {
  kill "$SRV" 2>/dev/null
  cp "${S:?}/bak/"*.json "${D:?}/"
  for f in questions outbox; do [ -f "${S:?}/bak/${f:?}.json" ] || rm -f "${D:?}/${f:?}.json"; done
  find "${D:?}/uploads/tickets" -type f -newer "${S:?}/marker" -delete 2>/dev/null
  rm -rf "${S:?}"
  echo "(đã khôi phục dữ liệu)"
}
trap cleanup EXIT
sleep 1
"$PHP" -r '
$d = $argv[1];
$app1 = "Exif\0\0" . str_repeat("GPS-LAT-10.7769-LON-106.7009 ", 3);
file_put_contents("$d/anh-gps.jpg", "\xFF\xD8\xFF\xE1" . pack("n", strlen($app1) + 2) . $app1 . "\xFF\xDB" . pack("n", 67) . "\0" . str_repeat("\x01", 64) . "\xFF\xDA" . pack("n", 8) . "\x01\x01\x00\x00\x3F\x00" . str_repeat("\x55", 50) . "\xFF\xD9");
file_put_contents("$d/gia-mao.jpg", "day la van ban, khong phai anh");
file_put_contents("$d/lon.pdf", "%PDF-1.4\n" . str_repeat("A", 11 * 1048576));
file_put_contents("$d/x.exe", "MZ...");
file_put_contents("$d/nho.pdf", "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");' "$F"

csrf() { grep -o 'name="_csrf" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }
login() { local j="$S/$1.jar"; : > "$j"; local t=$(curl -s -c "$j" -b "$j" "$B/$2/dang-nhap.php" | csrf);
  curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" -c "$j" -b "$j" --data-urlencode "_csrf=$t" --data-urlencode "email=$3" --data-urlencode "pw=TestHS2026!" "$B/$2/dang-nhap.php"; }
get() { curl -s -c "$S/$1.jar" -b "$S/$1.jar" "$B/$2"; }
code() { curl -s -o /dev/null -w "%{http_code} %{redirect_url}" -c "$S/$1.jar" -b "$S/$1.jar" "$B/$2"; }
pass=0; fail=0
ok() { if eval "$2"; then echo "  ✅ $1"; pass=$((pass+1)); else echo "  ❌ $1"; fail=$((fail+1)); fi; }
# Git Bash trên Windows làm hỏng tham số tiếng Việt khi truyền cho curl.exe → ghi giá trị ra tệp UTF-8 rồi cho curl đọc từ tệp.
_n=0
fix_args() { FIXED=(); local prev=""; for a in "$@"; do
    if [ "$prev" = "--data-urlencode" ] && [[ "$a" == *=* ]]; then _n=$((_n+1)); printf '%s' "${a#*=}" > "$S/v$_n"; FIXED+=("${a%%=*}@$S/v$_n")
    elif [ "$prev" = "-F" ] && [[ "$a" == *=* ]] && [[ "${a#*=}" != @* ]]; then _n=$((_n+1)); printf '%s' "${a#*=}" > "$S/v$_n"; FIXED+=("${a%%=*}=<$S/v$_n")
    else FIXED+=("$a"); fi; prev="$a"; done; }
post() { local j=$1 p=$2; shift 2; local t=$(get $j "$p" | csrf); fix_args "$@"; curl -s -L -c "$S/$j.jar" -b "$S/$j.jar" -F "_csrf=$t" "${FIXED[@]}" "$B/$p"; }
postd() { local j=$1 p=$2; shift 2; local t=$(get $j "$p" | csrf); fix_args "$@"; curl -s -L -c "$S/$j.jar" -b "$S/$j.jar" --data-urlencode "_csrf=$t" "${FIXED[@]}" "$B/$p"; }
tk() { grep -A16 "\"code\": \"$1\"" $D/tickets.json; }
send_ticket() { # $1 jar, $2 type, $3 group, $4 urgent, $5 desc, $6 anonymous
  postd $1 "sv/gui-phieu.php?buoc=1" --data-urlencode "type=$2" >/dev/null
  postd $1 "sv/gui-phieu.php?buoc=2" --data-urlencode "group=$3" >/dev/null
  postd $1 "sv/gui-phieu.php?buoc=3" --data-urlencode "urgent=$4" >/dev/null
  [ "$4" = "1" ] && postd $1 "sv/gui-phieu.php?buoc=3" --data-urlencode urgent=1 --data-urlencode confirm=1 >/dev/null
  post $1 "sv/gui-phieu.php?buoc=4" -F "description=$5" -F occurred_at= -F location= -F action=next >/dev/null
  postd $1 "sv/gui-phieu.php?buoc=5" --data-urlencode "anonymous=$6" | grep -o "KN-2026-00[0-9][0-9]" | head -1
}
REG="--data-urlencode name=Thu --data-urlencode pw=abc12345 --data-urlencode pw2=abc12345 --data-urlencode agree=1"

echo "1. Đăng ký bằng mã + email"
r=$(postd reg sv/dang-ky.php --data-urlencode code=DEMO-0002 --data-urlencode email=demo3@example.com $REG)
ok "Sai cặp mã/email bị chặn" 'echo "$r" | grep -q "không khớp với danh sách"'
r=$(postd reg sv/dang-ky.php --data-urlencode code=DEMO-0002 --data-urlencode email=demo2@example.com --data-urlencode name=Thu --data-urlencode pw=abcdefgh --data-urlencode pw2=abcdefgh --data-urlencode agree=1)
ok "Mật khẩu không có số bị chặn" 'echo "$r" | grep -q "có chữ và số"'
r=$(postd reg sv/dang-ky.php --data-urlencode code=demo-0002 --data-urlencode email=Demo2@Example.com $REG)
ok "Đúng cặp (khác hoa thường) → sang trang xác thực" 'echo "$r" | grep -q "Kiểm tra hộp thư"'
ok "Mã được đánh dấu đã dùng" 'grep -A4 "DEMO-0002" $D/codes.json | grep -q "\"used\": true"'
ok "Có thư xác thực trong hộp thư đi" 'grep -q "Xác thực email" $D/outbox.json'
link=$(echo "$r" | grep -o 'sv/xac-thuc.php?token=[0-9a-f]*' | head -1)
t=$(get reg sv/dang-nhap.php | csrf); r=$(curl -s -c "$S/reg.jar" -b "$S/reg.jar" --data-urlencode "_csrf=$t" --data-urlencode email=demo2@example.com --data-urlencode pw=abc12345 "$B/sv/dang-nhap.php")
ok "Chưa xác thực thì chưa đăng nhập được" 'echo "$r" | grep -q "chưa xác thực"'
curl -s -o /dev/null -c "$S/reg.jar" -b "$S/reg.jar" "$B/$link"
t=$(get reg sv/dang-nhap.php | csrf); r=$(curl -s -o /dev/null -w "%{redirect_url}" -c "$S/reg.jar" -b "$S/reg.jar" --data-urlencode "_csrf=$t" --data-urlencode email=demo2@example.com --data-urlencode pw=abc12345 "$B/sv/dang-nhap.php")
ok "Sau xác thực đăng nhập được" 'echo "$r" | grep -q "sv/hoc.php"'
r=$(postd reg2 sv/dang-ky.php --data-urlencode code=DEMO-0002 --data-urlencode email=demo2@example.com $REG)
ok "Dùng lại mã/email đã dùng bị chặn" 'echo "$r" | grep -q "Email này đã được đăng ký\|đã được dùng"'
for i in 1 2 3 4 5; do postd brute sv/dang-ky.php --data-urlencode code=SAI-000$i --data-urlencode email=demo3@example.com $REG >/dev/null; done
r=$(postd brute sv/dang-ky.php --data-urlencode code=DEMO-0003 --data-urlencode email=demo3@example.com $REG)
ok "Dò mã: sai 5 lần với 1 email → tạm khóa email đó (kể cả khi nhập đúng)" 'echo "$r" | grep -q "quá nhiều lần"'
r=$(postd brute2 sv/dang-ky.php --data-urlencode code=DEMO-0004 --data-urlencode email=demo4@example.com $REG)
ok "Bạn khác cùng IP (NAT trường) vẫn đăng ký được" 'echo "$r" | grep -q "Kiểm tra hộp thư"'

echo "2. Đăng nhập, đặt lại mật khẩu"
t=$(get wrong sv/dang-nhap.php | csrf)
r=$(curl -s -c "$S/wrong.jar" -b "$S/wrong.jar" --data-urlencode "_csrf=$t" --data-urlencode email=E2400249.test@example.com --data-urlencode pw=sai "$B/sv/dang-nhap.php")
ok "Sai mật khẩu báo lỗi rõ" 'echo "$r" | grep -q "Email hoặc mật khẩu không đúng"'
r=$(postd rs sv/quen-mat-khau.php --data-urlencode email=admin@abc-trunghoc.edu.vn)
ok "Quên mật khẩu admin: KHÔNG hiện liên kết trên trang" '! echo "$r" | grep -q "mat-khau-moi.php?token"'
r=$(postd rs2 sv/quen-mat-khau.php --data-urlencode email=E2400249.test@example.com)
ok "Học sinh (bản chạy thử): có liên kết đặt lại" 'echo "$r" | grep -q "mat-khau-moi.php?token"'
r=$(postd rs3 sv/quen-mat-khau.php --data-urlencode email=khongco@example.com)
ok "Email không tồn tại: cùng thông báo, không lộ" 'echo "$r" | grep -q "Nếu email có trong hệ thống" && ! echo "$r" | grep -q "token="'

echo "3. Học tập, hỏi đáp, Vì sao em chọn"
login hs sv E2400249.test@example.com >/dev/null
P='sv/tinh-huong.php?chu-de=thuoc-la-dien-tu&so=1'
postd hs "$P" --data-urlencode choice=1 >/dev/null
ok "Chọn đáp án → lưu tiến độ" 'grep -q "thuoc-la-dien-tu-1" $D/users.json'
r=$(get hs "$P"); ok "Hiện giải thích lựa chọn" 'echo "$r" | grep -q "Giải thích cho lựa chọn B"'
postd hs "$P" --data-urlencode action=why --data-urlencode "why=Vì từ chối là an toàn <b>nhất</b>" >/dev/null
ok "Gửi 'Vì sao em chọn'" 'grep -q "Vì từ chối là an toàn" $D/questions.json'
postd hs "$P" --data-urlencode action=reset >/dev/null; r=$(get hs "$P")
ok "Làm lại được" '! echo "$r" | grep -q "Giải thích cho lựa chọn"'
postd hs "sv/chu-de.php?id=thuoc-la-dien-tu" --data-urlencode action=ask --data-urlencode "question=Thuốc lá điện tử có gây nghiện không ạ?" >/dev/null
login gv qt giaovien@abc-trunghoc.edu.vn >/dev/null
r=$(get gv qt/hoi-dap.php); qid=$(echo "$r" | grep -o 'name="id" value="q[0-9a-f]*"' | head -1 | sed 's/.*value="//;s/"//')
ok "Giáo viên thấy câu hỏi chờ" '[ -n "$qid" ]'
postd gv qt/hoi-dap.php --data-urlencode action=answer --data-urlencode id=$qid --data-urlencode "answer=Có, nicotine gây nghiện." --data-urlencode published=1 >/dev/null
r=$(get reg "sv/chu-de.php?id=thuoc-la-dien-tu")
ok "Câu trả lời đã công bố hiện cho học sinh khác, không kèm tên" 'echo "$r" | grep -q "nicotine gây nghiện" && echo "$r" | grep -q "Một bạn hỏi"'
ok "Giáo viên không vào được phiếu" '[ "$(code gv qt/phieu.php)" != "200 " ]'
r=$(get gv "qt/hoi-dap.php?tab=vi-sao"); ok "'Vì sao em chọn' hiện cho GV và được escape" 'echo "$r" | grep -q "&lt;b&gt;nhất"'

echo "4. Gửi phiếu ẩn danh + tệp đính kèm"
postd hs "sv/gui-phieu.php?buoc=1" --data-urlencode "type=Phản ánh" >/dev/null
postd hs "sv/gui-phieu.php?buoc=2" --data-urlencode "group=Bạo lực học đường, bắt nạt" >/dev/null
postd hs "sv/gui-phieu.php?buoc=3" --data-urlencode urgent=0 >/dev/null
DESC="Nhóm bạn <script>alert(1)</script> chặn đường em ở cổng sau"
r=$(post hs "sv/gui-phieu.php?buoc=4" -F "description=$DESC" -F occurred_at= -F location=Cong -F action=upload -F "files[]=@$F/gia-mao.jpg")
ok "Ảnh giả (văn bản đổi đuôi .jpg) bị chặn theo MIME thực" 'echo "$r" | grep -q "không đúng định dạng JPG"'
r=$(post hs "sv/gui-phieu.php?buoc=4" -F "description=$DESC" -F occurred_at= -F location=Cong -F action=upload -F "files[]=@$F/x.exe")
ok "Tệp .exe bị chặn" 'echo "$r" | grep -q "không được phép"'
r=$(post hs "sv/gui-phieu.php?buoc=4" -F "description=$DESC" -F occurred_at= -F location=Cong -F action=upload -F "files[]=@$F/lon.pdf")
ok "Tệp > 10 MB bị chặn" 'echo "$r" | grep -q "vượt quá\|không thành công"'
r=$(post hs "sv/gui-phieu.php?buoc=4" -F "description=$DESC" -F occurred_at= -F location=Cong -F action=upload -F "files[]=@$F/anh-gps.jpg" -F "files[]=@$F/nho.pdf" -F "files[]=@$F/nho.pdf" -F "files[]=@$F/nho.pdf" -F "files[]=@$F/nho.pdf" -F "files[]=@$F/nho.pdf")
ok "Quá 5 tệp/phiếu bị chặn" 'echo "$r" | grep -q "tối đa 5 tệp"'
ok "Kiểm tra phía trình duyệt có trên form (data-max-mb, data-exts)" 'echo "$r" | grep -q "data-max-mb=\"10\"" && echo "$r" | grep -q "data-exts="'
post hs "sv/gui-phieu.php?buoc=4" -F "description=$DESC" -F occurred_at= -F location=Cong -F action=next >/dev/null
r=$(postd hs "sv/gui-phieu.php?buoc=5" --data-urlencode anonymous=1)
code1=$(echo "$r" | grep -o "KN-2026-00[0-9][0-9]" | head -1)
ok "Nhận mã phiếu dạng KN-2026-xxxx ($code1)" '[ -n "$code1" ]'
ok "Xác nhận ghi rõ 'tự động'" 'echo "$r" | grep -q "Xác nhận tự động"'
ok "tickets.json không chứa id/tên/email người gửi" '! grep -q "owner_id\|u_hs1\|E2400249" $D/tickets.json'
ok "Liên kết người gửi lưu riêng ở ticket_owners.json" 'grep -q "\"$code1\": \"u_hs1\"" $D/ticket_owners.json'
clean=$(ls -t $D/uploads/tickets/*.clean.jpg 2>/dev/null | head -1); orig=${clean/.clean/}
ok "Ảnh có bản đã xóa GPS (bản gốc vẫn còn GPS)" '[ -n "$clean" ] && ! grep -q "GPS-LAT" "$clean" && grep -q "GPS-LAT" "$orig"'
ok "Báo cán bộ phụ trách qua email, thư không chứa mô tả" 'grep -q "\"ticket\": \"$code1\"" $D/outbox.json && ! grep -q "chặn đường em" $D/outbox.json'

echo "5. Cán bộ xử lý phiếu ẩn danh"
login bao qt baotq@abc-trunghoc.edu.vn >/dev/null
r=$(get bao "qt/xu-ly-phieu.php?id=$code1")
ok "Bảo (đầu mối nhóm bắt nạt) mở được phiếu" 'echo "$r" | grep -q "Phiếu #$code1"'
ok "Không lộ tên/email/id người gửi trong HTML" '! echo "$r" | grep -qi "E2400249\|u_hs1"'
ok "Nội dung <script> được escape (XSS)" 'echo "$r" | grep -q "&lt;script&gt;" && ! echo "$r" | grep -q "<script>alert"'
ok "Bảo xem được bản đã xóa GPS" '[ "$(curl -s -b "$S/bao.jar" "$B/tep.php?k=att&t=$code1&i=0" | grep -c GPS-LAT)" = "0" ]'
ok "Bảo KHÔNG tải được bản gốc" '[ "$(curl -s -o /dev/null -w "%{http_code}" -b "$S/bao.jar" "$B/tep.php?k=att&t=$code1&i=0&goc=1")" = "403" ]'
ok "Đường dẫn images/../tickets bị chặn" '[ "$(curl -s -o /dev/null -w "%{http_code}" -b "$S/bao.jar" "$B/tep.php?k=img&f=images/../tickets/$(basename "$orig")")" = "404" ]'
postd bao "qt/xu-ly-phieu.php?id=$code1" --data-urlencode action=reply --data-urlencode "reply=Thầy đã nhận, em giữ an toàn nhé." >/dev/null
ok "Học sinh nhận email 'Phiếu có cập nhật', không kèm nội dung" 'grep -q "Phiếu #$code1 có cập nhật" $D/outbox.json && ! grep -q "giữ an toàn nhé" $D/outbox.json'
postd bao "qt/xu-ly-phieu.php?id=$code1" --data-urlencode action=reveal --data-urlencode "basis=Cần thiết để xác minh sự việc" --data-urlencode reason=thu --data-urlencode confirm=1 >/dev/null
ok "Bảo KHÔNG mở được danh tính" '! tk $code1 | grep -q "u_bao\"\]"'
postd bao "qt/xu-ly-phieu.php?id=$code1" --data-urlencode action=save --data-urlencode "group=Bạo lực học đường, bắt nạt" --data-urlencode "priority=Phản hồi sau" --data-urlencode "status=Đang xem xét" --data-urlencode assignee=u_hs1 --data-urlencode deadline= >/dev/null
ok "Không phân công được cho tài khoản học sinh" '! tk $code1 | grep -q "\"assignee\": \"u_hs1\""'

echo "6. Mở danh tính (Võ Thanh Tùng)"
login tung qt tungvt@abc-trunghoc.edu.vn >/dev/null
r=$(postd tung "qt/xu-ly-phieu.php?id=$code1" --data-urlencode action=reveal --data-urlencode basis= --data-urlencode reason=)
ok "Thiếu căn cứ/lý do bị chặn" 'echo "$r" | grep -q "Nhập lý do cụ thể"'
r=$(postd tung "qt/xu-ly-phieu.php?id=$code1" --data-urlencode action=reveal --data-urlencode "basis=Cần thiết để xác minh sự việc" --data-urlencode "reason=Cần gặp trực tiếp để xác minh" --data-urlencode confirm=1)
ok "Tùng mở được và thấy email người gửi" 'echo "$r" | grep -q "E2400249.test@example.com"'
ok "Nhật ký ghi ai / phiếu / lý do" 'grep -B4 -A3 "Cần gặp trực tiếp để xác minh" $D/audit.json | grep -q "Võ Thanh Tùng"'
oc=$(curl -s -o /dev/null -w "%{http_code}" -b "$S/tung.jar" "$B/tep.php?k=att&t=$code1&i=0&goc=1")
ok "Tùng tải bản gốc được và có nhật ký" '[ "$oc" = "200" ] && grep -q "Tải tệp gốc" $D/audit.json'
r=$(get bao "qt/xu-ly-phieu.php?id=$code1"); ok "Bảo vẫn không thấy danh tính sau khi Tùng mở" '! echo "$r" | grep -q "E2400249"'
login ad qt admin@abc-trunghoc.edu.vn >/dev/null
r=$(postd ad qt/tai-khoan.php --data-urlencode action=staff_edit --data-urlencode id=u_bao --data-urlencode "role=Xử lý phiếu" --data-urlencode reveal=1)
ok "Không cấp được quyền mở danh tính cho người thứ 3" '! grep -A12 "\"id\": \"u_bao\"" $D/users.json | grep -q "\"can_reveal\": true"'

echo "7. Định tuyến, hàng chờ, SLA"
code2=$(send_ticket hs "Phản ánh" "Chưa rõ / Khác" 0 "Em không biết báo ai" 0)
ok "Phiếu chưa rõ nhóm vào hàng chờ phân loại ($code2)" 'tk $code2 | grep -q "\"assignee\": null"'
postd hs "sv/gui-phieu.php?buoc=1" --data-urlencode "type=Phản ánh" >/dev/null; postd hs "sv/gui-phieu.php?buoc=2" --data-urlencode "group=Phản ánh liên quan trực tiếp đến người đang xử lý" >/dev/null
r=$(postd hs "sv/gui-phieu.php?buoc=3" --data-urlencode urgent=1); ok "Chọn 'cần hỗ trợ ngay' hiện ngay số 1900 0000" 'echo "$r" | grep -q "Gọi 1900 0000"'
code3=$(send_ticket hs "Phản ánh" "Phản ánh liên quan trực tiếp đến người đang xử lý" 1 "Liên quan thầy phụ trách" 1)
ok "Phiếu liên quan người xử lý → Võ Thanh Tùng, mức Cần phản hồi ngay ($code3)" 'tk $code3 | grep -q "\"assignee\": \"u_tung\"" && tk $code3 | grep -q "Cần phản hồi ngay"'
ok "Bảo không mở được phiếu liên quan cán bộ" '[ "$(code bao qt/xu-ly-phieu.php?id=$code3)" != "200 " ]'
"$PHP" -r '$f="'$D'/tickets.json"; $a=json_decode(file_get_contents($f),true); foreach($a as &$t) if($t["code"]==="'$code3'") $t["deadline"]=date("c", time()-60); file_put_contents($f, json_encode($a, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));'
"$PHP" $ROOTD/cron.php >/dev/null
ok "Quá 15 phút chưa xác nhận → chuyển dự phòng + nhắc (cron)" 'tk $code3 | grep -q "\"assignee\": \"u_admin\"" && grep -q "Chuyển dự phòng" $D/audit.json && grep -q "quá hạn xác nhận" $D/outbox.json'
r=$(curl -s "$B/?ngoaigio=1"); ok "Ngoài giờ: hiện giờ tiếp nhận và 1900 0000" 'echo "$r" | grep -q "Ngoài giờ trực" && echo "$r" | grep -q "1900 0000"'

echo "8. Học sinh chỉ thấy phiếu của mình"
r=$(get reg sv/phieu.php); ok "Học sinh khác không thấy phiếu của E2400249" '! echo "$r" | grep -q "KN-2026-00"'
ok "Không xem được chi tiết phiếu người khác" '[ "$(code reg sv/chi-tiet-phieu.php?id=$code1)" != "200 " ]'
ok "Không tải được tệp phiếu người khác" '[ "$(curl -s -o /dev/null -w "%{http_code}" -b "$S/reg.jar" "$B/tep.php?k=att&t=$code1&i=0")" = "404" ]'

echo "9. Trang công khai, giao diện, bảo mật chung"
for p in chinh-sach quy-tac an-danh gio-tiep-nhan; do ok "trang.php?p=$p tải được" '[ "$(curl -s -o /dev/null -w "%{http_code}" "$B/trang.php?p=$p")" = "200" ]'; done
ok "Không trang nào hứa 'bảo mật/an toàn tuyệt đối'" '! curl -s "$B/" "$B/trang.php?p=chinh-sach" "$B/trang.php?p=quy-tac" | grep -qi "bảo mật tuyệt đối\|đảm bảo an toàn tuyệt đối"'
r=$(curl -s "$B/"); ok "Header: Trang chủ, Tài liệu, tìm kiếm, nút đỏ Em cần hỗ trợ" 'echo "$r" | grep -q "Trang chủ" && echo "$r" | grep -q ">Tài liệu<" && echo "$r" | grep -q "lp-search" && echo "$r" | grep -q "lp-nav__sos"'
ok "3 lối vào chính" 'echo "$r" | grep -q "Khám phá tình huống" && echo "$r" | grep -q "Gửi góp ý hoặc phản ánh" && echo "$r" | grep -q "lp-cta--danger\">Em cần hỗ trợ"'
ok "Màu #1565C0 và #C62828" 'grep -qi "c-primary: #1565c0" $ROOTD/assets/css/app.css && grep -qi "c-danger: #c62828" $ROOTD/assets/css/app.css'
ok "POST không có CSRF bị chặn (419)" '[ "$(curl -s -o /dev/null -w "%{http_code}" -b "$S/hs.jar" -d "type=x" "$B/sv/gui-phieu.php?buoc=1")" = "419" ]'
ok "data/ và includes/ bị chặn truy cập trực tiếp" '[ "$(curl -s -o /dev/null -w "%{http_code}" "$B/data/ticket_owners.json")" = "403" ] && [ "$(curl -s -o /dev/null -w "%{http_code}" "$B/includes/config.php")" = "403" ]'
ok "Không có huy hiệu/điểm ở luồng gửi phiếu" '! grep -qi "huy hiệu\|điểm thưởng" $ROOTD/sv/gui-phieu.php $ROOTD/sv/gui-thanh-cong.php'
r=$(get hs "sv/tai-lieu.php?q=OTP"); ok "Tìm học liệu ra tình huống" 'echo "$r" | grep -q "Nhận đường link lạ"'
for i in 1 2 3 4 5; do t=$(get bf qt/dang-nhap.php | csrf); curl -s -o /dev/null -c "$S/bf.jar" -b "$S/bf.jar" --data-urlencode "_csrf=$t" --data-urlencode email=namph@abc-trunghoc.edu.vn --data-urlencode pw=sai$i "$B/qt/dang-nhap.php"; done
t=$(get bf2 qt/dang-nhap.php | csrf); r=$(curl -s -c "$S/bf2.jar" -b "$S/bf2.jar" --data-urlencode "_csrf=$t" --data-urlencode email=namph@abc-trunghoc.edu.vn --data-urlencode "pw=TestHS2026!" "$B/qt/dang-nhap.php")
ok "Dò mật khẩu: sai 5 lần → khóa 15 phút" 'echo "$r" | grep -q "quá nhiều lần"'
errs=$(grep -iE "Fatal|Warning:|Deprecated:|Uncaught" "$S/server.log" | head -3)
ok "Không có lỗi/cảnh báo PHP trong log máy chủ" '[ -z "$errs" ]'
[ -n "$errs" ] && echo "$errs"

echo; echo "KẾT QUẢ: $pass đạt, $fail lỗi"
[ "$fail" = "0" ]
