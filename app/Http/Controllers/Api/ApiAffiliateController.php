<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AffiliateLink; // Import model

class ApiAffiliateController extends Controller
{
    public function generateLink($product_slug) {
        $doctorID = Auth::id();
    
        // Tìm sản phẩm theo slug
        $product = \App\Models\Product::where('slug', $product_slug)->firstOrFail();
    
        // Kiểm tra xem link đã tồn tại chưa
        $existingLink = AffiliateLink::where([
            ['doctor_id', $doctorID],
            ['product_id', $product->id]
        ])->first();
    
        if ($existingLink) {
            return response()->json([
                'message' => 'Link Affiliate đã tồn tại!',
                'affiliate_link' => url("http://toikhoe.vn/product-detail/{$product->slug}?ref={$existingLink->hash_ref}"),
                'data' => $existingLink
            ], 200);
        }
    
        // Tạo mới link affiliate
        $affiliate = AffiliateLink::createAffiliateLink($doctorID, $product->id);
    
        return response()->json([
            'message' => 'Link Affiliate được tạo thành công!',
            'affiliate_link' => url("http://toikhoe.vn/product-detail/{$product->slug}?ref={$affiliate->hash_ref}"),
            'data' => $affiliate
        ], 201);
    }
    

    public function trackClick(Request $request, $affiliate_code) {
        // Tìm thông tin affiliate link
        $affiliate = \DB::table('affiliate_links')->where('affiliate_code', $affiliate_code)->first();

        if (!$affiliate) {
            return response()->json(['error' => 'Affiliate link không tồn tại.'], 404);
        }

        $ip_address = $request->ip();
        $user_agent = $request->header('User-Agent');
        $doctor_id = $affiliate->doctor_id;
        $product_id = $affiliate->product_id;

        // ✅ Lưu thông tin click vào bảng `affiliate_clicks` (bất kể có cộng điểm hay không)
        \DB::table('affiliate_clicks')->insert([
            'doctor_id' => $doctor_id,
            'product_id' => $product_id,
            'affiliate_code' => $affiliate_code,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 🛑 Kiểm tra xem IP/User-Agent đã click trong 10 phút gần đây chưa (chống spam điểm)
        $recentClick = \DB::table('affiliate_clicks')
                        ->where('doctor_id', $doctor_id)
                        ->where('product_id', $product_id)
                        ->where(function ($query) use ($ip_address, $user_agent) {
                            $query->where('ip_address', $ip_address)
                                  ->orWhere('user_agent', $user_agent);
                        })
                        ->where('created_at', '>', now()->subMinutes(10)) // Chỉ tính điểm 1 lần mỗi 10 phút
                        ->exists();

        if (!$recentClick) {
            // ✅ Chưa có click gần đây => Cộng điểm cho bác sĩ
            \DB::table('doctors')->where('id', $doctor_id)->increment('points', 1);
            $pointsAdded = 1;
        } else {
            // 🛑 Nếu đã click gần đây => Không cộng điểm
            $pointsAdded = 0;
        }

        return response()->json([
            'message' => 'Click được ghi nhận!',
            'doctor_id' => $doctor_id,
            'product_id' => $product_id,
            'points_added' => $pointsAdded
        ], 200);
    }




}
