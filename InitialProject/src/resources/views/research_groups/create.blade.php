@extends('dashboards.users.layouts.user-dash-layout')
@section('content')
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> มีปัญหาบางอย่างกับข้อมูลที่คุณกรอก<br><br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">สร้างกลุ่มวิจัย</h4>
            <p class="card-description">กรอกข้อมูลและรายละเอียดกลุ่มวิจัย</p>
            <form action="{{ route('researchGroups.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- ชื่อกลุ่มวิจัย (ภาษาไทย) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_th" value="{{ old('group_name_th') }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>

                <!-- ชื่อกลุ่มวิจัย (English) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_en" value="{{ old('group_name_en') }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>

                <!-- คำอธิบายกลุ่มวิจัย (ภาษาไทย) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_th" class="form-control" style="height:90px" placeholder="คำอธิบายกลุ่มวิจัย (ภาษาไทย)">{{ old('group_desc_th') }}</textarea>
                    </div>
                </div>

                <!-- คำอธิบายกลุ่มวิจัย (English) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_en" class="form-control" style="height:90px" placeholder="คำอธิบายกลุ่มวิจัย (English)">{{ old('group_desc_en') }}</textarea>
                    </div>
                </div>

                <!-- รายละเอียดกลุ่มวิจัย (ภาษาไทย) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_th" class="form-control" style="height:90px" placeholder="รายละเอียดกลุ่มวิจัย (ภาษาไทย)">{{ old('group_detail_th') }}</textarea>
                    </div>
                </div>

                <!-- รายละเอียดกลุ่มวิจัย (English) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_en" class="form-control" style="height:90px" placeholder="รายละเอียดกลุ่มวิจัย (English)">{{ old('group_detail_en') }}</textarea>
                    </div>
                </div>

                <!-- Image -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>Image</b></p>
                    <div class="col-sm-8">
                        <input type="file" name="group_image" class="form-control">
                    </div>
                </div>

                <!-- Link (ถ้ามี) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>Link (ถ้ามี)</b></p>
                    <div class="col-sm-8">
                        <input name="link" type="url" value="{{ old('link') }}" class="form-control" placeholder="https://example.com">
                        <small class="form-text text-muted">หากคุณกรอก link ระบบจะพาคุณไปยังเว็บไซต์นั้น แทนการแสดงข้อมูลในหน้านี้</small>
                    </div>
                </div>

                <!-- หัวหน้ากลุ่มวิจัย -->
                @if(auth()->user()->hasAnyRole(['admin', 'staff']))
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                    <div class="col-sm-8">
                        <select id="head0" name="head">
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->fname_th }} {{ $user->lname_th }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @else
                <!-- หากไม่ใช่ admin/staff ให้ตั้งค่า head เป็นผู้ใช้ที่ล็อกอินอยู่ -->
                <input type="hidden" name="head" value="{{ auth()->id() }}">
                @endif

                <!-- สมาชิกกลุ่มวิจัย (Member) -->
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>สมาชิกกลุ่มวิจัย</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemove">
                            <tr>
                                <th>
                                    <button type="button" name="add" id="add-btn2" class="btn btn-success btn-sm add">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Postdoctoral Researcher (Role = 3) -->
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>Postdoctoral Researcher</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemovePostdoc">
                            <tr>
                                <th>
                                    <button type="button" name="add" id="add-btn-postdoc" class="btn btn-success btn-sm add">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </table>
                        <small class="form-text text-muted">ค้นหาได้เฉพาะผู้ที่จบเอก (Ph.D.) เท่านั้น</small>
                    </div>
                </div>

                <!-- ปุ่ม Submit และ Back -->
                <button type="submit" class="btn btn-primary upload mt-5">Submit</button>
                <a class="btn btn-light mt-5" href="{{ route('researchGroups.index') }}">Back</a>
            </form>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
    $(document).ready(function() {
        // Select2 สำหรับหัวหน้ากลุ่ม (กรณีเป็น admin/staff)
        $("#head0").select2();

        // ------------------------------------------------------------------
        // สร้างตาราง "Member"
        var i = 0;
        $("#add-btn2").click(function() {
            ++i;
            $("#dynamicAddRemove").append(
                '<tr>' +
                    '<td>' +
                        '<select id="selUser' + i + '" name="moreFields[' + i + '][userid]" style="width: 200px;">' +
                            '<option value="">Select User</option>' +
                            '@foreach($users as $user)' +
                                '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                            '@endforeach' +
                        '</select>' +
                    '</td>' +
                    '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm remove-tr"><i class="fas fa-minus"></i></button>' +
                    '</td>' +
                '</tr>'
            );
            // ทำ select2
            $("#selUser" + i).select2();
        });

        // ลบแถว (Member)
        $(document).on('click', '.remove-tr', function() {
            $(this).parents('tr').remove();
            updateUsedArrays();
            syncOptions();
        });

        // ------------------------------------------------------------------
        // สร้างตาราง "Postdoc"
        var j = 0;
        $("#add-btn-postdoc").click(function() {
            j++;
            $("#dynamicAddRemovePostdoc").append(
                '<tr>' +
                    '<td>' +
                        '<select id="selPostdoc' + j + '" name="postdocFields[' + j + '][userid]" style="width: 200px;">' +
                            '<option value="">Select User</option>' +
                            '@foreach($users as $user)' +
                                '@if($user->doctoral_degree == "Ph.D.")' +
                                    '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                                '@endif' +
                            '@endforeach' +
                        '</select>' +
                    '</td>' +
                    '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm remove-postdoc"><i class="fas fa-minus"></i></button>' +
                    '</td>' +
                '</tr>'
            );
            $("#selPostdoc" + j).select2();
        });

        // ลบแถว (Postdoc)
        $(document).on('click', '.remove-postdoc', function() {
            $(this).parents('tr').remove();
            updateUsedArrays();
            syncOptions();
        });

        // ------------------------------------------------------------------
        // จัดการเรื่อง "ห้ามซ้ำระหว่าง Member กับ Postdoc" ด้วยการ 'ลบ' <option> ออกจากอีกฝั่ง
        let usedMembers = [];
        let usedPostdocs = [];

        function updateUsedArrays() {
            usedMembers = [];
            usedPostdocs = [];

            // รวบรวม user_id ในตาราง Member
            $('table#dynamicAddRemove select').each(function() {
                let val = $(this).val();
                if (val) {
                    usedMembers.push(val);
                }
            });

            // รวบรวม user_id ในตาราง Postdoc
            $('table#dynamicAddRemovePostdoc select').each(function() {
                let val = $(this).val();
                if (val) {
                    usedPostdocs.push(val);
                }
            });
        }

        function syncOptions() {
            // --------------------------------------------------
            // 1) ในตาราง Postdoc: ลบ <option> ออกทั้งหมดที่ "ถูกเลือก" ใน Member
            $('table#dynamicAddRemovePostdoc select').each(function() {
                let currentVal = $(this).val(); // user ที่ select นี้เลือกอยู่
                $(this).find('option').each(function(){
                    let optVal = $(this).val();
                    // หาก optVal อยู่ใน usedMembers และไม่ใช่คนที่ select นี้เลือกอยู่ -> ลบออก
                    if (optVal && usedMembers.includes(optVal) && optVal !== currentVal) {
                        $(this).remove();
                    }
                });
            });

            // --------------------------------------------------
            // 2) ในตาราง Member: ลบ <option> ออกทั้งหมดที่ "ถูกเลือก" ใน Postdoc
            $('table#dynamicAddRemove select').each(function() {
                let currentVal = $(this).val();
                $(this).find('option').each(function(){
                    let optVal = $(this).val();
                    if (optVal && usedPostdocs.includes(optVal) && optVal !== currentVal) {
                        $(this).remove();
                    }
                });
            });
        }

        // เรียกฟังก์ชันทุกครั้งเมื่อมีการเปลี่ยนแปลงใน select
        $(document).on('change', 'table#dynamicAddRemove select, table#dynamicAddRemovePostdoc select', function() {
            updateUsedArrays();
            syncOptions();
        });
    });
</script>
@stop
