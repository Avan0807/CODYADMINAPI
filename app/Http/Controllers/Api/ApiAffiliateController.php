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
}
