@extends('dashboards.users.layouts.user-dash-layout')
@section('content')
<div class="container">
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> พบปัญหาบางอย่างกับข้อมูลที่คุณกรอก<br><br>
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
<<<<<<< HEAD
            <form action="{{ route('researchGroups.update',$researchGroup->id) }}" method="POST" enctype="multipart/form-data">
=======

            <!-- ฟอร์มหลัก -->
            <form action="{{ route('researchGroups.update', $researchGroup->id) }}" method="POST" enctype="multipart/form-data">
>>>>>>> origin/main
                @csrf
                @method('PUT')

                <!-- 1) ข้อมูลกลุ่มวิจัยพื้นฐาน -->
                <div class="form-group row">
<<<<<<< HEAD
                    <p class="col-sm-3 "><b>URL</b></p>
                    <div class="col-sm-8">
                        <input name="group_url" class="form-control" placeholder="URL กลุ่มวิจัย" value="{{ $researchGroup->group_url }}">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3 "><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
=======
                    <label class="col-sm-3 col-form-label"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></label>
>>>>>>> origin/main
                    <div class="col-sm-8">
                        <input name="group_name_th" value="{{ $researchGroup->group_name_th }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>ชื่อกลุ่มวิจัย (English)</b></label>
                    <div class="col-sm-8">
                        <input name="group_name_en" value="{{ $researchGroup->group_name_en }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>

                <!-- หัวข้อการวิจัยหลัก -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>หัวข้อการวิจัยหลัก (ภาษาไทย)</b></label>
                    <div class="col-sm-8">
                        <input name="group_main_research_th" value="{{ $researchGroup->group_main_research_th }}" class="form-control" placeholder="หัวข้อการวิจัยหลัก (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>หัวข้อการวิจัยหลัก (English)</b></label>
                    <div class="col-sm-8">
                        <input name="group_main_research_en" value="{{ $researchGroup->group_main_research_en }}" class="form-control" placeholder="Main research topic (English)">
                    </div>
                </div>

                <!-- คำอธิบายกลุ่มวิจัย -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_desc_th" class="form-control" style="height:90px">{{ $researchGroup->group_desc_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>คำอธิบายกลุ่มวิจัย (English)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_desc_en" class="form-control" style="height:90px">{{ $researchGroup->group_desc_en }}</textarea>
                    </div>
                </div>

                <!-- รายละเอียดกลุ่มวิจัย -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_detail_th" class="form-control" style="height:90px">{{ $researchGroup->group_detail_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>รายละเอียดกลุ่มวิจัย (English)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_detail_en" class="form-control" style="height:90px">{{ $researchGroup->group_detail_en }}</textarea>
                    </div>
                </div>

                <!-- อัปโหลดรูปภาพของกลุ่มวิจัย -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>Image</b></label>
                    <div class="col-sm-8">
                        <input type="file" name="group_image" class="form-control" accept="image/*">
                        @if($researchGroup->group_image)
                            <div class="mt-2">
                                <img src="{{ asset('img/' . $researchGroup->group_image) }}" alt="Group Image" width="100">
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Link -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>Link (ถ้ามี)</b></label>
                    <div class="col-sm-8">
<<<<<<< HEAD
                        <input type="file" name="group_image" class="form-control">
=======
                        <input name="link" type="url" value="{{ old('link', $researchGroup->link) }}" class="form-control" placeholder="https://example.com">
                        <small class="form-text text-muted">
                            ถ้าคุณใส่ link ระบบจะนำคุณไปยังเว็บไซต์ที่คุณระบุ
                        </small>
