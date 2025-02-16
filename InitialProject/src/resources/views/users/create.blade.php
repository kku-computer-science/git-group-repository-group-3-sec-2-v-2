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
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card" style="padding: 16px;">
                <div class="card-body">
                    <h4 class="card-title mb-5">เพิ่มผู้ใช้งาน</h4>
                    <p class="card-description">กรอกข้อมูลแก้ไขรายละเอียดผู้ใช้งาน</p>
                    {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
                    
                    <!-- ฟอร์มสำหรับชื่อ-นามสกุล ภาษาไทย -->
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <p><b>ชื่อ (ภาษาไทย)</b></p>
                            {!! Form::text('fname_th', null, ['placeholder' => 'ชื่อภาษาไทย','class' => 'form-control']) !!}
                        </div>
                        <div class="col-sm-6">
                            <p><b>นามสกุล (ภาษาไทย)</b></p>
                            {!! Form::text('lname_th', null, ['placeholder' => 'นามสกุลภาษาไทย','class' => 'form-control']) !!}
                        </div>
                    </div>
                    
                    <!-- ฟอร์มสำหรับชื่อ-นามสกุล ภาษาอังกฤษ -->
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <p><b>ชื่อ (English)</b></p>
                            {!! Form::text('fname_en', null, ['placeholder' => 'ชื่อภาษาอังกฤษ','class' => 'form-control']) !!}
                        </div>
                        <div class="col-sm-6">
                            <p><b>นามสกุล (English)</b></p>
                            {!! Form::text('lname_en', null, ['placeholder' => 'นามสกุลภาษาอังกฤษ','class' => 'form-control']) !!}
                        </div>
                    </div>
                    
                    <!-- ฟอร์มสำหรับ Email -->
                    <div class="form-group row">
                        <div class="col-sm-8">
                            <p><b>Email</b></p>
                            {!! Form::text('email', null, ['placeholder' => 'Email','class' => 'form-control']) !!}
                        </div>
                    </div>
                    
                    <!-- ฟอร์มสำหรับ Password และ Confirm Password -->
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <p><b>Password:</b></p>
                            {!! Form::password('password', ['placeholder' => 'Password','class' => 'form-control']) !!}
                        </div>
                        <div class="col-sm-6">
                            <p><b>Confirm Password:</b></p>
                            {!! Form::password('password_confirmation', ['placeholder' => 'Confirm Password','class' =>'form-control']) !!}
                        </div>
                    </div>
                    
                    <!-- ฟอร์มสำหรับเลือก Role -->
                    <div class="form-group col-sm-8">
                        <p><b>Role:</b></p>
                        <div class="col-sm-8">
                            {!! Form::select('roles[]', $roles, [], ['class' => 'selectpicker','multiple']) !!}
                        </div>
                    </div>
                    
                    <!-- ฟอร์มสำหรับเลือก Department และ Program -->
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 for="category">Department <span class="text-danger">*</span></h6>
                                <select class="form-control" name="cat" id="cat" style="width: 100%;" required>
                                    <option>Select Category</option>
                                    @foreach ($departments as $cat)
                                    <option value="{{$cat->id}}">{{ $cat->department_name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <h6 for="subcat">Program <span class="text-danger">*</span></h6>
                                <select class="form-control select2" name="sub_cat" id="subcat" required>
                                    <option value="">Select Subcategory</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- เพิ่ม Checkbox สำหรับ is_research -->
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="form-check">
                                {!! Form::checkbox('is_research', 1, false, ['class' => 'form-check-input', 'id' => 'is_research']) !!}
                                <label class="form-check-label" for="is_research">เป็นนักวิจัยหรือไม่?</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ปุ่ม Submit และ Cancel -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a class="btn btn-secondary" href="{{ route('users.index') }}">Cancel</a>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script สำหรับ Ajax ดึงข้อมูล Subcategory -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->

<script>
    $('#cat').on('change', function(e) {
        var cat_id = e.target.value;
        $.get('/ajax-get-subcat?cat_id=' + cat_id, function(data) {
            $('#subcat').empty();
            $.each(data, function(index, areaObj) {
                $('#subcat').append('<option value="' + areaObj.id + '">' + areaObj.degree.title_en +' in '+ areaObj.program_name_en + '</option>');
            });
        });
    });
</script>
@endsection
