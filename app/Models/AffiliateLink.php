<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model {
    use HasFactory;

    protected $table = 'affiliate_links';

    protected $fillable = [
        'doctor_id',
        'product_id',
        'affiliate_code'
    ];

    // Mối quan hệ với bác sĩ
    public function doctor() {
        return $this->belongsTo(Doctor::class);
    }

    // Mối quan hệ với sản phẩm
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
