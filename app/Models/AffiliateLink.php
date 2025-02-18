<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $table = 'affiliate_links'; // Tên bảng trong database

    protected $primaryKey = 'id'; // Khóa chính

    public $timestamps = true; // Cho phép timestamps

    protected $fillable = [
        'doctor_id',
        'product_id',
        'affiliate_code',
        'hash_ref'
    ];

    /**
     * Liên kết với model Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Liên kết với model Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Tạo link affiliate mới với mã hash bảo mật
     */
    public static function createAffiliateLink($doctorId, $productId)
    {
        $affiliateCode = 'AFF-' . strtoupper(bin2hex(random_bytes(5))); // Mã gốc
        $hashRef = hash('sha256', $doctorId . $productId . time()); // Mã hash an toàn

        return self::create([
            'doctor_id' => $doctorId,
            'product_id' => $productId,
            'affiliate_code' => $affiliateCode, // Vẫn lưu affiliate_code nếu cần
            'hash_ref' => $hashRef, // Thêm cột hash_ref
        ]);
    }

    /**
     * Kiểm tra xem link affiliate có tồn tại không
     */
    public static function isAffiliateValid($doctorId, $productId)
    {
        return self::where('doctor_id', $doctorId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Tìm Affiliate theo hash_ref
     */
    public static function findByHash($hashRef)
    {
        return self::where('hash_ref', $hashRef)->first();
    }
}

