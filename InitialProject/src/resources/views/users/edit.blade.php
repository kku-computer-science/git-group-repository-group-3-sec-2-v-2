@extends('dashboards.users.layouts.user-dash-layout')

@section('content')
<div class="container">
    <div class="justify-content-center">
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Opps!</strong> Something went wrong, please check below errors.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card col-8" style="padding: 16px;">
            <div class="card-body">
                <h4 class="card-title">แก้ไขข้อมูลผู้ใช้งาน</h4>
                <p class="card-description">กรอกข้อมูลแก้ไขรายละเอียดผู้ใช้งาน</p>
                {!! Form::model($user, ['route' => ['users.update', $user->id], 'method'=>'PATCH']) !!}
                    
                    <!-- ชื่อ-นามสกุล ภาษาไทย -->
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <p><b>ชื่อ (ภาษาไทย)</b></p>
                            <input type="text" name="fname_th" value="{{ $user->fname_th }}" class="form-control" placeholder="ชื่อภาษาไทย">
                        </div>
                        <div class="col-sm-6">
                            <p><b>นามสกุล (ภาษาไทย)</b></p>
                            <input type="text" name="lname_th" value="{{ $user->lname_th }}" class="form-control" placeholder="นามสกุลภาษาไทย">
                        </div>
                    </div>
                    
                    <!-- ชื่อ-นามสกุล ภาษาอังกฤษ -->
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <p><b>ชื่อ (English)</b></p>
                            <input type="text" name="fname_en" value="{{ $user->fname_en }}" class="form-control" placeholder="First name">
                        </div>
                        <div class="col-sm-6">
                            <p><b>นามสกุล (English)</b></p>
                            <input type="text" name="lname_en" value="{{ $user->lname_en }}" class="form-control" placeholder="Last name">
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group row">
                        <p class="col-sm-3"><b>Email</b></p>
                        <div class="col-sm-8">
                            <input type="text" name="email" value="{{ $user->email }}" class="form-control">
                        </div>
                    </div>
                    
                    <!-- Password และ Confirm Password -->
                    <div class="form-group row">
                        <p class="col-sm-3"><b>Password</b></p>
                        <div class="col-sm-8">
                            <input type="password" name="password" class="form-control" placeholder="ใส่รหัสผ่านหากต้องการเปลี่ยน">
                        </div>
                    </div>
                    <div class="form-group row">
                        <p class="col-sm-3"><b>Confirm Password</b></p>
                        <div class="col-sm-8">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="ยืนยันรหัสผ่าน">
                        </div>
                    </div>
                    
                    <!-- Role -->
                    <div class="form-group row">
                        <p class="col-sm-3"><b>Role</b></p>
                        <div class="col-sm-8">
                            {!! Form::select('roles[]', $roles, $userRole, ['class' => 'selectpicker', 'multiple', 'data-live-search' => "true"]) !!}
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="form-group row">
                        <p class="col-sm-3"><b>Status</b></p>
                        <div class="col-sm-8">
                            <select id="status" class="form-control" style="width: 200px;" name="status">
                                <option value="1" {{ "1" == $user->status ? 'selected' : '' }}>กำลังศึกษา</option>
                                <option value="2" {{ "2" == $user->status ? 'selected' : '' }}>จบการศึกษา</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Department และ Program -->
                    <div class="form-group row">
                        <div class="col-md-6">
                            <p><b>Department <span class="text-danger">*</span></b></p>
                            <select class="form-control" name="cat" id="cat" required>
                                <option>Select Category</option>
                                @foreach ($departments as $cat)
                                    <option value="{{ $cat->id }}" {{ $user->program->department_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->department_name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <p><b>Program <span class="text-danger">*</span></b></p>
                            <select class="form-control select2" name="sub_cat" id="subcat" required>
                                <option>Select Category</option>
                                @foreach ($programs as $prog)
                                    <option value="{{ $prog->id }}" {{ $user->program->id == $prog->id ? 'selected' : '' }}>
                                        {{ $prog->program_name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Checkbox สำหรับ is_research -->
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_research" value="1" class="form-check-input" id="is_research" {{ $user->is_research == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_research">เป็นนักวิจัยหรือไม่?</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-5">Submit</button>
                    <a class="btn btn-light mt-5" href="{{ route('users.index') }}">Cancel</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<!-- Script สำหรับ Ajax ดึงข้อมูล Subcategory เมื่อเลือก Department -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
<script>
    $('#cat').on('change', function(e) {
        var cat_id = e.target.value;
        $.get('/ajax-get-subcat?cat_id=' + cat_id, function(data) {
            $('#subcat').empty();
            $.each(data, function(index, areaObj) {
                $('#subcat').append('<option value="' + areaObj.id + '">' + areaObj.program_name_en + '</option>');
            });
        });
    });
</script>
@endsection
