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

            <!-- ฟอร์มหลัก -->
            <form action="{{ route('researchGroups.update', $researchGroup->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- 1) ข้อมูลกลุ่มวิจัยพื้นฐาน -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_th" value="{{ $researchGroup->group_name_th }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>ชื่อกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <input name="group_name_en" value="{{ $researchGroup->group_name_en }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>

                <!-- หัวข้อการวิจัยหลัก -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวข้อการวิจัยหลัก (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <input name="group_main_research_th" value="{{ $researchGroup->group_main_research_th }}" class="form-control" placeholder="หัวข้อการวิจัยหลัก (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>หัวข้อการวิจัยหลัก (English)</b></p>
                    <div class="col-sm-8">
                        <input name="group_main_research_en" value="{{ $researchGroup->group_main_research_en }}" class="form-control" placeholder="Main research topic (English)">
                    </div>
                </div>

                <!-- คำอธิบายกลุ่มวิจัย -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_th" class="form-control" style="height:90px">{{ $researchGroup->group_desc_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>คำอธิบายกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_desc_en" class="form-control" style="height:90px">{{ $researchGroup->group_desc_en }}</textarea>
                    </div>
                </div>

                <!-- รายละเอียดกลุ่มวิจัย -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_th" class="form-control" style="height:90px">{{ $researchGroup->group_detail_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <p class="col-sm-3"><b>รายละเอียดกลุ่มวิจัย (English)</b></p>
                    <div class="col-sm-8">
                        <textarea name="group_detail_en" class="form-control" style="height:90px">{{ $researchGroup->group_detail_en }}</textarea>
                    </div>
                </div>

                <!-- อัปโหลดรูปภาพของกลุ่มวิจัย -->
                <div class="form-group row">
                    <p class="col-sm-3"><b>Image</b></p>
                    <div class="col-sm-8">
                        <input type="file" name="group_image" class="form-control">
                        @if($researchGroup->group_image)
                            <p class="mt-2">
                                <img src="{{ asset('img/' . $researchGroup->group_image) }}" alt="Group Image" width="100">
                            </p>
                        @endif
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

                <!-- 2) หัวหน้ากลุ่มวิจัย (role=1) -->
                @php
                    $headUser = $researchGroup->user->firstWhere('pivot.role', 1);
                @endphp

                @if(auth()->user()->hasAnyRole(['admin','staff']))
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

                <!-- 3) สมาชิกกลุ่มวิจัย (role=2/3) -->
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>สมาชิกกลุ่มวิจัย</b></p>
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

                <!-- 4) นักวิจัยรับเชิญ (Visiting Scholars) -->
                <div class="form-group row">
                    <p class="col-sm-3 pt-4"><b>นักวิจัยรับเชิญ</b></p>
                    <div class="col-sm-8">
                        <table class="table" id="dynamicAddRemoveVisiting">
                            <tr>
                                <th>
                                    <button type="button" name="add" id="add-btn-visiting" class="btn btn-success btn-sm">
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
        // Initialize select2 สำหรับหัวหน้ากลุ่ม
        $("#head0").select2();

        // สมาชิกกลุ่มวิจัย (role=2 หรือ 3)
        var researchGroupUsers = @json($researchGroup->user);
        var i = 0;
        for (var idx = 0; idx < researchGroupUsers.length; idx++) {
            var obj = researchGroupUsers[idx];
            if (obj.pivot.role == 2 || obj.pivot.role == 3) {
                appendMemberRow(i, obj.id, String(obj.pivot.role), String(obj.pivot.can_edit));
                i++;
            }
        }

        // ปุ่มเพิ่มสมาชิกกลุ่มวิจัย
        $("#add-btn2").click(function() {
            appendMemberRow(i, "", "", "0");
            i++;
        });

        // สร้างแถวสำหรับสมาชิกกลุ่ม (ไม่รวมช่อง Upload รูป)
        function appendMemberRow(index, userId, roleVal, canEditVal) {
            var rowHtml = "<tr>";
            rowHtml += "  <td>";
            rowHtml += "    <select id=\"selUser" + index + "\" name=\"moreFields[" + index + "][userid]\" class=\"member-select\" style=\"width:200px;\">";
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
            if (isAdmin == "1") {
                rowHtml += "  <td>";
                rowHtml += "    <select name=\"moreFields[" + index + "][can_edit]\" class=\"form-control\" style=\"width:120px;\">";
                rowHtml += "      <option value=\"1\">Can Edit</option>";
                rowHtml += "      <option value=\"0\">Can't Edit</option>";
                rowHtml += "    </select>";
                rowHtml += "  </td>";
            } else {
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
            if (isAdmin == "1" && canEditVal !== "") {
                $("#dynamicAddRemove tr:last [name=\"moreFields[" + index + "][can_edit]\"]").val(canEditVal);
            }
            updateMemberOptions();
            checkUserType("#selUser" + index);
        }

        $(document).on("click", ".remove-tr", function() {
            $(this).closest("tr").remove();
            updateMemberOptions();
        });

        function checkUserType(selector) {
            var $userSelect = $(selector);
            var userType = $userSelect.find(":selected").data("usertype");
            var $row = $userSelect.closest("tr");
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
            $(".member-select").each(function() {
                var value = $(this).val();
                if (value) {
                    selectedValues.push(value);
                }
            });
            $(".member-select").each(function() {
                var $this = $(this);
                $this.find("option").each(function() {
                    if (selectedValues.indexOf($(this).val()) !== -1 && $(this).val() !== $this.val()) {
                        $(this).prop("disabled", true);
                    } else {
                        $(this).prop("disabled", false);
                    }
                });
                $this.trigger('change.select2');
            });
        }

        $(document).on("change", ".member-select", function() {
            checkUserType(this);
            updateMemberOptions();
        });

        // ส่วน Visiting Scholars
        var v = 0;
        $("#add-btn-visiting").click(function() {
            v++;
            var htmlVisiting = "";
            htmlVisiting += "<tr>";
            // Dropdown ให้เลือกจากตาราง Autor (จากตัวแปร $authors) หรือเลือก 'เพิ่มด้วยตัวเอง'
            htmlVisiting += "  <td>";
            htmlVisiting += "    <select class='visiting-author-select form-control' name='visiting[" + v + "][author_id]'>";
            htmlVisiting += "      <option value=''>-- เลือกจากรายชื่อ --</option>";
            htmlVisiting += "      <option value='manual'>เพิ่มด้วยตัวเอง</option>";
            @foreach($authors as $author)
                htmlVisiting += "      <option value='{{ $author->id }}' data-first_name='{{ $author->author_fname }}' data-last_name='{{ $author->author_lname }}' data-affiliation='{{ $author->belong_to }}'>{{ $author->author_fname }} {{ $author->author_lname }} ({{ $author->belong_to }})</option>";
            @endforeach
            htmlVisiting += "    </select>";
            htmlVisiting += "  </td>";
            // ฟิลด์กรอก first_name
            htmlVisiting += "  <td><input type='text' name='visiting[" + v + "][first_name]' class='form-control visiting-first-name' placeholder='ชื่อ' /></td>";
            // ฟิลด์กรอก last_name
            htmlVisiting += "  <td><input type='text' name='visiting[" + v + "][last_name]' class='form-control visiting-last-name' placeholder='นามสกุล' /></td>";
            // ฟิลด์กรอก affiliation (สังกัด)
            htmlVisiting += "  <td><input type='text' name='visiting[" + v + "][affiliation]' class='form-control visiting-affiliation' placeholder='สังกัด' /></td>";
            // ช่องอัปโหลดรูปสำหรับ Visiting Scholar
            htmlVisiting += "  <td><input type='file' name='visiting[" + v + "][picture]' class='form-control' /></td>";
            // ปุ่มลบแถว
            htmlVisiting += "  <td><button type='button' class='btn btn-danger btn-sm remove-visiting'><i class='mdi mdi-minus'></i></button></td>";
            htmlVisiting += "</tr>";

            $("#dynamicAddRemoveVisiting").append(htmlVisiting);
        });

        $(document).on("change", ".visiting-author-select", function() {
            var selectedVal = $(this).val();
            var $row = $(this).closest("tr");
            if (selectedVal && selectedVal !== "manual") {
                var firstName = $(this).find("option:selected").data("first_name");
                var lastName = $(this).find("option:selected").data("last_name");
                var affiliation = $(this).find("option:selected").data("affiliation");
                $row.find(".visiting-first-name").val(firstName).prop("readonly", true);
                $row.find(".visiting-last-name").val(lastName).prop("readonly", true);
                $row.find(".visiting-affiliation").val(affiliation).prop("readonly", true);
            } else {
                $row.find(".visiting-first-name").val("").prop("readonly", false);
                $row.find(".visiting-last-name").val("").prop("readonly", false);
                $row.find(".visiting-affiliation").val("").prop("readonly", false);
            }
        });

        $(document).on("click", ".remove-visiting", function() {
            $(this).closest("tr").remove();
        });
    });
</script>
@stop
