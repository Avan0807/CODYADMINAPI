<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateOrder;
use App\Models\Order;
use App\Models\Doctor;

class AffiliateOrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng Affiliate.
     */
    public function index()
    {
        // Make sure the affiliateOrders are being retrieved correctly
        $affiliateOrders = AffiliateOrder::with(['order', 'doctor'])->latest()->paginate(10);
        
        return view('backend.affiliate_orders.index', compact('affiliateOrders'));
    }
    

    /**
     * Cập nhật trạng thái đơn hàng Affiliate.
     */
    public function updateStatus(Request $request, $id)
    {
        $affiliateOrder = AffiliateOrder::findOrFail($id);

        $validStatuses = ['pending', 'approved', 'paid', 'rejected'];
        if (!in_array($request->status, $validStatuses)) {
            return redirect()->back()->with('error', 'Trạng thái không hợp lệ!');
        }

        $affiliateOrder->status = $request->status;
        $affiliateOrder->save();

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
    }
}
