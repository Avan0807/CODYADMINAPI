@extends('backend.layouts.master')

@section('title','Quản lý Đơn Hàng Affiliate')

@section('main-content')
<div class="container-fluid">
    <h4 class="mb-3">Danh sách đơn hàng Affiliate</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Bác sĩ</th>
                <th>Đơn hàng</th>
                <th>Hoa hồng</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($affiliateOrders as $order)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $order->doctor->name }}</td>
                <td>{{ $order->order->order_number }}</td>
                <td>{{ number_format($order->commission, 0, ',', '.') }} VNĐ</td>
                <td>
                    <span class="badge badge-{{ $order->status == 'approved' ? 'success' : 'warning' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>
                    <form action="{{ route('admin.affiliate.orders.update', $order->id) }}" method="POST">
                        @csrf
                        <select name="status" class="form-control">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="approved" {{ $order->status == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                            <option value="rejected" {{ $order->status == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm mt-1">Cập nhật</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $affiliateOrders->links() }}
</div>
@endsection
