<?php
declare(strict_types=1);

/**
 * Dữ liệu khởi tạo cho bản CHẠY THỬ "KẾT NỐI HỌC SINH" – toàn bộ là dữ liệu GIẢ LẬP.
 * Chỉ dùng khi tệp data/<tên>.json chưa tồn tại.
 * Muốn làm lại từ đầu: xóa các tệp .json trong data/.
 */
function seed_data(string $name): array
{
    return match ($name) {
        'config'  => seed_config(),
        'topics'  => seed_topics(),
        'users'   => seed_users(),
        'tickets' => seed_tickets(),
        'audit'   => seed_audit(),
        'docs'    => [],
        'codes'   => seed_codes(),
        default   => [],
    };
}

/* Nhóm vấn đề (cố định theo kịch bản phân công) */
const G_BULLY   = 'Bạo lực học đường, bắt nạt';
const G_VAPE    = 'Sử dụng thuốc lá điện tử';
const G_SOCIAL  = 'Các vấn đề trên mạng xã hội';
const G_HARASS  = 'Quấy rối, xâm hại';
const G_FEEDBACK = 'Góp ý về hoạt động và môi trường học đường';
const G_STAFF   = 'Phản ánh liên quan trực tiếp đến người đang xử lý';

/* Mã cán bộ */
const U_ADMIN = 'u_admin';   // Nguyễn Minh Anh – quản trị chính
const U_BAO   = 'u_bao';     // Trần Quốc Bảo – công tác học sinh
const U_HA    = 'u_ha';      // Lê Thu Hà – tư vấn, hỗ trợ học sinh
const U_NAM   = 'u_nam';     // Phạm Hoàng Nam – hỗ trợ dự phòng
const U_TUNG  = 'u_tung';    // Võ Thanh Tùng – tiếp nhận độc lập
const U_HS    = 'u_hs1';     // Học sinh chạy thử E2400249

function seed_config(): array
{
    return [
        'duty_start'         => '08:00',
        'duty_end'           => '17:00',
        'duty_days'          => [1, 2, 3, 4, 5],
        'urgent_ack_minutes' => 15,
        'first_reply_days'   => 2,      // ngày làm việc
        'fallback'           => true,
        'hotline'            => '1900 0000',
        'file_types'         => ['pdf', 'jpg', 'jpeg', 'png', 'mp4'],
        'max_file_mb'        => 10,
        'max_files'          => 5,      // mỗi phản ánh
        'groups'             => [G_BULLY, G_VAPE, G_SOCIAL, G_HARASS, G_FEEDBACK, G_STAFF],
        // primary = người phụ trách, backup = người hỗ trợ khi vắng mặt/quá hạn,
        // independent = người tiếp nhận độc lập (khi phản ánh liên quan đến chính người phụ trách)
        'routing'            => [
            G_BULLY       => ['primary' => U_BAO,  'backup' => U_NAM,   'independent' => U_TUNG],
            G_VAPE        => ['primary' => U_BAO,  'backup' => U_NAM,   'independent' => U_TUNG],
            G_SOCIAL      => ['primary' => U_HA,   'backup' => U_NAM,   'independent' => U_TUNG],
            G_HARASS      => ['primary' => U_HA,   'backup' => U_TUNG,  'independent' => U_TUNG],
            G_FEEDBACK    => ['primary' => U_BAO,  'backup' => U_NAM,   'independent' => U_TUNG],
            G_STAFF       => ['primary' => U_TUNG, 'backup' => U_ADMIN, 'independent' => U_TUNG],
            ISSUE_UNKNOWN => ['primary' => '',     'backup' => U_NAM,   'independent' => U_TUNG],
        ],
        'templates' => [
            ['id' => 't1', 'name' => 'Đã tiếp nhận – đang xác minh', 'body' => 'Thầy/cô đã tiếp nhận phản ánh của em và đang xác minh thông tin. Thầy/cô sẽ phản hồi em sớm nhất có thể.'],
            ['id' => 't2', 'name' => 'Đề nghị bổ sung thông tin', 'body' => 'Để hỗ trợ em tốt hơn, em vui lòng bổ sung thêm thời gian, địa điểm và những người liên quan (nếu có). Danh tính của em vẫn được bảo mật.'],
            ['id' => 't3', 'name' => 'Hướng dẫn liên hệ hỗ trợ trực tiếp', 'body' => 'Em có thể gặp trực tiếp cán bộ tư vấn tại phòng tư vấn học đường trong giờ hành chính (08:00–17:00, thứ Hai đến thứ Sáu). Nếu gặp nguy hiểm, em hãy gọi ngay số hỗ trợ 1900 0000.'],
        ],
    ];
}

