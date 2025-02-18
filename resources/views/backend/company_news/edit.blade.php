@extends('backend.layouts.master')

@section('title','CODY || Chỉnh sửa Tin Tức Công Ty')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh Sửa Tin Tức</h5>
    <div class="card-body">
      <form method="post" action="{{ route('company_news.update', $news->id) }}" enctype="multipart/form-data">
        @csrf 
        @method('PATCH')

        <div class="form-group">
          <label for="title" class="col-form-label">Tiêu đề <span class="text-danger">*</span></label>
          <input id="title" type="text" name="title" value="{{ $news->title }}" class="form-control">
          @error('title')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="content" class="col-form-label">Nội dung <span class="text-danger">*</span></label>
          <textarea class="form-control" id="content" name="content">{{ $news->content }}</textarea>
          @error('content')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="image" class="col-form-label">Ảnh</label>
          <input type="file" class="form-control" name="image">
          @if($news->image)
            <img src="{{ asset('storage/'.$news->image) }}" class="img-fluid mt-2" style="max-width:80px">
          @endif
        </div>

        <div class="form-group">
          <label for="published_at" class="col-form-label">Ngày xuất bản</label>
          <input type="date" class="form-control" name="published_at" value="{{ $news->published_at }}">
        </div>

        <div class="form-group">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
          <button class="btn btn-success" type="submit">Cập nhật</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script>
    $(document).ready(function() {
      $('#message').summernote({
        placeholder: "Nhập tin tức.....",
          tabsize: 2,
          height: 150
      });
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script>
    $(document).ready(function() {
      $('#message').summernote({
        placeholder: "Nhập tin tức...",
          tabsize: 2,
          height: 150
      });
    });
</script>
@endpush