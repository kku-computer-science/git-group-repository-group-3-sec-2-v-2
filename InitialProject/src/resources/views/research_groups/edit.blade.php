@extends('dashboards.users.layouts.user-dash-layout')
@section('content')
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="card" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">แก้ไขข้อมูลกลุ่มวิจัย</h4>
            <p class="card-description">กรอกข้อมูลแก้ไขรายละเอียดกลุ่มวิจัย</p>
            <form action="{{ route('researchGroups.update', $researchGroup->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- ชื่อกลุ่มวิจัย (ภาษาไทย) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_th" value="{{ $researchGroup->group_name_th }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>

                <!-- ชื่อกลุ่มวิจัย (English) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_en" value="{{ $researchGroup->group_name_en }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>

                <!-- คำอธิบายกลุ่มวิจัย (ภาษาไทย) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_th" class="form-control" style="height:90px">{{ $researchGroup->group_desc_th }}</textarea>
                    </div>
                </div>

                <!-- คำอธิบายกลุ่มวิจัย (English) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_en" class="form-control" style="height:90px">{{ $researchGroup->group_desc_en }}</textarea>
                    </div>
                </div>

                <!-- รายละเอียดกลุ่มวิจัย (ภาษาไทย) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_th" class="form-control" style="height:90px">{{ $researchGroup->group_detail_th }}</textarea>
                    </div>
                </div>

                <!-- รายละเอียดกลุ่มวิจัย (English) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_en" class="form-control" style="height:90px">{{ $researchGroup->group_detail_en }}</textarea>
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
                        <input name="link" type="url" value="{{ old('link', $researchGroup->link) }}" class="form-control" placeholder="https://example.com">
                        <small class="form-text text-muted">
                            ถ้าคุณใส่ link ระบบจะนำคุณไปยังเว็บไซต์ที่คุณระบุแทนการแสดงข้อมูลในหน้านี้.
                        </small>
                    </div>
                </div>

                <!-- หัวหน้ากลุ่มวิจัย -->
                @php
                // ดึงข้อมูลของ head user จากความสัมพันธ์ (role = 1)
                $headUser = $researchGroup->user->firstWhere('pivot.role', 1);
                @endphp

                @if(auth()->user()->hasAnyRole(['admin', 'staff']))
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                    <div class="col-sm-8">
                        <select id="head0" name="head" class="form-control">
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" @if($headUser && $headUser->id == $user->id) selected @endif>
                                {{ $user->fname_th }} {{ $user->lname_th }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @else
                <!-- หากไม่ใช่ admin หรือ staff ให้แสดงเฉพาะข้อมูลโดยไม่สามารถแก้ไขได้ -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                    <div class="col-sm-8">
                        <input type="hidden" name="head" value="{{ $headUser->id ?? auth()->id() }}">
                        <p class="form-control-plaintext">
                            {{ $headUser ? $headUser->fname_th . ' ' . $headUser->lname_th : '' }}
                        </p>
                    </div>
                </div>
                @endif

                <!-- สมาชิกกลุ่มวิจัย (Role = 2) -->
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

                <!-- นักวิจัยรับเชิญ (Visiting Scholars) -->
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>นักวิจัยรับเชิญ</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemoveVisiting">
                            <tr>
                                <th>
                                    <button type="button" name="add" id="add-btn-visiting" class="btn btn-success btn-sm add-visiting">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- ปุ่ม Submit -->
                <button type="submit" class="btn btn-primary mt-5">Submit</button>
                <a class="btn btn-light mt-5" href="{{ route('researchGroups.index') }}">Back</a>
            </form>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
    $(document).ready(function() {
        // ทำ select2 ให้กับ #head0 (ถ้ามี)
        $("#head0").select2();

        // ดึงข้อมูลผู้ใช้ที่เกี่ยวข้องกับกลุ่มวิจัย (รูปแบบ JSON)
        var researchGroup = <?php echo $researchGroup['user']; ?>;
        var i = 0; // สำหรับสมาชิก (Role = 2)
        var j = 0; // สำหรับ Postdoctoral (Role = 3)

        // วนลูปแสดงสมาชิกที่มี role = 2 (Member)
        for (var idx = 0; idx < researchGroup.length; idx++) {
            var obj = researchGroup[idx];
            if (obj.pivot.role == 2) {
                $("#dynamicAddRemove").append(
                    '<tr>' +
                    '  <td>' +
                    '    <!-- Dropdown เลือก User -->' +
                    '    <select id="selUser' + i + '" name="moreFields[' + i + '][userid]" class="member-select" style="width: 200px;">' +
                    '      @foreach($users as $user)' +
                    '      <option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                    '      @endforeach' +
                    '    </select>' +
                    '  </td>' +
                    '  <td>' +
                    '    <!-- Dropdown เลือก Role -->' +
                    '<select name="moreFields[' + i + '][role]" class="form-control" style="width: 120px;">' +
                    '    <option value="PI">หัวหน้ากลุ่มวิจัย (PI)</option>' +
                    '    <option value="Postdoc">Postdoctoral Researcher</option>' +
                    '    <option value="PhD">นักศึกษาปริญญาเอก (Ph.D.)</option>' +
                    '    <option value="Master">นักศึกษาปริญญาโท</option>' +
                    '    <option value="Undergrad">นักศึกษาปริญญาตรี</option>' +
                    '    <option value="อื่นๆ">อื่นๆ</option>' +
                    '</select>' +
                    '  </td>' +
                    '  <td>' +
                    '    <!-- Dropdown เลือก Can Edit -->' +
                    '    <select name="moreFields[' + i + '][can_edit]" class="form-control" style="width: 120px;">' +
                    '      <option value="1">Can Edit</option>' +
                    '      <option value="0">Can\'t Edit</option>' +
                    '    </select>' +
                    '  </td>' +
                    '  <td>' +
                    '    <button type="button" class="btn btn-danger btn-sm remove-tr"><i class="mdi mdi-minus"></i></button>' +
                    '  </td>' +
                    '</tr>'
                );
                $("#selUser" + i).val(obj.id).select2();
                i++;
            }
        }

        // วนลูปแสดง Postdoctoral Researcher ที่มี role = 3
        // for (var k = 0; k < researchGroup.length; k++) {
        //     var obj2 = researchGroup[k];
        //     if (obj2.pivot.role == 3) {
        //         j++;
        //         $("#dynamicAddRemovePostdoc").append(
        //             '<tr>' +
        //             '  <td>' +
        //             '    <select id="selPostdoc' + j + '" name="postdocFields[' + j + '][userid]" class="postdoc-select" style="width: 200px;">' +
        //             '      <option value="">Select User</option>' +
        //             '      @foreach($users as $user)' +
        //             '      @if($user->doctoral_degree == "Ph.D.")' +
        //             '      <option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
        //             '      @endif' +
        //             '      @endforeach' +
        //             '    </select>' +
        //             '  </td>' +
        //             '  <td>' +
        //             '    <button type="button" class="btn btn-danger btn-sm remove-postdoc"><i class="mdi mdi-minus"></i></button>' +
        //             '  </td>' +
        //             '</tr>'
        //         );
        //         $("#selPostdoc" + j).val(obj2.id).select2();
        //     }
        // }

        // ------------------------------------------------------------------
        // เพิ่มสมาชิก (Role = 2)
        $("#add-btn2").click(function() {
            i++;
            $("#dynamicAddRemove").append(
                '<tr>' +
                '  <td>' +
                '    <!-- Dropdown เลือก User -->' +
                '    <select id="selUser' + i + '" name="moreFields[' + i + '][userid]" class="member-select" style="width: 200px;">' +
                '      <option value="">Select User</option>' +
                '      @foreach($users as $user)' +
                '      <option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                '      @endforeach' +
                '    </select>' +
                '  </td>' +
                '  <td>' +
                '    <!-- Dropdown เลือก Role -->' +
                '    <select name="moreFields[' + i + '][role]" class="form-control" style="width: 120px;">' +
                '      <option value="Postdoc">Postdoctoral Researcher</option>' +
                '      <option value="PhD">นักศึกษาปริญญาเอก (Ph.D.)</option>' +
                '      <option value="Master">นักศึกษาปริญญาโท</option>' +
                '      <option value="Undergrad">นักศึกษาปริญญาตรี</option>' +
                '      <option value="อื่นๆ">อื่นๆ</option>' +
                '    </select>' +
                '  </td>' +
                '  <td>' +
                '    <!-- Dropdown เลือก Can Edit -->' +
                '    <select name="moreFields[' + i + '][can_edit]" class="form-control" style="width: 120px;">' +
                '      <option value="1">Can Edit</option>' +
                '      <option value="0">Can\'t Edit</option>' +
                '    </select>' +
                '  </td>' +
                '  <td>' +
                '    <button type="button" class="btn btn-danger btn-sm remove-tr"><i class="mdi mdi-minus"></i></button>' +
                '  </td>' +
                '</tr>'
            );
            $("#selUser" + i).select2();
            removeOptionsInPostdoc();
        });

        // ลบสมาชิก (Role = 2)
        $(document).on('click', '.remove-tr', function() {
            $(this).parents('tr').remove();
            removeOptionsInPostdoc();
        });

        // ------------------------------------------------------------------
        // เพิ่ม Postdoctoral (Role = 3)
        // $("#add-btn-postdoc").click(function() {
        //     j++;
        //     $("#dynamicAddRemovePostdoc").append(
        //         '<tr>' +
        //         '  <td>' +
        //         '    <select id="selPostdoc' + j + '" name="postdocFields[' + j + '][userid]" class="postdoc-select" style="width: 200px;">' +
        //         '      <option value="">Select User</option>' +
        //         '      @foreach($users as $user)' +
        //         '      @if($user->doctoral_degree == "Ph.D.")' +
        //         '      <option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
        //         '      @endif' +
        //         '      @endforeach' +
        //         '    </select>' +
        //         '  </td>' +
        //         '  <td>' +
        //         '    <button type="button" class="btn btn-danger btn-sm remove-postdoc"><i class="mdi mdi-minus"></i></button>' +
        //         '  </td>' +
        //         '</tr>'
        //     );
        //     $("#selPostdoc" + j).select2();
        //     removeOptionsInPostdoc();
        // });

        // ลบ Postdoctoral
        // $(document).on('click', '.remove-postdoc', function() {
        //     $(this).parents('tr').remove();
        //     removeOptionsInPostdoc();
        // });

        // ------------------------------------------------------------------
        // เมื่อมีการ change ใน select ของ Member/Postdoc ก็ให้ลบ option ใหม่
        // $(document).on('change', '.member-select', function() {
        //     removeOptionsInPostdoc();
        // });
        // $(document).on('change', '.postdoc-select', function() {
        //     removeOptionsInPostdoc();
        // });

        // ------------------------------------------------------------------
        // ฟังก์ชันสำหรับ "ลบ" option ใน Postdoc ถ้าอยู่ใน Member
        // function removeOptionsInPostdoc() {
        //     // 1) รวบรวม user_id ที่ถูกเลือกใน Member
        //     var selectedMembers = [];
        //     $(".member-select").each(function() {
        //         var val = $(this).val();
        //         if (val) {
        //             selectedMembers.push(val);
        //         }
        //     });

        //     // 2) ในทุกๆ Postdoc <select> ให้ลบ <option> ที่ match กับ selectedMembers
        //     $(".postdoc-select").each(function() {
        //         var $thisSelect = $(this);
        //         var currentVal = $thisSelect.val(); // user ที่ select นี้เลือกอยู่

        //         // วนลูปทุก <option>
        //         $thisSelect.find("option").each(function() {
        //             var optVal = $(this).val();

        //             // ถ้าเป็น option ที่ตรงกับ selectedMembers และไม่ใช่ตัวที่ select นี้กำลังถืออยู่ -> ลบ
        //             if (optVal && selectedMembers.includes(optVal) && optVal !== currentVal) {
        //                 $(this).remove();
        //             }
        //         });

        //         // refresh select2
        //         $thisSelect.select2();
        //     });
        // }

        // // เรียกครั้งแรก
        // removeOptionsInPostdoc();

        // นักวิจัยรับเชิญ
        $(document).ready(function() {
            var v = 0; // ตัวนับสำหรับ visiting scholars

            // เมื่อคลิกปุ่มเพิ่มนักวิจัยรับเชิญ
            $("#add-btn-visiting").click(function() {
                v++;
                $("#dynamicAddRemoveVisiting").append(
                    '<tr>' +
                    '  <td>' +
                    '    <input type="text" name="visiting[' + v + '][first_name]" class="form-control" placeholder="ชื่อ">' +
                    '  </td>' +
                    '  <td>' +
                    '    <input type="text" name="visiting[' + v + '][last_name]" class="form-control" placeholder="นามสกุล">' +
                    '  </td>' +
                    '  <td>' +
                    '    <input type="text" name="visiting[' + v + '][affiliation]" class="form-control" placeholder="สังกัด">' +
                    '  </td>' +
                    '  <td>' +
                    '    <button type="button" class="btn btn-danger btn-sm remove-visiting"><i class="mdi mdi-minus"></i></button>' +
                    '  </td>' +
                    '</tr>'
                );
            });

            // เมื่อคลิกปุ่มลบในแต่ละแถว
            $(document).on('click', '.remove-visiting', function() {
                $(this).closest('tr').remove();
            });
        });
    });
</script>
@stop