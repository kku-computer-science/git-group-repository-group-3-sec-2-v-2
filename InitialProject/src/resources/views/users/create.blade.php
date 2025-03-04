@extends('dashboards.users.layouts.user-dash-layout')

@section('content')
<div class="container">
    <div class="justify-content-center">
        @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i>ข้อผิดพลาด!</strong> กรุณาตรวจสอบข้อมูลที่กรอก<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="col-md-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus mr-2"></i>เพิ่มผู้ใช้งานใหม่</h4>
                </div>
                <div class="card-body">
                    <p class="card-description text-muted mb-4"><i class="fas fa-info-circle mr-1"></i> กรุณากรอกข้อมูลให้ครบถ้วน ช่องที่มีเครื่องหมาย <span class="text-danger">*</span> เป็นช่องที่จำเป็นต้องกรอก</p>
                    
                    {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">ข้อมูลส่วนตัว</h5>
                        </div>
                        <div class="card-body">
                            <!-- ฟอร์มสำหรับชื่อ-นามสกุล ภาษาไทย -->
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3">
                                    <label for="fname_th" class="font-weight-bold">ชื่อ (ภาษาไทย) <span class="text-danger">*</span></label>
                                    {!! Form::text('fname_th', null, ['placeholder' => 'ชื่อภาษาไทย','class' => 'form-control', 'id' => 'fname_th']) !!}
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label for="lname_th" class="font-weight-bold">นามสกุล (ภาษาไทย) <span class="text-danger">*</span></label>
                                    {!! Form::text('lname_th', null, ['placeholder' => 'นามสกุลภาษาไทย','class' => 'form-control', 'id' => 'lname_th']) !!}
                                </div>
                            </div>
                            
                            <!-- ฟอร์มสำหรับชื่อ-นามสกุล ภาษาอังกฤษ -->
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3">
                                    <label for="fname_en" class="font-weight-bold">ชื่อ (English) <span class="text-danger">*</span></label>
                                    {!! Form::text('fname_en', null, ['placeholder' => 'First name','class' => 'form-control', 'id' => 'fname_en']) !!}
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label for="lname_en" class="font-weight-bold">นามสกุล (English) <span class="text-danger">*</span></label>
                                    {!! Form::text('lname_en', null, ['placeholder' => 'Last name','class' => 'form-control', 'id' => 'lname_en']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">ข้อมูลบัญชีผู้ใช้</h5>
                        </div>
                        <div class="card-body">
                            <!-- ฟอร์มสำหรับ Email -->
                            <div class="form-group row">
                                <div class="col-sm-8 mb-3">
                                    <label for="email" class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        {!! Form::text('email', null, ['placeholder' => 'Email','class' => 'form-control', 'id' => 'email']) !!}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ฟอร์มสำหรับ Password และ Confirm Password -->
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3">
                                    <label for="password" class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        {!! Form::password('password', ['placeholder' => 'Password','class' => 'form-control', 'id' => 'password']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label for="password_confirmation" class="font-weight-bold">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        {!! Form::password('password_confirmation', ['placeholder' => 'Confirm Password','class' =>'form-control', 'id' => 'password_confirmation']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">ข้อมูลสิทธิ์และสังกัด</h5>
                        </div>
                        <div class="card-body">
                            <!-- ฟอร์มสำหรับเลือก Role -->
                            <div class="form-group row">
                                <div class="col-sm-8 mb-3">
                                    <label for="roles" class="font-weight-bold">Role <span class="text-danger">*</span></label>
                                    <div class="role-select-container">
                                        <div class="role-icon">
                                            <i class="fas fa-user-tag"></i>
                                        </div>
                                        {!! Form::select('roles[]', $roles, [], ['class' => 'form-control custom-select2', 'id' => 'roles', 'multiple' => 'multiple']) !!}
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-info"><i class="fas fa-info-circle mr-1"></i> หากเลือก role เป็น teacher ระบบจะกำหนดให้เป็นนักวิจัยโดยอัตโนมัติ</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ฟอร์มสำหรับเลือก Department และ Program -->
                            <div class="form-group row">
                                <div class="col-md-6 mb-3">
                                    <label for="cat" class="font-weight-bold">Department <span class="text-danger">*</span></label>
                                    <div class="dept-select-container">
                                        <div class="dept-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <select class="form-control custom-select2" name="cat" id="cat" required>
                                            <option value="">เลือกภาควิชา</option>
                                            @foreach ($departments as $cat)
                                            <option value="{{$cat->id}}">{{ $cat->department_name_en }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subcat" class="font-weight-bold">Program <span class="text-danger">*</span></label>
                                    <div class="prog-select-container">
                                        <div class="prog-icon">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <select class="form-control custom-select2" name="sub_cat" id="subcat" required>
                                            <option value="">เลือกหลักสูตร</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- เพิ่ม Checkbox สำหรับ is_research -->
                            <div class="form-group row mt-3">
                                <div class="col-sm-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="custom-control custom-switch">
                                                {!! Form::checkbox('is_research', 1, false, ['class' => 'custom-control-input', 'id' => 'is_research']) !!}
                                                <label class="custom-control-label font-weight-bold" for="is_research">เป็นนักวิจัย</label>
                                                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle mr-1"></i> สำหรับ role อื่นที่ไม่ใช่ teacher (teacher จะถูกกำหนดเป็นนักวิจัยโดยอัตโนมัติ)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ปุ่ม Submit และ Cancel -->
                    <div class="form-group text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5 mr-3">
                            <i class="fas fa-save mr-2"></i>บันทึกข้อมูล
                        </button>
                        <a class="btn btn-secondary btn-lg px-5" href="{{ route('users.index') }}">
                            <i class="fas fa-times mr-2"></i>ยกเลิก
                        </a>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script สำหรับ Ajax ดึงข้อมูล Subcategory -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for better dropdown UI
        $('.custom-select2').select2({
            minimumResultsForSearch: 10,
            width: '100%',
            dropdownCssClass: 'custom-select2-dropdown'
        });

        // Special initialization for roles select
        $('#roles').select2({
            minimumResultsForSearch: 10,
            width: '100%',
            dropdownCssClass: 'custom-select2-dropdown',
            placeholder: 'เลือกบทบาท',
            allowClear: true
        });
        
        // Fix for placeholder appearance
        $('#roles').on('select2:open', function() {
            if (!$(this).val() || $(this).val().length === 0) {
                $('.select2-search__field').attr('placeholder', 'ค้นหาบทบาท');
            }
        });
        
        // Ajax for Department-Program relationship
        $('#cat').on('change', function(e) {
            var cat_id = e.target.value;
            if(cat_id) {
                $.get('/ajax-get-subcat?cat_id=' + cat_id, function(data) {
                    $('#subcat').empty();
                    $('#subcat').append('<option value="">เลือกหลักสูตร</option>');
                    $.each(data, function(index, areaObj) {
                        $('#subcat').append('<option value="' + areaObj.id + '">' + areaObj.degree.title_en +' in '+ areaObj.program_name_en + '</option>');
                    });
                    $('#subcat').trigger('change');
                });
            } else {
                $('#subcat').empty();
                $('#subcat').append('<option value="">เลือกหลักสูตร</option>');
                $('#subcat').trigger('change');
            }
        });

        // ฟังก์ชันสำหรับตรวจสอบว่ามี role teacher หรือไม่
        function checkTeacherRole() {
            var hasTeacherRole = false;
            var selectedRoles = $('#roles').val() || [];
            
            $('#roles option:selected').each(function() {
                if ($(this).text().toLowerCase() === 'teacher') {
                    hasTeacherRole = true;
                    return false; // break loop
                }
            });
            
            // ถ้ามี role teacher ให้ check และ disable checkbox is_research
            if (hasTeacherRole) {
                $('#is_research').prop('checked', true);
                $('#is_research').prop('disabled', true);
            } else {
                $('#is_research').prop('disabled', false);
            }
        }
        
        // ตรวจสอบเมื่อมีการเปลี่ยนแปลงใน select roles
        $('#roles').on('change', function() {
            checkTeacherRole();
        });
        
        // ตรวจสอบตั้งแต่เริ่มต้น
        checkTeacherRole();
    });
</script>

<style>
    .card {
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
        border: 1px solid #e3e6f0;
    }
    
    .card-header {
        font-weight: bold;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .form-control {
        border-radius: 5px;
        height: 45px;
        border: 1px solid #d1d3e2;
    }
    
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .btn {
        border-radius: 5px;
        font-weight: bold;
        padding: 0.5rem 1.2rem;
    }
    
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
    }
    
    .btn-secondary {
        background-color: #858796;
        border-color: #858796;
    }
    
    .btn-secondary:hover {
        background-color: #717384;
        border-color: #6c6e7c;
    }
    
    /* Custom switch styling */
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .custom-switch .custom-control-label::before {
        height: 1.5rem;
        width: 2.75rem;
        border-radius: 1rem;
    }
    
    .custom-switch .custom-control-label::after {
        height: calc(1.5rem - 4px);
        width: calc(1.5rem - 4px);
        border-radius: 50%;
    }
    
    /* Custom select container styling */
    .role-select-container, 
    .dept-select-container, 
    .prog-select-container {
        position: relative;
    }
    
    .role-icon, 
    .dept-icon, 
    .prog-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        color: #6c757d;
    }
    
    /* Select2 custom styling */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        height: 45px;
        border: 1px solid #d1d3e2;
        border-radius: 5px;
        padding-left: 40px; /* Space for icon */
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 45px;
        padding-left: 0;
        color: #6e707e;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 5px 0;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4e73df;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d3e2;
        border-radius: 3px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #4e73df;
        border-color: #4e73df;
        color: white;
        border-radius: 3px;
        padding: 2px 10px;
        margin-top: 7px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }
    
    .select2-dropdown {
        border: 1px solid #d1d3e2;
        border-radius: 5px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .select2-container--open .select2-dropdown--below {
        border-top: none;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    
    .input-group-text {
        background-color: #f8f9fc;
        border: 1px solid #d1d3e2;
    }
    
    /* Fix to allow select2 to work inside bootstrap modal */
    .select2-container {
        z-index: 1050;
    }
    
    /* Placeholder style */
    .select2-container--default .select2-selection--multiple .select2-search__field::placeholder {
        color: #858796;
    }
    
    /* Fix for select2 inside input-group */
    .input-group > .select2-container--default {
        width: auto;
        flex: 1 1 auto;
    }
    
    .input-group > .select2-container--default .select2-selection--single {
        height: 100%;
        line-height: 1.5;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>
@endsection