/**
 * Một tình huống trắc nghiệm.
 * $choices: [[nội dung, giải thích], …]; $best: vị trí đáp án phù hợp nhất (0 = A).
 */
function seed_sit(string $topicId, string $title, string $question, array $choices, int $best): array
{
    return [
        'id'          => $topicId . '-1',
        'title'       => $title,
        'question'    => $question,
        'image'       => '',
        'video'       => '',
        'choices'     => array_map(fn ($c) => ['text' => $c[0], 'explanation' => $c[1]], $choices),
        'best'        => $best,
        'status'      => 'Đã công bố',
        'order'       => 1,
        'allow_retry' => true,
        'updated_at'  => date('c'),
    ];
}

function seed_topics(): array
{
    return [
        [
            'id' => 'bao-luc-hoc-duong', 'name' => 'Bạo lực học đường', 'icon' => 'shield', 'order' => 1,
            'description' => 'Nhận biết hành vi bắt nạt, cách ứng xử và hướng tìm kiếm sự hỗ trợ.',
            'content' => "Nhận biết: Bắt nạt là hành vi cố ý, thường lặp lại, gây tổn thương về thể chất hoặc tinh thần cho người khác – như đánh, đe dọa, chế giễu, đặt biệt danh xấu, cô lập, xúc phạm hoặc bêu riếu trên mạng xã hội.\n\nCách ứng xử: Giữ bình tĩnh, không đáp trả bằng bạo lực; rời khỏi nơi không an toàn; không hùa theo hay chia sẻ nội dung bắt nạt; lưu lại bằng chứng (chụp màn hình, ghi thời gian, địa điểm).\n\nTìm kiếm hỗ trợ: Báo giáo viên chủ nhiệm, phụ trách công tác học sinh hoặc cha mẹ; gửi phản ánh trên website (có thể ẩn danh); gọi Tổng đài quốc gia bảo vệ trẻ em 111 (miễn phí).",
            'situations' => [seed_sit('bao-luc-hoc-duong',
                'Bị trêu chọc, cô lập và xúc phạm trên mạng xã hội',
                'Một bạn cùng lớp thường xuyên bị nhóm bạn trêu chọc, cô lập và đăng bài xúc phạm trên mạng xã hội. Khi chứng kiến bạn học bị bắt nạt như vậy, em nên làm gì?',
                [
                    ['Làm ngơ vì đó không phải chuyện của mình', 'Chưa phù hợp. Sự im lặng của người chứng kiến khiến hành vi bắt nạt dễ tiếp diễn và người bị bắt nạt càng cảm thấy cô đơn, bất lực.'],
                    ['Không hùa theo, động viên bạn, lưu lại bằng chứng và báo thầy cô hoặc gửi phản ánh', 'Phù hợp nhất. Không tham gia giúp bắt nạt không lan rộng; lời động viên giúp bạn bớt cô đơn; bằng chứng (ảnh chụp màn hình, thời gian) giúp thầy cô xử lý đúng. Em có thể gửi phản ánh ẩn danh trên website nếu lo ngại bị trả đũa.'],
                    ['Đăng bài đáp trả, lời qua tiếng lại với nhóm bắt nạt', 'Chưa phù hợp. Đáp trả gay gắt dễ làm mâu thuẫn leo thang, em cũng có thể bị cuốn vào và trở thành mục tiêu tiếp theo.'],
                    ['Chia sẻ bài đăng cho nhiều người cùng biết để “làm rõ trắng đen”', 'Chưa phù hợp. Chia sẻ lại làm nội dung xúc phạm lan rộng hơn, khiến bạn bị tổn thương nhiều hơn. Hãy đưa bằng chứng cho người có trách nhiệm thay vì phát tán.'],
                ], 1)],
        ],
        [
            'id' => 'thuoc-la-dien-tu', 'name' => 'Phòng tránh thuốc lá điện tử', 'icon' => 'vapeOff', 'order' => 2,
            'description' => 'Nhận biết tác hại, quy định liên quan và cách từ chối.',
            'content' => "Tác hại: Thuốc lá điện tử chứa nicotine gây nghiện nhanh, ảnh hưởng đến não bộ đang phát triển, tim mạch và hô hấp. Dung dịch có thể chứa hóa chất độc hại và có nguy cơ bị trộn chất gây nghiện, ma túy.\n\nQuy định: Theo Nghị quyết số 173/2024/QH15 của Quốc hội, từ năm 2025 Việt Nam cấm sản xuất, kinh doanh, nhập khẩu, tàng trữ, vận chuyển và sử dụng thuốc lá điện tử, thuốc lá nung nóng. Nội quy nhà trường cũng nghiêm cấm hút thuốc dưới mọi hình thức.\n\nCách từ chối: Nói “không” rõ ràng; đưa lý do ngắn gọn; đề nghị làm việc khác; rời khỏi nhóm khi bị ép; chia sẻ với thầy cô hoặc cha mẹ nếu bị rủ rê nhiều lần.",
            'situations' => [seed_sit('thuoc-la-dien-tu',
                'Được bạn rủ dùng thuốc lá điện tử trong giờ nghỉ',
                'Trong giờ nghỉ, một nhóm bạn rủ em thử thuốc lá điện tử “có mùi trái cây, không hại đâu”. Em nên xử lý như thế nào khi bị rủ rê sử dụng thuốc lá điện tử?',
                [
                    ['Thử một hơi cho vui để không mất lòng bạn', 'Chưa phù hợp. Nicotine gây nghiện rất nhanh; “chỉ thử một lần” là cách nhiều người bắt đầu nghiện. Sản phẩm không rõ nguồn gốc còn có nguy cơ bị trộn chất gây nghiện.'],
                    ['Từ chối dứt khoát, nêu lý do ngắn gọn, rủ bạn làm việc khác; nếu bị ép nhiều lần thì báo thầy cô', 'Phù hợp nhất. Lời từ chối rõ ràng (“Mình không dùng, nó có hại”) giúp em giữ lập trường mà không cần tranh cãi. Nếu bị ép buộc hoặc thấy bạn bè sử dụng thường xuyên, em có thể báo thầy cô hoặc gửi phản ánh để được hỗ trợ.'],
                    ['Nhận cất giữ hộ để bạn không bị phát hiện', 'Chưa phù hợp. Tàng trữ thuốc lá điện tử vi phạm nội quy nhà trường và quy định pháp luật; em có thể gặp rắc rối dù không trực tiếp sử dụng.'],
                    ['Bỏ đi rồi kể cho cả lớp để chê cười các bạn', 'Chưa phù hợp. Bêu riếu dễ gây mâu thuẫn. Nếu lo cho sức khỏe của bạn, em nên góp ý riêng hoặc báo người có trách nhiệm.'],
                ], 1)],
        ],
        [
            'id' => 'an-toan-mang-xa-hoi', 'name' => 'An toàn trên mạng xã hội', 'icon' => 'messageLines', 'order' => 3,
            'description' => 'Nhận diện dấu hiệu lừa đảo, bảo vệ dữ liệu cá nhân và cách báo cáo sự việc.',
            'content' => "Dấu hiệu lừa đảo: Tin nhắn báo trúng thưởng bất ngờ; thúc giục làm ngay; yêu cầu mật khẩu, mã OTP, số tài khoản ngân hàng; đường link lạ hoặc tên miền viết sai; giả danh người quen, nhà trường, ngân hàng, cơ quan nhà nước.\n\nBảo vệ dữ liệu cá nhân: Không chia sẻ mật khẩu, mã OTP với bất kỳ ai; dùng mật khẩu mạnh và bật xác thực hai lớp; hạn chế công khai số điện thoại, địa chỉ, ảnh giấy tờ.\n\nBáo cáo sự việc: Không bấm link; chặn và báo cáo tài khoản trên nền tảng; báo cha mẹ, thầy cô; nếu đã lỡ cung cấp thông tin, đổi mật khẩu ngay và gọi số tổng đài chính thức của ngân hàng.",
            'situations' => [seed_sit('an-toan-mang-xa-hoi',
                'Nhận đường link lạ đòi thông tin tài khoản ngân hàng',
                'Em nhận được tin nhắn: “Chúc mừng bạn trúng thưởng 5 triệu đồng! Bấm vào link và nhập số tài khoản ngân hàng, mật khẩu, mã OTP để nhận thưởng trong 10 phút.” Khi nhận được đường link đáng ngờ, em nên làm gì?',
                [
                    ['Bấm vào xem thử, thấy lạ thì thoát ra', 'Chưa phù hợp. Chỉ cần bấm vào link độc hại, thiết bị có thể bị cài mã độc hoặc tài khoản bị chiếm quyền.'],
                    ['Nhập thông tin nhanh vì phần thưởng sắp hết hạn', 'Chưa phù hợp. Đây là dấu hiệu lừa đảo điển hình (thúc giục, đòi OTP). Ngân hàng và đơn vị trao thưởng thật không bao giờ yêu cầu mật khẩu hay mã OTP.'],
                    ['Không bấm link, không cung cấp thông tin; chặn, báo cáo tin nhắn và báo cha mẹ, thầy cô', 'Phù hợp nhất. Không tương tác là cách an toàn nhất. Báo cáo giúp nền tảng chặn kẻ gian, báo người lớn giúp em được hỗ trợ kịp thời. Nếu lỡ nhập thông tin, hãy đổi mật khẩu ngay và gọi tổng đài chính thức của ngân hàng.'],
                    ['Chuyển tiếp link cho bạn bè để hỏi thật hay giả', 'Chưa phù hợp. Chuyển tiếp có thể khiến bạn bè bấm nhầm và bị lừa. Hãy hỏi người lớn bằng cách mô tả hoặc chụp màn hình, không gửi link.'],
                ], 2)],
        ],
        [
            'id' => 'phong-chong-tao-hon', 'name' => 'Phòng, chống tảo hôn', 'icon' => 'heart', 'order' => 4,
            'description' => 'Nhận biết tảo hôn, tác động đối với sức khỏe và việc học; quyền tự nguyện kết hôn và cách tìm kiếm sự hỗ trợ.',
            'content' => "Tảo hôn là gì: Là việc lấy vợ, lấy chồng khi một bên hoặc cả hai bên chưa đủ tuổi kết hôn theo quy định của pháp luật.\n\nĐiều kiện về tuổi và quyền tự nguyện: Theo Luật Hôn nhân và gia đình năm 2014, nam từ đủ 20 tuổi, nữ từ đủ 18 tuổi mới được kết hôn; việc kết hôn do hai bên tự nguyện quyết định. Pháp luật cấm tảo hôn và cưỡng ép kết hôn.\n\nTác động: Mang thai, sinh con quá sớm gây nguy hiểm cho sức khỏe người mẹ và em bé; phải bỏ học, mất cơ hội học tập, nghề nghiệp; dễ rơi vào nghèo khó, bạo lực gia đình.\n\nTìm kiếm hỗ trợ: Chia sẻ với thầy cô chủ nhiệm, cán bộ tư vấn học đường hoặc người thân tin cậy; gửi phản ánh trên website; gọi Tổng đài quốc gia bảo vệ trẻ em 111 (miễn phí). Nhà trường và chính quyền địa phương có trách nhiệm can thiệp, vận động gia đình.",
            'situations' => [seed_sit('phong-chong-tao-hon',
                'Bị gia đình thúc ép nghỉ học để lấy chồng',
                'Bạn H. năm nay 16 tuổi, bị gia đình thúc ép nghỉ học để lấy chồng. H. không muốn nhưng sợ trái lời cha mẹ. Khi bản thân hoặc bạn bè bị ép kết hôn khi chưa đủ tuổi, em nên làm gì?',
                [
                    ['Nghe theo gia đình vì đây là chuyện riêng của mỗi nhà', 'Chưa phù hợp. Tảo hôn là hành vi pháp luật nghiêm cấm, không phải “chuyện riêng”. Việc kết hôn sớm ảnh hưởng nghiêm trọng đến sức khỏe, việc học và tương lai.'],
                    ['Chia sẻ với thầy cô, cán bộ tư vấn hoặc người lớn tin cậy; gửi phản ánh hoặc gọi 111 để được hỗ trợ', 'Phù hợp nhất. Nhà trường, chính quyền địa phương và Tổng đài 111 có thể can thiệp, gặp gỡ và giải thích cho gia đình mà vẫn bảo vệ em. Nếu là bạn bè gặp chuyện, em hãy động viên bạn lên tiếng hoặc báo giúp bạn.'],
                    ['Bỏ nhà đi một mình để tránh bị ép', 'Chưa phù hợp. Rời nhà một mình rất nguy hiểm, dễ bị kẻ xấu lợi dụng. Hãy tìm sự giúp đỡ từ người lớn tin cậy và các kênh hỗ trợ chính thức.'],
                    ['Khuyên bạn đồng ý vì sớm muộn gì cũng phải lập gia đình', 'Chưa phù hợp. Kết hôn phải đủ tuổi và hoàn toàn tự nguyện. Em có quyền được học tập và tự quyết định về hôn nhân khi trưởng thành.'],
                ], 1)],
        ],
        [
            'id' => 'phong-chong-quay-roi', 'name' => 'Phòng, chống quấy rối tình dục', 'icon' => 'hand', 'order' => 5,
            'description' => 'Nhận diện hành vi quấy rối trực tiếp và trên mạng; tôn trọng ranh giới; bảo vệ bản thân và tìm kiếm hỗ trợ.',
            'content' => "Nhận diện: Quấy rối tình dục là lời nói, cử chỉ, tin nhắn, hình ảnh hoặc hành vi đụng chạm mang tính tình dục mà người kia không mong muốn – có thể xảy ra trực tiếp hoặc trên mạng (bình phẩm cơ thể, gửi tin nhắn/ảnh nhạy cảm, đòi gửi ảnh riêng tư…).\n\nTôn trọng ranh giới: Cơ thể của mỗi người thuộc về chính người đó. “Không” nghĩa là không. Không ai được phép đụng chạm hay nói về cơ thể em khi em không đồng ý.\n\nBảo vệ bản thân: Rời khỏi tình huống, đến nơi đông người, an toàn; nói “không” dứt khoát; tránh ở một mình với người có hành vi quấy rối; chặn và báo cáo tài khoản trên mạng.\n\nLưu giữ bằng chứng khi an toàn: Chụp màn hình tin nhắn, ghi lại thời gian, địa điểm, người chứng kiến – nhưng không phát tán nội dung nhạy cảm.\n\nTìm kiếm hỗ trợ: Báo người lớn tin cậy, cán bộ tư vấn học đường; gửi phản ánh nhóm “Quấy rối, xâm hại” (có thể ẩn danh); gọi Tổng đài 111; gọi 113 khi đang gặp nguy hiểm.",
            'situations' => [seed_sit('phong-chong-quay-roi',
                'Bị bình phẩm cơ thể, nhắn tin và đụng chạm gây khó chịu',
                'Một người thường xuyên bình phẩm về cơ thể của em, gửi tin nhắn có nội dung tình dục và cố tình đụng chạm khiến em khó chịu, lo sợ. Khi bị quấy rối tình dục hoặc chứng kiến bạn bị quấy rối, em nên làm gì để bảo đảm an toàn và được hỗ trợ?',
                [
                    ['Cố chịu đựng, giữ im lặng vì sợ bị cười chê hoặc bị đổ lỗi', 'Chưa phù hợp. Bị quấy rối không phải lỗi của em. Im lặng khiến hành vi tiếp diễn và có thể nghiêm trọng hơn.'],
                    ['Rời đến nơi an toàn, nói “không” dứt khoát; khi an toàn thì lưu bằng chứng và báo người lớn tin cậy hoặc gửi phản ánh', 'Phù hợp nhất. An toàn của em là ưu tiên số một. Bằng chứng (ảnh chụp tin nhắn, thời gian, địa điểm) giúp nhà trường xử lý. Em có thể gửi phản ánh ẩn danh, gọi 111 hoặc 113 khi gặp nguy hiểm. Nếu chứng kiến bạn bị quấy rối, hãy ở bên bạn, đưa bạn đến nơi an toàn và báo người có trách nhiệm.'],
                    ['Nhắn tin đáp trả lại bằng nội dung tương tự cho “huề”', 'Chưa phù hợp. Đáp trả có thể bị lợi dụng để đe dọa hoặc đổ lỗi cho em và làm tình hình xấu hơn. Hãy chặn, lưu bằng chứng và báo người lớn.'],
                    ['Gửi ảnh, tin nhắn đó cho nhiều bạn xem để cảnh báo mọi người', 'Chưa phù hợp. Phát tán nội dung nhạy cảm gây tổn thương thêm và có thể vi phạm pháp luật. Chỉ đưa bằng chứng cho thầy cô, cán bộ tư vấn hoặc cơ quan chức năng.'],
                ], 1)],
        ],
    ];
}

