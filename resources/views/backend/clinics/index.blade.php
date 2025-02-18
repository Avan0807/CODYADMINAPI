@extends('backend.layouts.master')

@section('title','CODY || Danh sách phòng khám')

@section('main-content')

<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>

    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách phòng khám</h6>
      <a href="{{ route('clinics.create') }}" class="btn btn-primary btn-sm float-right">
          <i class="fas fa-plus"></i> Thêm phòng khám
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        @if($clinics->count() > 0)
        <table class="table table-bordered table-hover" id="clinic-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Tên phòng khám</th>
              <th>Địa chỉ</th>
              <th>Điện thoại</th>
              <th>Email</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($clinics as $clinic)   
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $clinic->name }}</td>
                    <td>{{ $clinic->address }}</td>
                    <td>{{ $clinic->phone }}</td>
                    <td>{{ $clinic->email }}</td>
                    <td>
                        <a href="{{ route('clinics.edit', $clinic->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form method="POST" action="{{ route('clinics.destroy', $clinic->id) }}" class="d-inline">
                          @csrf 
                          @method('delete')
                          <button type="submit" class="btn btn-danger btn-sm dltBtn">
                              <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{ $clinics->links() }}</span>
        @else
          <h6 class="text-center">Không có phòng khám nào! Vui lòng thêm mới.</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
@endpush

@push('scripts')
  <script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <script>
      $(document).ready(function(){
          $('#notification-dataTable').DataTable({
              "columnDefs": [
                  {
                      "orderable": false,
                      "targets": [4]
                  }
              ]
          });

          $('.dltBtn').click(function(e){
              e.preventDefault();
              var form = $(this).closest('form');

              swal({
                  title: "Bạn có chắc không?",
                  text: "Sau khi xóa, bạn sẽ không thể khôi phục dữ liệu này!",
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
              })
              .then((willDelete) => {
                  if (willDelete) {
                      form.submit();
                  } else {
                      swal("Dữ liệu của bạn vẫn an toàn!");
                  }
              });
          });
      });
  </script>
@endpush