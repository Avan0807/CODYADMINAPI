<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AffiliateLink; // Import model

class ApiAffiliateController extends Controller
{
    public function generateLink($product_id) {
        $doctorID = Auth::id(); // Lấy ID bác sĩ

        // Kiểm tra xem đã có link Affiliate cho bác sĩ và sản phẩm này chưa
        $existingLink = AffiliateLink::where('doctor_id', $doctorID)
                                    ->where('product_id', $product_id)
                                    ->first();

        if ($existingLink) {
            return response()->json([
                'message' => 'Link Affiliate đã tồn tại!',
                'affiliate_link' => url("/api/product-detail/{$product_id}?ref={$doctorID}"),
                'data' => $existingLink
            ], 200);
        }

        // Tạo mới link affiliate
        $affiliate = new AffiliateLink();
        $affiliate->doctor_id = $doctorID;
        $affiliate->product_id = $product_id;
        $affiliate->affiliate_code = strtoupper('AFF-' . uniqid()); // Tạo mã affiliate ngẫu nhiên
        $affiliate->created_at = now();
        $affiliate->save();

        return response()->json([
            'message' => 'Link Affiliate được tạo và lưu thành công!',
            'affiliate_link' => url("/api/product-detail/{$product_id}?ref={$doctorID}"),
            'data' => $affiliate
        ], 201);
    }

    public function trackClick(Request $request, $affiliate_code) {
        // Tìm thông tin affiliate link
        $affiliate = \DB::table('affiliate_links')->where('affiliate_code', $affiliate_code)->first();

        if (!$affiliate) {
            return response()->json(['error' => 'Affiliate link không tồn tại.'], 404);
        }

        // Lưu thông tin lượt click vào bảng affiliate_clicks
        \DB::table('affiliate_clicks')->insert([
            'doctor_id' => $affiliate->doctor_id,
            'product_id' => $affiliate->product_id,
            'affiliate_code' => $affiliate_code,
            'ip_address' => $request->ip(),  // Lấy địa chỉ IP của user
            'user_agent' => $request->header('User-Agent'), // Lấy thông tin trình duyệt
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Cộng điểm cho bác sĩ (ví dụ: mỗi click được +1 điểm)
        \DB::table('doctors')->where('id', $affiliate->doctor_id)->increment('points', 1);

        return response()->json([
            'message' => 'Click được ghi nhận thành công!',
            'doctor_id' => $affiliate->doctor_id,
            'product_id' => $affiliate->product_id,
            'points_added' => 1
        ], 200);
    }


}
