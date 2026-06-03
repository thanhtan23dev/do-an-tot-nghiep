<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    public static function analyzeProgress($orderData)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $promptTemplate = 'Bạn là chuyên gia quản trị sản xuất tại một xưởng gia công đá mỹ nghệ. Nhiệm vụ của bạn là phân tích dữ liệu các đơn hàng đang thực hiện và chẩn đoán nguy cơ chậm tiến độ.
        
Tuyệt đối KHÔNG chỉ dựa vào phép toán đếm số lượng công việc. Hãy đọc kỹ mảng "cac_viec_chua_xong", kết hợp giữa "ten_viec" và đặc biệt là phần "mo_ta" (mô tả chi tiết) để ĐÁNH GIÁ MỨC ĐỘ PHỨC TẠP của từng công việc. 

Ví dụ suy luận: 
- Nếu "mo_ta" chứa các yêu cầu phức tạp (chạm khắc 3D, làm thủ công nguyên khối...) mà tỷ lệ hoàn thành (so_luong_da_xong / so_luong_can_lam) còn rất thấp, cộng thêm hạn chót (ngay_con_lai) chỉ còn ít ngày -> Nguy cơ Cao.
- Nếu khối lượng công việc lớn (ví dụ: cần làm 100 cái) nhưng mới chỉ xong rất ít (ví dụ: 5 cái), dù công việc đơn giản nhưng ngày còn lại quá ngắn -> Nguy cơ Cao.
- Nếu "so_luong_da_xong" đã gần đạt "so_luong_can_lam", hoặc phần mô tả chỉ là gia công máy móc đơn giản, thời gian còn dư dả -> An toàn.
- Chú ý "trang_thai_hien_tai": Nếu công việc đang ở trạng thái "rejected" (bị bắt sửa lại) thì tiến độ sẽ bị kéo dài thêm rất nhiều.


Dữ liệu đầu vào:
' . json_encode($orderData, JSON_UNESCAPED_UNICODE) . '

Yêu cầu BẮT BUỘC: 
Chỉ trả về kết quả dưới định dạng JSON thuần túy (Mảng các object), KHÔNG CÓ thẻ markdown (như ```json), KHÔNG CÓ text giải thích. Cấu trúc JSON chuẩn:
[
  {
    "order_id": 123, 
    "muc_do_canh_bao": "Cao" (chỉ chọn: Cao/Trung bình/An toàn), 
    "ly_do_suy_luan": "Viết ngắn gọn 1-2 câu giải thích chuyên môn tại sao bạn đánh giá như vậy dựa trên tên và MÔ TẢ của các công việc chưa xong"
  }
]';

        try {
            $response = Http::timeout(120)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $promptTemplate]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
                return json_decode($aiText, true); 
            }

            Log::error('Gemini API Error: ' . $response->body());
            return [];

        } catch (\Exception $e) {
            Log::error('Lỗi gọi Gemini: ' . $e->getMessage());
            return [];
        }
    }
}