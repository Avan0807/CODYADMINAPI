<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateOrder extends Model
{
    use HasFactory;

    protected $table = 'affiliate_orders';

    protected $fillable = [
        'order_id',
        'doctor_id',
        'commission',
        'status',
    ];

    /**
     * Định nghĩa quan hệ với bảng `orders`
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Định nghĩa quan hệ với bảng `doctors`
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Đếm tổng số đơn hàng tiếp thị
     */
    public static function countAffiliateOrders()
    {
        return self::count();
    }

    /**
     * Tính tổng hoa hồng từ Affiliate Orders
     */
    public static function totalAffiliateCommission()
    {
        return self::where('status', 'pending')->sum('commission');
    }


}