function seed_users(): array
{
    $pw = password_hash(DEMO_PASSWORD, PASSWORD_DEFAULT);
    $staff = fn ($id, $name, $email, $role, $reveal) => [
        'id' => $id, 'role' => 'staff', 'name' => $name, 'email' => $email, 'password' => $pw,
        'staff_role' => $role, 'can_reveal' => $reveal, 'locked' => false, 'verified' => true,
        'created_at' => date('c', strtotime('-30 days')),
    ];
    return [
        // Chỉ quản trị chính và người tiếp nhận độc lập được xem thông tin định danh.
        $staff(U_ADMIN, 'Admin', 'admin@abc-trung học.edu.vn', 'Quản trị viên', true),
        $staff(U_BAO, 'Trần Quốc Bảo', 'baotq@abc-trung học.edu.vn', 'Xử lý phiếu', false),
        $staff(U_HA, 'Lê Thu Hà', 'hatl@abc-trung học.edu.vn', 'Xử lý phiếu', false),
        $staff(U_NAM, 'Phạm Hoàng Nam', 'namph@abc-trung học.edu.vn', 'Xử lý phiếu', false),
        $staff(U_TUNG, 'Võ Thanh Tùng', 'tungvt@abc-trung học.edu.vn', 'Xử lý phiếu', true),
        [
            'id' => U_HS, 'role' => 'student', 'name' => 'E2400249', 'email' => 'E2400249.test@example.com',
            'password' => $pw, 'locked' => false, 'verified' => true,
            'progress' => ['bao-luc-hoc-duong' => ['bao-luc-hoc-duong-1']],
            'created_at' => date('c', strtotime('-7 days')),
        ],
    ];
}