>>>>>>> origin/main
                    </div>
                </div>

                <!-- 2) หัวหน้ากลุ่มวิจัย (role=1) -->
                @php
                    $headUser = $researchGroup->user->firstWhere('pivot.role', 1);
                @endphp
                @if(auth()->user()->hasAnyRole(['admin','staff']))
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><b>หัวหน้ากลุ่มวิจัย</b></label>
                        <div class="col-sm-8">
                            <select id="head0" name="head" class="form-control">
                                @foreach($users as $user)
                                    @if($user->hasRole('teacher'))
                                        <option value="{{ $user->id }}" @if($headUser && $headUser->id == $user->id) selected @endif>
                                            {{ $user->fname_th }} {{ $user->lname_th }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <!-- สำหรับ non-admin: แสดง select ที่ disabled พร้อม input ซ่อนเพื่อส่งค่า -->
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><b>หัวหน้ากลุ่มวิจัย</b></label>
                        <div class="col-sm-8">
                            <select id="head0" class="form-control" disabled>
                                @foreach($users as $user)
                                    @if($user->hasRole('teacher'))
                                        <option value="{{ $user->id }}" @if($headUser && $headUser->id == $user->id) selected @endif>
                                            {{ $user->fname_th }} {{ $user->lname_th }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" name="head" value="{{ $headUser->id ?? auth()->id() }}">
                        </div>
                    </div>
                @endif

                <!-- 3) สมาชิกกลุ่มวิจัย (role=2/3) -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label pt-4"><b>สมาชิกกลุ่มวิจัย</b></label>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemove">
                            <tr>
                                <th>
                                    <button type="button" name="add" id="add-btn2" class="btn btn-success btn-sm">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
<<<<<<< HEAD
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
=======

                <!-- 4) นักวิจัยรับเชิญ (Visiting Scholars) -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label pt-4"><b>นักวิจัยรับเชิญ</b></label>
                    <div class="col-sm-8">
                        <div id="visitingContainer">
                            @if($researchGroup->visitingScholars->isNotEmpty())
                                @foreach($researchGroup->visitingScholars as $key => $scholar)
                                    <div class="visiting-scholar-entry border p-3 mb-3">
                                        <!-- บรรทัดแรก: รายชื่อ -->
                                        <div class="form-group">
                                            <label>รายชื่อ</label>
                                            <select class="visiting-author-select form-control" name="visiting[{{ $key }}][author_id]">
                                                <option value="">-- เลือกจากรายชื่อ --</option>
                                                <option value="manual" @if(!$authors->contains('id', $scholar->id)) selected @endif>เพิ่มด้วยตัวเอง</option>
                                                @foreach($authors as $author)
                                                    <option value="{{ $author->id }}"
                                                        @if($scholar->id == $author->id) selected @endif
                                                        data-first_name="{{ $author->author_fname }}"
                                                        data-last_name="{{ $author->author_lname }}"
                                                        data-affiliation="{{ $author->belong_to }}">
                                                        {{ $author->author_fname }} {{ $author->author_lname }} ({{ $author->belong_to }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- บรรทัดที่สอง: ชื่อ และ นามสกุล -->
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>ชื่อ</label>
                                                <input type="text" name="visiting[{{ $key }}][first_name]" class="form-control visiting-first-name" placeholder="ชื่อ" value="{{ $scholar->author_fname }}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>นามสกุล</label>
                                                <input type="text" name="visiting[{{ $key }}][last_name]" class="form-control visiting-last-name" placeholder="นามสกุล" value="{{ $scholar->author_lname }}">
                                            </div>
                                        </div>
                                        <!-- บรรทัดที่สาม: สังกัด และ อัปโหลดรูป -->
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>สังกัด</label>
                                                <input type="text" name="visiting[{{ $key }}][affiliation]" class="form-control visiting-affiliation" placeholder="สังกัด" value="{{ $scholar->belong_to }}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>อัปโหลดรูป</label>
                                                <input type="file" name="visiting[{{ $key }}][picture]" class="form-control" accept="image/*">
                                                @if($scholar->picture)
                                                    <div class="mt-2">
                                                        <img src="{{ asset('images/imag_user/' . $scholar->picture) }}" alt="Visiting Scholar Image" width="80">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-visiting">ลบ</button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" id="add-btn-visiting" class="btn btn-success btn-sm mt-2">
                            <i class="mdi mdi-plus"></i> เพิ่มนักวิจัยรับเชิญ
                        </button>
                    </div>
                </div>

                <!-- ปุ่ม Submit / Back -->
                <div class="form-group row mt-4">
                    <div class="col-sm-8 offset-sm-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a class="btn btn-light" href="{{ route('researchGroups.index') }}">Back</a>
                    </div>
                </div>
>>>>>>> origin/main
            </form>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
    $(document).ready(function() {
<<<<<<< HEAD
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
                $("#dynamicAddRemove").append(
                    '<tr><td><select id="selUser' + i + '" name="moreFields[' + i + '][userid]"  style="width: 200px;">@foreach($users as $user)<option value="{{ $user->id }}" >{{ $user->fname_th }} {{ $user->lname_th }}</option>@endforeach</select></td><td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="mdi mdi-minus"></i></button></td></tr>'
                );
                document.getElementById("selUser" + i).value = obj.id;
                $("#selUser" + i).select2();

            }
        }
        for (postdocIndex = 0; postdocIndex < researchGroup.length; postdocIndex++) {
            var obj = researchGroup[postdocIndex];
            if (obj.pivot.role === 3) {
                $("#postdocAddRemove").append(
                    '<tr>' +
                    '<td><select id="selPostdoc' + postdocIndex + '" name="postdoctoral[' + postdocIndex + '][userid]" class="form-control select2" style="width: 200px;">' +
                    '<option value="">Select Post Doctoral</option>' +
                    '@foreach($users as $user)' +
                    '@if($user->doctoral_degree == "Ph.D.")' +
                    '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                    '@endif' +
                    '@endforeach' +
                    '</select></td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="fas fa-minus"></i></button></td>' +
                    '</tr>'
                );
                document.getElementById("selPostdoc" + postdocIndex).value = obj.id;
                $("#selPostdoc" + postdocIndex).select2();
            }
        }
        for (studentIndex = 0; studentIndex < researchGroup.length; studentIndex++) {
            var obj = researchGroup[studentIndex];
            if (obj.pivot.role === 5) {
                $("#studentAddRemove").append(
                    '<tr>' +
                    '<td><select name="students[' + studentIndex + '][userid]" class="form-control select2" style="width: 200px;" id="selStudent' + studentIndex + '">' +
                    '<option value="">Select Student</option>' +
                    '@foreach($users as $user)' +
                    '@if($user->academic_ranks_th == null && $user->fname_th != "ผู้ดูแลระบบ" && $user->fname_th != "เจ้าหน้าที่")' +
                    '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                    '@endif' +
                    '@endforeach' +
                    '</select></td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="fas fa-minus"></i></button></td>' +
                    '</tr>'
                );
                document.getElementById("selStudent" + studentIndex).value = obj.id;
                $("#selStudent" + studentIndex).select2();

            }
        }

        // Add Member Fields
        $("#add-btn2").click(function() {
            ++i;
            $("#dynamicAddRemove").append(
                '<tr>' +
                '<td><select id="selUser' + i + '" name="moreFields[' + i + '][userid]" style="width: 200px;">' +
                '<option value="">Select User</option>' +
                '@foreach($users as $user)' +
                '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
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
                '<td><select id="selPostdoc' + postdocIndex + '" name="postdoctoral[' + postdocIndex + '][userid]" style="width: 200px;">' +
                '<option value="">Select Post Doctoral</option>' +
                '@foreach($users as $user)' +
                '@if($user->doctoral_degree == "Ph.D.")' +
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
                '<td><select name="students[' + studentIndex + '][userid]" style="width: 200px;" id="selStudent' + studentIndex + '">' +
                '<option value="">Select Student</option>' +
                '@foreach($users as $user)' +
                '@if($user->academic_ranks_th == null && $user->fname_th != "ผู้ดูแลระบบ" && $user->fname_th != "เจ้าหน้าที่")' +
                '<option value="{{ $user->id }}">{{ $user->fname_th }} {{ $user->lname_th }}</option>' +
                '@endif' +
                '@endforeach' +
                '</select></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-tr"><i class="fas fa-minus"></i></button></td>' +
                '</tr>'
            );
            $("#selStudent" + studentIndex).select2();
        });

        $(document).on('click', '.remove-tr', function() {
            var row = $(this).closest('tr');
            var table = row.closest('table');

            if (table.attr('id') === 'visitingAddRemove') {
                row.prev().remove();
            }

            row.remove();
        });

=======
        // Initialize select2 สำหรับหัวหน้ากลุ่ม
        $("#head0").select2();

        // Initialize select2 สำหรับ dropdown ของ Visiting Scholars ที่มีอยู่แล้ว
        $(".visiting-author-select").select2({
            placeholder: "-- เลือกจากรายชื่อ --",
            allowClear: true,
        });

        // ----------- สมาชิกกลุ่มวิจัย (Members) -----------
        var researchGroupUsers = @json($researchGroup->user);
        var i = 0;
        for (var idx = 0; idx < researchGroupUsers.length; idx++) {
            var obj = researchGroupUsers[idx];
            if (obj.pivot.role == 2 || obj.pivot.role == 3) {
                appendMemberRow(i, obj.id, String(obj.pivot.role), String(obj.pivot.can_edit));
                i++;
            }
        }

        $("#add-btn2").click(function() {
            appendMemberRow(i, "", "", "0");
            i++;
        });

        function appendMemberRow(index, userId, roleVal, canEditVal) {
            var rowHtml = "<tr>";
            rowHtml += "  <td>";
            rowHtml += "    <select id=\"selUser" + index + "\" name=\"moreFields[" + index + "][userid]\" class=\"member-select form-control\" style=\"width:200px;\">";
            rowHtml += "      <option value=\"\">Select User</option>";
            @foreach($users as $u)
                @if($u->hasAnyRole(['teacher','student']))
                    rowHtml += "      <option value=\"{{ $u->id }}\" data-usertype=\"{{ $u->hasRole('teacher') ? 'teacher' : 'student' }}\">{{ $u->fname_th }} {{ $u->lname_th }}</option>";
                @endif
            @endforeach
            rowHtml += "    </select>";
            rowHtml += "  </td>";
            rowHtml += "  <td>";
            rowHtml += "    <select name=\"moreFields[" + index + "][role]\" class=\"form-control role-select\" style=\"width:220px;\">";
            rowHtml += "      <option value=\"2\">Researcher</option>";
            rowHtml += "      <option value=\"3\">Postdoctoral Researcher</option>";
            rowHtml += "    </select>";
            rowHtml += "  </td>";
            if ("{{ auth()->user()->hasRole('admin') }}" == "1") {
                rowHtml += "  <td>";
                rowHtml += "    <select name=\"moreFields[" + index + "][can_edit]\" class=\"form-control\" style=\"width:120px;\">";
                rowHtml += "      <option value=\"0\" " + (canEditVal == "0" ? "selected" : "") + ">View</option>";
                rowHtml += "      <option value=\"1\" " + (canEditVal == "1" ? "selected" : "") + ">View and Edit</option>";
                rowHtml += "    </select>";
                rowHtml += "  </td>";
            } else {
                rowHtml += "  <td>";
                rowHtml += "    <input type=\"hidden\" name=\"moreFields[" + index + "][can_edit]\" value=\"" + canEditVal + "\">";
                var numVal = parseInt(canEditVal, 10);
                rowHtml += numVal === 1 ? "<small style='color:green;'>View and Edit</small>" : "<small style='color:gray;'>View</small>";
                rowHtml += "  </td>";
            }
            rowHtml += "  <td>";
            rowHtml += "    <button type=\"button\" class=\"btn btn-danger btn-sm remove-tr\"><i class=\"mdi mdi-minus\"></i></button>";
            rowHtml += "  </td>";
            rowHtml += "</tr>";

            $("#dynamicAddRemove").append(rowHtml);
            $("#selUser" + index).select2();
            if (userId) {
                $("#selUser" + index).val(userId).trigger("change");
            }
            if (roleVal) {
                $("#dynamicAddRemove tr:last .role-select").val(roleVal);
            }
            updateMemberOptions();
            if ($("#head0").is("select")) {
                updateHeadOptions();
            }
            checkUserType("#selUser" + index);
        }

        $(document).on("click", ".remove-tr", function() {
            $(this).closest("tr").remove();
            updateMemberOptions();
            if ($("#head0").is("select")) {
                updateHeadOptions();
            }
        });

        function checkUserType(selector) {
            var $userSelect = $(selector);
            var userType    = $userSelect.find(":selected").data("usertype");
            var $row        = $userSelect.closest("tr");
            var $roleSelect = $row.find(".role-select");
            if (userType === "student") {
                $roleSelect.val("2").trigger("change");
                $roleSelect.prop("disabled", true);
                $roleSelect.find("option[value='2']").text("Student");
                $roleSelect.find("option[value='3']").hide();
            } else {
                $roleSelect.prop("disabled", false);
                $roleSelect.find("option[value='2']").text("Researcher");
                $roleSelect.find("option[value='3']").show();
            }
        }

        function updateMemberOptions() {
            var selectedValues = [];
            var headValue = $("#head0").val();
            if (headValue) {
                selectedValues.push(headValue);
            }
            $(".member-select").each(function() {
                var value = $(this).val();
                if (value) {
                    selectedValues.push(value);
                }
            });
            $(".member-select").each(function() {
                var $this = $(this);
                $this.find("option").each(function() {
                    if (selectedValues.indexOf($(this).val()) !== -1 && $(this).val() !== $this.val()){
                        $(this).prop("disabled", true);
                    } else {
                        $(this).prop("disabled", false);
                    }
                });
                $this.trigger('change.select2');
            });
        }

        function updateHeadOptions() {
            if (!$("#head0").is("select")) return;
            var memberSelectedValues = [];
            $(".member-select").each(function() {
                var val = $(this).val();
                if(val) {
                    memberSelectedValues.push(val);
                }
            });
            $("#head0").find("option").each(function() {
                if(memberSelectedValues.indexOf($(this).val()) !== -1 && $(this).val() !== $("#head0").val()) {
                    $(this).prop("disabled", true);
                } else {
                    $(this).prop("disabled", false);
                }
            });
            $("#head0").trigger("change.select2");
        }

        $("#head0").on("change", function(){
            updateMemberOptions();
            if ($("#head0").is("select")) {
                updateHeadOptions();
            }
        });

        $(document).on("change", ".member-select", function() {
            checkUserType(this);
            updateMemberOptions();
            if ($("#head0").is("select")) {
                updateHeadOptions();
            }
        });

        // ----------- นักวิจัยรับเชิญ (Visiting Scholars) -----------
        var v = {{ $researchGroup->visitingScholars->count() }};
        $("#add-btn-visiting").click(function() {
            v++;
            var entryHtml = "";
            entryHtml += "<div class='visiting-scholar-entry border p-3 mb-3'>";
            entryHtml += "<div class='form-group'>";
            entryHtml += "  <label>รายชื่อ</label>";
            entryHtml += "  <select class='visiting-author-select form-control' name='visiting[" + v + "][author_id]'>";
            entryHtml += "      <option value=''>-- เลือกจากรายชื่อ --</option>";
            entryHtml += "      <option value='manual'>เพิ่มด้วยตัวเอง</option>";
            @foreach($authors as $author)
                entryHtml += "      <option value='{{ $author->id }}' data-first_name='{{ $author->author_fname }}' data-last_name='{{ $author->author_lname }}' data-affiliation='{{ $author->belong_to }}'>{{ $author->author_fname }} {{ $author->author_lname }} ({{ $author->belong_to }})</option>";
            @endforeach
            entryHtml += "  </select>";
            entryHtml += "</div>";
            entryHtml += "<div class='form-row'>";
            entryHtml += "  <div class='form-group col-md-6'>";
            entryHtml += "    <label>ชื่อ</label>";
            entryHtml += "    <input type='text' name='visiting[" + v + "][first_name]' class='form-control visiting-first-name' placeholder='ชื่อ'>";
            entryHtml += "  </div>";
            entryHtml += "  <div class='form-group col-md-6'>";
            entryHtml += "    <label>นามสกุล</label>";
            entryHtml += "    <input type='text' name='visiting[" + v + "][last_name]' class='form-control visiting-last-name' placeholder='นามสกุล'>";
            entryHtml += "  </div>";
            entryHtml += "</div>";
            entryHtml += "<div class='form-row'>";
            entryHtml += "  <div class='form-group col-md-6'>";
            entryHtml += "    <label>สังกัด</label>";
            entryHtml += "    <input type='text' name='visiting[" + v + "][affiliation]' class='form-control visiting-affiliation' placeholder='สังกัด'>";
            entryHtml += "  </div>";
            entryHtml += "  <div class='form-group col-md-6'>";
            entryHtml += "    <label>อัปโหลดรูป</label>";
            entryHtml += "    <input type='file' name='visiting[" + v + "][picture]' class='form-control' accept='image/*'>";
            entryHtml += "  </div>";
            entryHtml += "</div>";
            entryHtml += "<button type='button' class='btn btn-danger btn-sm remove-visiting'>ลบ</button>";
            entryHtml += "</div>";

            $("#visitingContainer").append(entryHtml);
            $("#visitingContainer").find(".visiting-author-select").last().select2({
                placeholder: "-- เลือกจากรายชื่อ --",
                allowClear: true,
            });
            updateVisitingOptions();
        });

        $(document).on("change", ".visiting-author-select", function() {
            updateVisitingOptions();
            var selectedVal = $(this).val();
            var $entry = $(this).closest(".visiting-scholar-entry");
            if(selectedVal && selectedVal !== "manual"){
                var firstName = $(this).find("option:selected").data("first_name");
                var lastName = $(this).find("option:selected").data("last_name");
                var affiliation = $(this).find("option:selected").data("affiliation");
                $entry.find(".visiting-first-name").val(firstName).prop("readonly", false);
                $entry.find(".visiting-last-name").val(lastName).prop("readonly", false);
                $entry.find(".visiting-affiliation").val(affiliation).prop("readonly", false);
            } else {
                $entry.find(".visiting-first-name").val("").prop("readonly", false);
                $entry.find(".visiting-last-name").val("").prop("readonly", false);
                $entry.find(".visiting-affiliation").val("").prop("readonly", false);
            }
        });

        $(document).on("click", ".remove-visiting", function() {
            $(this).closest(".visiting-scholar-entry").remove();
            updateVisitingOptions();
        });

        function updateVisitingOptions() {
            var selectedVisiting = [];
            $(".visiting-author-select").each(function() {
                var val = $(this).val();
                if(val && val !== "manual"){
                    selectedVisiting.push(val);
                }
            });
            $(".visiting-author-select").each(function() {
                var $this = $(this);
                $this.find("option").each(function() {
                    if(selectedVisiting.indexOf($(this).val()) !== -1 && $(this).val() !== $this.val()){
                        $(this).prop("disabled", true);
                    } else {
                        $(this).prop("disabled", false);
                    }
                });
            });
        }
>>>>>>> origin/main
    });
</script>
@stop
