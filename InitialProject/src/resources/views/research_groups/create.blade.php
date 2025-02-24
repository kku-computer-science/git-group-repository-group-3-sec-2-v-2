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
            <h4 class="card-title">สร้างกลุ่มวิจัย</h4>
            <p class="card-description">กรอกข้อมูลและรายละเอียดกลุ่มวิจัย</p>

            <!-- ฟอร์มหลัก -->
            <form action="{{ route('researchGroups.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- =====================
                     1) ข้อมูลกลุ่มวิจัยพื้นฐาน (8 ฟิลด์)
                ====================== -->

                <!-- (1) group_name_th / group_name_en -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_name_th"
                            value="{{ old('group_name_th') }}"
                            class="form-control"
                            placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_name_en"
                            value="{{ old('group_name_en') }}"
                            class="form-control"
                            placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>

                <!-- (2) group_main_research_th / group_main_research_en -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวข้อการวิจัยหลัก (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_main_research_th"
                            value="{{ old('group_main_research_th') }}"
                            class="form-control"
                            placeholder="หัวข้อการวิจัยหลัก (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวข้อการวิจัยหลัก (English)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_main_research_en"
                            value="{{ old('group_main_research_en') }}"
                            class="form-control"
                            placeholder="Main research topic (English)">
                    </div>
                </div>

                <!-- (3) group_desc_th / group_desc_en -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_desc_th"
                            class="form-control"
                            style="height:90px"
                            placeholder="คำอธิบายกลุ่มวิจัย (ภาษาไทย)"
                        >{{ old('group_desc_th') }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_desc_en"
                            class="form-control"
                            style="height:90px"
                            placeholder="คำอธิบายกลุ่มวิจัย (English)"
                        >{{ old('group_desc_en') }}</textarea>
                    </div>
                </div>

                <!-- (4) group_detail_th / group_detail_en -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_detail_th"
                            class="form-control"
                            style="height:90px"
                            placeholder="รายละเอียดกลุ่มวิจัย (ภาษาไทย)"
                        >{{ old('group_detail_th') }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_detail_en"
                            class="form-control"
                            style="height:90px"
                            placeholder="รายละเอียดกลุ่มวิจัย (English)"
                        >{{ old('group_detail_en') }}</textarea>
                    </div>
                </div>

                <!-- อัปโหลดรูปภาพ -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>Image</b></p>
                    <div class="col-sm-8">
                        <input type="file" name="group_image" class="form-control">
                    </div>
                </div>

                <!-- Link -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>Link (ถ้ามี)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="link"
                            type="url"
                            value="{{ old('link') }}"
                            class="form-control"
                            placeholder="https://example.com"
                        >
                        <small class="form-text text-muted">
                            หากคุณกรอก link ระบบจะพาคุณไปยังเว็บไซต์ที่ระบุแทนการแสดงข้อมูลในหน้านี้
                        </small>
                    </div>
                </div>

                <!-- =====================
                     2) หัวหน้ากลุ่มวิจัย (role=1)
                ======================-->
                @if(auth()->user()->hasAnyRole(['admin','staff']))
                    <div class="form-group row">
                        <p class="col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                        <div class="col-sm-8">
                            <select id="head0" name="head" class="form-control">
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->fname_th }} {{ $user->lname_th }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <!-- ถ้าไม่ใช่ admin/staff => ซ่อนเป็น user ที่ล็อกอิน -->
                    <input
                        type="hidden"
                        name="head"
                        value="{{ auth()->id() }}"
                    >
                @endif

                <!-- =====================
                     3) สมาชิกกลุ่มวิจัย (role=2/3)
                     - Student => บังคับเป็น role=2 (label Student, disable)
                     - Teacher => เลือกได้ (2=Researcher, 3=Postdoc)
                     - Admin => dropdown can_edit, Non-admin => hidden=0
                ======================-->
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>สมาชิกกลุ่มวิจัย</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemove">
                            <tr>
                                <th>
                                    <button
                                        type="button"
                                        name="add"
                                        id="add-btn2"
                                        class="btn btn-success btn-sm"
                                    >
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- =====================
                     4) นักวิจัยรับเชิญ (Visiting Scholars)
                ======================-->
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>นักวิจัยรับเชิญ</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemoveVisiting">
                            <tr>
                                <th>
                                    <button
                                        type="button"
                                        name="add"
                                        id="add-btn-visiting"
                                        class="btn btn-success btn-sm"
                                    >
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
    var isAdmin = "{{ auth()->user()->hasRole('admin') ? 1 : 0 }}";

    $(document).ready(function() {
        // select2 สำหรับ head (admin/staff)
        $("#head0").select2();

        // --------------- Members ---------------
        var i = 0;
        $("#add-btn2").click(function() {
            // ค่าเริ่มต้น => ถ้าไม่ใช่ admin => can_edit=0
            appendMemberRow(i, "", "", "0");
            i++;
        });

        function appendMemberRow(index, userId, roleVal, canEditVal) {
            var rowHtml = "<tr>";

            // เลือก User
            rowHtml += "  <td>";
            rowHtml += "    <select id=\"selUser" + index + "\" name=\"moreFields[" + index + "][userid]\" class=\"member-select\" style=\"width:200px;\">";
            rowHtml += "      <option value=\"\">Select User</option>";
            @foreach($users as $u)
                @php
                    $dataUsertype = $u->hasRole("teacher") ? "teacher" : "student";
                @endphp
                rowHtml += "      <option value=\"{{ $u->id }}\" data-usertype=\"{{ $dataUsertype }}\">{{ $u->fname_th }} {{ $u->lname_th }}</option>";
            @endforeach
            rowHtml += "    </select>";
            rowHtml += "  </td>";

            // Role
            rowHtml += "  <td>";
            rowHtml += "    <select name=\"moreFields[" + index + "][role]\" class=\"form-control role-select\" style=\"width:220px;\">";
            rowHtml += "      <option value=\"2\">Researcher</option>";
            rowHtml += "      <option value=\"3\">Postdoctoral Researcher</option>";
            rowHtml += "    </select>";
            rowHtml += "  </td>";

            // can_edit
            if(isAdmin == "1") {
                // admin => dropdown
                rowHtml += "  <td>";
                rowHtml += "    <select name=\"moreFields[" + index + "][can_edit]\" class=\"form-control\" style=\"width:120px;\">";
                rowHtml += "      <option value=\"1\">Can Edit</option>";
                rowHtml += "      <option value=\"0\" selected>Can't Edit</option>";
                rowHtml += "    </select>";
                rowHtml += "  </td>";
            } else {
                // non-admin => hidden=0
                rowHtml += "  <td>";
                rowHtml += "    <input type=\"hidden\" name=\"moreFields[" + index + "][can_edit]\" value=\"" + canEditVal + "\">";
                var numVal = parseInt(canEditVal, 10);
                if(numVal === 1) {
                    rowHtml += "    <small style='color:green;'>Can Edit</small>";
                } else {
                    rowHtml += "    <small style='color:gray;'>No Edit</small>";
                }
                rowHtml += "  </td>";
            }

            // ปุ่มลบ
            rowHtml += "  <td>";
            rowHtml += "    <button type=\"button\" class=\"btn btn-danger btn-sm remove-tr\"><i class=\"mdi mdi-minus\"></i></button>";
            rowHtml += "  </td>";

            rowHtml += "</tr>";

            $("#dynamicAddRemove").append(rowHtml);

            $("#selUser" + index).select2();

            // ถ้ามี userId => set
            if(userId) {
                $("#selUser" + index).val(userId).trigger("change");
            }
            // ถ้ามี role => set
            if(roleVal) {
                $("#dynamicAddRemove tr:last .role-select").val(roleVal);
            }
            // ถ้า admin => set can_edit
            if(isAdmin == "1" && canEditVal !== "") {
                $("#dynamicAddRemove tr:last [name=\"moreFields[" + index + "][can_edit]\"]").val(canEditVal);
            }

            checkUserType("#selUser" + index);
        }

        // ลบแถว Member
        $(document).on("click", ".remove-tr", function() {
            $(this).closest("tr").remove();
        });

        // Student => role=2 (disable, label=Student)
        // Teacher => enable role select (2/3)
        function checkUserType(selector) {
            var $userSelect = $(selector);
            var userType    = $userSelect.find(":selected").data("usertype");
            var $row        = $userSelect.closest("tr");
            var $roleSelect = $row.find(".role-select");

            if(userType === "student") {
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

        $(document).on("change", ".member-select", function() {
            checkUserType(this);
        });

        // --------------- Visiting Scholars ---------------
        var v = 0;
        $("#add-btn-visiting").click(function() {
            v++;
            var htmlVisiting = "";
            htmlVisiting += "<tr>";
            htmlVisiting += "  <td><input type=\"text\" name=\"visiting[" + v + "][first_name]\" class=\"form-control\" placeholder=\"ชื่อ\" /></td>";
            htmlVisiting += "  <td><input type=\"text\" name=\"visiting[" + v + "][last_name]\" class=\"form-control\" placeholder=\"นามสกุล\" /></td>";
            htmlVisiting += "  <td><input type=\"text\" name=\"visiting[" + v + "][affiliation]\" class=\"form-control\" placeholder=\"สังกัด\" /></td>";
            htmlVisiting += "  <td><button type=\"button\" class=\"btn btn-danger btn-sm remove-visiting\"><i class=\"mdi mdi-minus\"></i></button></td>";
            htmlVisiting += "</tr>";

            $("#dynamicAddRemoveVisiting").append(htmlVisiting);
        });
        $(document).on("click", ".remove-visiting", function() {
            $(this).closest("tr").remove();
        });
    });
</script>
@stop