/** Vài cặp mã + email chạy thử (mỗi cặp đăng ký được 1 tài khoản). Dữ liệu thật do quản trị viên nạp. */
function seed_codes(): array
{
    $now = date('c');
    return array_map(fn ($i) => [
        'code' => 'DEMO-000' . $i, 'email' => 'demo' . $i . '@example.com',
        'used' => false, 'created_at' => $now, 'used_at' => null,
    ], range(1, 5));
}

function seed_tickets(): array
{
    $ago = fn (string $rel) => date('c', strtotime($rel));
    $ack = fn (string $t, bool $urgent = false) => [
        'from' => 'system', 'author_id' => null, 'time' => $t,
        'text' => $urgent
            ? 'Hệ thống đã tiếp nhận phản ánh khẩn của em và báo cho cán bộ trực. Đây chưa phải phản hồi của cán bộ.'
            : 'Hệ thống đã tiếp nhận phản ánh. Đây chưa phải phản hồi của cán bộ.',
    ];
    $base = fn (array $o) => $o + [
        'owner_id' => U_HS, 'urgent' => false, 'anonymous' => true, 'status' => 'Đã tiếp nhận', 'priority' => 'Thường',
        'occurred_at' => '', 'location' => '', 'attachments' => [], 'history' => [], 'has_update' => false, 'revealed_to' => [],
    ];

    return [
        $base([
            'code' => 'KN-2026-0001', 'type' => 'Đề nghị hỗ trợ', 'group' => G_BULLY, 'status' => 'Cần bổ sung', 'priority' => 'Cao',
            'created_at' => $ago('-1 day'), 'deadline' => $ago('+1 day'), 'assignee' => U_BAO, 'has_update' => true,
            'description' => 'Bạn em trong lớp bị một nhóm bạn đặt biệt danh, chế giễu và lập nhóm chat riêng để nói xấu. Bạn ấy rất buồn và không muốn đi học.',
            'location' => 'Lớp học và nhóm chat của lớp', 'occurred_at' => $ago('-3 days'),
            'messages' => [
                $ack($ago('-1 day')),
                ['from' => 'staff', 'author_id' => U_BAO, 'time' => $ago('-20 hours'), 'text' => 'Cảm ơn em đã lên tiếng. Em có thể cho thầy biết thêm sự việc diễn ra từ khi nào và em có lưu lại ảnh chụp tin nhắn không? Danh tính của em vẫn được bảo mật.'],
            ],
            'history' => [
                ['time' => $ago('-20 hours'), 'text' => 'Trần Quốc Bảo đổi trạng thái → Cần bổ sung'],
                ['time' => $ago('-1 day'), 'text' => 'Hệ thống chuyển đến đầu mối Trần Quốc Bảo'],
                ['time' => $ago('-1 day'), 'text' => 'Phiếu được tạo (ẩn danh)'],
            ],
        ]),
        $base([
            'code' => 'KN-2026-0002', 'type' => 'Phản ánh', 'group' => G_VAPE, 'status' => 'Đang xem xét',
            'created_at' => $ago('-3 days'), 'deadline' => $ago('-1 day'), 'assignee' => U_BAO,
            'description' => 'Có một nhóm học sinh thường hút thuốc lá điện tử ở khu nhà vệ sinh tầng 2 trong giờ ra chơi.',
            'location' => 'Nhà vệ sinh tầng 2, dãy B',
            'messages' => [$ack($ago('-3 days'))],
            'history' => [['time' => $ago('-3 days'), 'text' => 'Hệ thống chuyển đến đầu mối Trần Quốc Bảo']],
        ]),
        $base([
            'code' => 'KN-2026-0003', 'type' => 'Góp ý', 'group' => G_FEEDBACK, 'anonymous' => false, 'status' => 'Đã có phản hồi',
            'created_at' => $ago('-6 days'), 'deadline' => null, 'assignee' => U_BAO,
            'description' => 'Em mong nhà trường bố trí thêm ghế đá và cây xanh ở sân sau để học sinh có chỗ ngồi nghỉ.',
            'messages' => [
                $ack($ago('-6 days')),
                ['from' => 'staff', 'author_id' => U_BAO, 'time' => $ago('-5 days'), 'text' => 'Cảm ơn góp ý của em. Nhà trường sẽ đưa nội dung này vào kế hoạch cải tạo sân trường học kỳ tới.'],
            ],
        ]),
        $base([
            'code' => 'KN-2026-0004', 'type' => 'Phản ánh', 'group' => ISSUE_UNKNOWN,
            'created_at' => $ago('-2 hours'), 'deadline' => $ago('+2 days'), 'assignee' => null,
            'description' => 'Em bị mất đồ trong ngăn bàn hai lần trong tuần này, không biết nên báo ai.',
            'messages' => [$ack($ago('-2 hours'))],
            'history' => [['time' => $ago('-2 hours'), 'text' => 'Phiếu vào hàng chờ phân loại']],
        ]),
    ];
}

function seed_audit(): array
{
    $a = fn ($rel, $actor, $actorId, $action, $ticket, $detail) => [
        'id' => new_id('l'), 'time' => date('c', strtotime($rel)), 'actor' => $actor, 'actor_id' => $actorId,
        'action' => $action, 'ticket' => $ticket, 'detail' => $detail,
    ];
    return [
        $a('-20 hours', 'Trần Quốc Bảo', U_BAO, 'Phản hồi', 'KN-2026-0001', 'Gửi tin nhắn trong hộp thư'),
        $a('-1 day', 'Hệ thống', null, 'Phân công', 'KN-2026-0001', 'Tự chuyển đến đầu mối Trần Quốc Bảo'),
    ];
}
