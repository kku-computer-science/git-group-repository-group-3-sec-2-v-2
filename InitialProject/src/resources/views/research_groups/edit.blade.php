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
            <form action="{{ route('researchGroups.update',$researchGroup->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group row">
                    <p class="col-sm-3 "><b>URL</b></p>
                    <div class="col-sm-8">
                        <input name="group_url" class="form-control" placeholder="URL กลุ่มวิจัย" value="{{ old('group_url') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3 "><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_th" value="{{ $researchGroup->group_name_th }}" class="form-control"
                            placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3 "><b>ชื่อกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_en" value="{{ $researchGroup->group_name_en }}" class="form-control"
                            placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_th" value="{{ $researchGroup->group_desc_th }}" class="form-control"
                            style="height:90px">{{ $researchGroup->group_desc_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_en" value="{{ $researchGroup->group_desc_en }}" class="form-control"
                            style="height:90px">{{ $researchGroup->group_desc_en }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_th" value="{{ $researchGroup->group_detail_th }}" class="form-control"
                            style="height:90px">{{ $researchGroup->group_detail_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_en" value="{{ $researchGroup->group_detail_en }}" class="form-control"
                            style="height:90px">{{ $researchGroup->group_detail_en }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>image</b></p>
                    <div class="col-sm-8">
                        <input type="file" name="group_image" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                    <div class="col-sm-8">
                        <select id='head0' name="head">
                            @foreach($researchGroup->user as $u)
                            @if($u->pivot->role == 1)
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" @if($u->id == $user->id) selected @endif>
                                {{ $user->fname_th }} {{ $user->lname_th }}
                            </option>
                            @endforeach
                            @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>สมาชิกกลุ่มวิจัย</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemove">
                            <tr>
                                <th><button type="button" name="add" id="add-btn2" class="btn btn-success btn-sm add"><i
                                            class="mdi mdi-plus"></i></button></th>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>Post_Doctoral</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="postdocAddRemove">
                            <tr>
                                <th><button type="button" name="add" id="add-btn3" class="btn btn-success btn-sm add"><i
                                            class="mdi mdi-plus"></i></button></th>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>Visiting</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="visitingAddRemove">
                            <tr>
                                <th>
                                    <button type="button" id="add-btn4" class="btn btn-success btn-sm add"><i class="mdi mdi-plus"></i></button>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>Students</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="studentAddRemove">
                            <tr>
                                <th><button type="button" name="add" id="add-btn5" class="btn btn-success btn-sm add"><i
                                            class="mdi mdi-plus"></i></button></th>
                            </tr>
                        </table>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-5">Submit</button>
                <a class="btn btn-light mt-5" href="{{ route('researchGroups.index') }}"> Back</a>
            </form>
        </div>
    </div>
</div>
@stop
@section('javascript')
<script>
    $(document).ready(function() {
        $("#head0").select2()
        $("#fund").select2()


        var researchGroup = <?php echo $researchGroup['user']; ?>;
        var i = 0;
        var postdocIndex = 0;
        var visitingIndex = 0;
        var studentIndex = 0;

        for (i = 0; i < researchGroup.length; i++) {
            var obj = researchGroup[i];

            if (obj.pivot.role === 2) {
                $("#dynamicAddRemove").append('<tr><td><select id="selUser' + i + '" name="moreFields[' + i +
                    '][userid]"  style="width: 200px;">@foreach($users as $user)<option value="{{ $user->id }}" >{{ $user->fname_th }} {{ $user->lname_th }}</option>@endforeach</select></td><td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="mdi mdi-minus"></i></button></td></tr>'
                );
                document.getElementById("selUser" + i).value = obj.id;
                $("#selUser" + i).select2()

            }
            //document.getElementById("#dynamicAddRemove").value = "10";
        }
        $("#add-btn2").click(function() {
            ++i;
            $("#dynamicAddRemove").append(
                '<tr>' +
                '<td><select id="selUser' + i + '" name="moreFields[' + i + '][userid]" style="width: 200px;">' +
                '<option value="">Select User</option>' +
                '@foreach($users as $user)' +
                '@if($user->fname_th != "ผู้ดูแลระบบ" && $user->fname_th != "เจ้าหน้าที่")' +
                '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                '@endif' +
                '@endforeach' +
                '</select></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="mdi mdi-minus"></i></button></td>' +
                '</tr>'
            );
            $("#selUser" + i).select2();
        });

        // Add Post Doctoral Fields
        $("#add-btn3").click(function() {
            ++postdocIndex;
            $("#postdocAddRemove").append(
                '<tr>' +
                '<td><select id="selPostdoc' + postdocIndex + '" name="postdoctoral[' + postdocIndex + '][userid]" class="form-control select2" style="width: 200px;">' +
                '<option value="">Select Post Doctoral</option>' +
                '@foreach($users as $user)' +
                '@if($user->doctoral_degree == "Ph.D.")' + // เฉพาะผู้ใช้ที่มี doctoral_degree = "Ph.D."
                '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                '@endif' +
                '@endforeach' +
                '</select></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="fas fa-minus"></i></button></td>' +
                '</tr>'
            );
            $("#selPostdoc" + postdocIndex).select2();
        });

        // Add Visiting Fields
        $("#add-btn4").click(function() {
            ++visitingIndex;
            $("#visitingAddRemove").append(
                '<tr>' +
                '<td><input type="text" name="visiting[' + visitingIndex + '][prefix]" class="form-control" placeholder="คำนำหน้า"></td>' +
                '<td><input type="text" name="visiting[' + visitingIndex + '][fname]" class="form-control" placeholder="ชื่อ"></td>' +
                '<td><input type="text" name="visiting[' + visitingIndex + '][lname]" class="form-control" placeholder="นามสกุล"></td>' +
                '</tr>' +
                '<tr>' +
                '<td colspan="4">' +
                '<input type="text" name="visiting[' + visitingIndex + '][affiliation]" class="form-control" placeholder="สังกัด">' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="mdi mdi-minus"></i></button></td>' +
                '</td>' +
                '</tr>'
            );
        });

        // Add Student Fields
        $("#add-btn5").click(function() {
            ++studentIndex;
            $("#studentAddRemove").append(
                '<tr>' +
                '<td><select name="students[' + studentIndex + '][userid]" class="form-control select2" style="width: 200px;" id="selStudent' + studentIndex + '">' +
                '<option value="">Select Student</option>' +
                '@foreach($users as $user)' +
                '@if($user->academic_ranks_th == "" && $user->fname_th != "ผู้ดูแลระบบ" && $user->fname_th != "เจ้าหน้าที่")' + // เฉพาะผู้ใช้ที่ academic_ranks_th เป็นค่าว่าง
                '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                '@endif' +
                '@endforeach' +
                '</select></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="fas fa-minus"></i></button></td>' +
                '</tr>'
            );
            $("#selStudent" + studentIndex).select2();
        });

        // $(document).on('click', '.remove-tr', function() {
        //     $(this).parents('tr').remove();
        // });

        $(document).on('click', '.remove-tr', function() {
            var row = $(this).closest('tr');
            var table = row.closest('table');

            if (table.attr('id') === 'visitingAddRemove') {
                row.prev().remove();
            }

            row.remove();
        });

    });
</script>
@stop