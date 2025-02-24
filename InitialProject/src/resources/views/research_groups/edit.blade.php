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

            <!-- ฟอร์มหลัก -->
            <form action="{{ route('researchGroups.update', $researchGroup->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- =====================
                     1) ข้อมูลกลุ่มวิจัยพื้นฐาน
                ====================== -->

                <!-- 1.1) ชื่อกลุ่มวิจัย -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_name_th"
                            value="{{ $researchGroup->group_name_th }}"
                            class="form-control"
                            placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_name_en"
                            value="{{ $researchGroup->group_name_en }}"
                            class="form-control"
                            placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>

                <!-- 1.2) หัวข้อการวิจัยหลัก (main_research_th/en) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวข้อการวิจัยหลัก (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_main_research_th"
                            value="{{ $researchGroup->group_main_research_th }}"
                            class="form-control"
                            placeholder="หัวข้อการวิจัยหลัก (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวข้อการวิจัยหลัก (English)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="group_main_research_en"
                            value="{{ $researchGroup->group_main_research_en }}"
                            class="form-control"
                            placeholder="Main research topic (English)">
                    </div>
                </div>

                <!-- 1.3) คำอธิบายกลุ่มวิจัย (desc_th/en) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_desc_th"
                            class="form-control"
                            style="height:90px"
                        >{{ $researchGroup->group_desc_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_desc_en"
                            class="form-control"
                            style="height:90px"
                        >{{ $researchGroup->group_desc_en }}</textarea>
                    </div>
                </div>

                <!-- 1.4) รายละเอียดกลุ่มวิจัย (detail_th/en) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_detail_th"
                            class="form-control"
                            style="height:90px"
                        >{{ $researchGroup->group_detail_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea
                            name="group_detail_en"
                            class="form-control"
                            style="height:90px"
                        >{{ $researchGroup->group_detail_en }}</textarea>
                    </div>
                </div>

                <!-- อัปโหลดรูปภาพ -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>Image</b></p>
                    <div class="col-sm-8">
                        <input type="file" name="group_image" class="form-control">
                        @if($researchGroup->group_image)
                            <p class="mt-2">
                                <img
                                    src="{{ asset('img/' . $researchGroup->group_image) }}"
                                    alt="Group Image"
                                    width="100"
                                >
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Link (ถ้ามี) -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>Link (ถ้ามี)</b></p>
                    <div class="col-sm-8">
                        <input
                            name="link"
                            type="url"
                            value="{{ old('link', $researchGroup->link) }}"
                            class="form-control"
                            placeholder="https://example.com"
                        >
                        <small class="form-text text-muted">
                            ถ้าคุณใส่ link ระบบจะนำคุณไปยังเว็บไซต์ที่คุณระบุแทนการแสดงข้อมูลในหน้านี้.
                        </small>
                    </div>
                </div>

                <!-- =====================
                     2) หัวหน้ากลุ่มวิจัย (role=1)
                ====================== -->
                @php
                    // ดึงข้อมูล user ที่เป็นหัวหน้ากลุ่ม (pivot.role = 1)
                    $headUser = $researchGroup->user->firstWhere('pivot.role', 1);
                @endphp

                @if(auth()->user()->hasAnyRole(['admin','staff']))
                    <div class="form-group row">
                        <p class="col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                        <div class="col-sm-8">
                            <select id="head0" name="head" class="form-control">
                                @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    @if($headUser && $headUser->id == $user->id) selected @endif
                                >
                                    {{ $user->fname_th }} {{ $user->lname_th }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <!-- ถ้าไม่ใช่ admin/staff => แค่แสดงหัวหน้าแบบ read only -->
                    <div class="form-group row">
                        <p class="col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                        <div class="col-sm-8">
                            <input
                                type="hidden"
                                name="head"
                                value="{{ $headUser->id ?? auth()->id() }}"
                            >
                            <p class="form-control-plaintext">
                                {{ $headUser
                                    ? $headUser->fname_th . ' ' . $headUser->lname_th
                                    : ''
                                }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- =====================
                     3) สมาชิกกลุ่มวิจัย (role=2/3)
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

                <!-- ปุ่ม Submit / Back -->
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
        // select2 สำหรับหัวหน้ากลุ่ม
        $("#head0").select2();

        // ดึง user-pivot ของ researchGroup ปัจจุบัน
        var researchGroupUsers = @json($researchGroup->user);
        var i = 0; // ตัวนับสมาชิก

        // วนลูปสร้างแถวสมาชิกที่มีอยู่แล้ว (role=2 หรือ 3)
        for (var idx = 0; idx < researchGroupUsers.length; idx++) {
            var obj = researchGroupUsers[idx];
            if (obj.pivot.role == 2 || obj.pivot.role == 3) {
                appendMemberRow(
                    i,
                    obj.id,
                    String(obj.pivot.role),
                    String(obj.pivot.can_edit)
                );
                i++;
            }
        }

        // ปุ่ม + เพิ่มสมาชิก (new row)
        // ถ้าเป็น non-admin => can_edit=0
        $("#add-btn2").click(function() {
            appendMemberRow(i, "", "", "0");
            i++;
        });

        function appendMemberRow(index, userId, roleVal, canEditVal) {
            var rowHtml = "<tr>";

            // ----- เลือก User -----
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

            // ----- Role (2=Researcher, 3=Postdoc) -----
            rowHtml += "  <td>";
            rowHtml += "    <select name=\"moreFields[" + index + "][role]\" class=\"form-control role-select\" style=\"width:220px;\">";
            rowHtml += "      <option value=\"2\">Researcher</option>";
            rowHtml += "      <option value=\"3\">Postdoctoral Researcher</option>";
            rowHtml += "    </select>";
            rowHtml += "  </td>";

            // ----- can_edit -----
            if (isAdmin == "1") {
                // ถ้าเป็น admin => dropdown
                rowHtml += "  <td>";
                rowHtml += "    <select name=\"moreFields[" + index + "][can_edit]\" class=\"form-control\" style=\"width:120px;\">";
                rowHtml += "      <option value=\"1\">Can Edit</option>";
                rowHtml += "      <option value=\"0\">Can't Edit</option>";
                rowHtml += "    </select>";
                rowHtml += "  </td>";
            } else {
                // ถ้าไม่ใช่ admin => hidden + แสดงข้อความ
                rowHtml += "  <td>";
                rowHtml += "    <input type=\"hidden\" name=\"moreFields[" + index + "][can_edit]\" value=\"" + canEditVal + "\">";

                var numVal = parseInt(canEditVal, 10);
                if (numVal === 1) {
                    rowHtml += "    <small style='color:green;'>Can Edit</small>";
                } else {
                    rowHtml += "    <small style='color:gray;'>No Edit</small>";
                }
                rowHtml += "  </td>";
            }

            // ----- ปุ่มลบ -----
            rowHtml += "  <td>";
            rowHtml += "    <button type=\"button\" class=\"btn btn-danger btn-sm remove-tr\"><i class=\"mdi mdi-minus\"></i></button>";
            rowHtml += "  </td>";

            rowHtml += "</tr>";

            // แปะลงในตาราง
            $("#dynamicAddRemove").append(rowHtml);

            // init select2
            $("#selUser" + index).select2();

            // ถ้ามี userId => set
            if (userId) {
                $("#selUser" + index).val(userId).trigger("change");
            }
            // ถ้ามี role => set
            if (roleVal) {
                $("#dynamicAddRemove tr:last .role-select").val(roleVal);
            }
            // ถ้า admin => set can_edit
            if (isAdmin == "1" && canEditVal !== "") {
                $("#dynamicAddRemove tr:last [name=\"moreFields[" + index + "][can_edit]\"]").val(canEditVal);
            }

            checkUserType("#selUser" + index);
        }

        // ลบแถว
        $(document).on("click", ".remove-tr", function() {
            $(this).closest("tr").remove();
        });

        // student => role=2 (label Student, disable)
        // teacher => enable role select
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

        $(document).on("change", ".member-select", function() {
            checkUserType(this);
        });

        // Visiting Scholars
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
