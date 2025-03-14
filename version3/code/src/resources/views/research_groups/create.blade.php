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
            <h4 class="card-title">สร้างกลุ่มวิจัย</h4>
            <p class="card-description">กรอกข้อมูลและรายละเอียดกลุ่มวิจัย</p>

            <!-- ฟอร์มหลัก -->
            <form action="{{ route('researchGroups.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- 1) ข้อมูลกลุ่มวิจัยพื้นฐาน (8 ฟิลด์) -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></label>
                    <div class="col-sm-8">
                        <input name="group_name_th" value="{{ old('group_name_th') }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>ชื่อกลุ่มวิจัย (English)</b></label>
                    <div class="col-sm-8">
                        <input name="group_name_en" value="{{ old('group_name_en') }}" class="form-control" placeholder="ชื่อกลุ่มวิจัย (English)">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>หัวข้อการวิจัยหลัก (ภาษาไทย)</b></label>
                    <div class="col-sm-8">
                        <input name="group_main_research_th" value="{{ old('group_main_research_th') }}" class="form-control" placeholder="หัวข้อการวิจัยหลัก (ภาษาไทย)">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>หัวข้อการวิจัยหลัก (English)</b></label>
                    <div class="col-sm-8">
                        <input name="group_main_research_en" value="{{ old('group_main_research_en') }}" class="form-control" placeholder="Main research topic (English)">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_desc_th" class="form-control" style="height:90px" placeholder="คำอธิบายกลุ่มวิจัย (ภาษาไทย)">{{ old('group_desc_th') }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>คำอธิบายกลุ่มวิจัย (English)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_desc_en" class="form-control" style="height:90px" placeholder="คำอธิบายกลุ่มวิจัย (English)">{{ old('group_desc_en') }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_detail_th" class="form-control" style="height:90px" placeholder="รายละเอียดกลุ่มวิจัย (ภาษาไทย)">{{ old('group_detail_th') }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>รายละเอียดกลุ่มวิจัย (English)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_detail_en" class="form-control" style="height:90px" placeholder="รายละเอียดกลุ่มวิจัย (English)">{{ old('group_detail_en') }}</textarea>
                    </div>
                </div>

                <!-- อัปโหลดรูปภาพ -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>Image</b></label>
                    <div class="col-sm-8">
                        <input type="file" name="group_image" class="form-control" accept="image/*">
                    </div>
                </div>

                <!-- Link -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>Link (ถ้ามี)</b></label>
                    <div class="col-sm-8">
                        <input name="link" type="url" value="{{ old('link') }}" class="form-control" placeholder="https://example.com">
                        <small class="form-text text-muted">
                            หากคุณกรอก link ระบบจะพาคุณไปยังเว็บไซต์ที่ระบุแทนการแสดงข้อมูลในหน้านี้
                        </small>
                    </div>
                </div>

                <!-- 2) หัวหน้ากลุ่มวิจัย (role=1) -->
                @if(auth()->user()->hasAnyRole(['admin','staff']))
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><b>หัวหน้ากลุ่มวิจัย</b></label>
                        <div class="col-sm-8">
                            <select id="head0" name="head" class="form-control">
                                @foreach($users as $user)
                                    @if($user->hasRole('teacher'))
                                        <option value="{{ $user->id }}">
                                            {{ $user->fname_th }} {{ $user->lname_th }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="head" value="{{ auth()->id() }}">
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

                <!-- 4) นักวิจัยรับเชิญ (Visiting Scholars) -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label pt-4"><b>นักวิจัยรับเชิญ</b></label>
                    <div class="col-sm-8">
                        <div id="visitingContainer">
                            <!-- Entry สำหรับ Visiting Scholar จะถูกแทรกในที่นี่ -->
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
            </form>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
    var isAdmin = "{{ auth()->user()->hasRole('admin') ? 1 : 0 }}";

    $(document).ready(function() {
        // Initialize select2 สำหรับหัวหน้ากลุ่ม (head)
        $("#head0").select2();

        // เมื่อมีการเปลี่ยนแปลงใน Head ให้ update options ของทั้ง Head และ Member
        $("#head0").on("change", function() {
            updateMemberOptions();
            updateHeadOptions();
        });

        // ----------- สมาชิกกลุ่มวิจัย (Members) -----------
        var i = 0;
        $("#add-btn2").click(function() {
            appendMemberRow(i, "", "", "0");
            i++;
        });

        function appendMemberRow(index, userId, roleVal, canEditVal) {
            var rowHtml = "<tr>";
            // เลือก User
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
            // Role
            rowHtml += "  <td>";
            rowHtml += "    <select name=\"moreFields[" + index + "][role]\" class=\"form-control role-select\" style=\"width:220px;\">";
            rowHtml += "      <option value=\"2\">Researcher</option>";
            rowHtml += "      <option value=\"3\">Postdoctoral Researcher</option>";
            rowHtml += "    </select>";
            rowHtml += "  </td>";
            // can_edit
            if(isAdmin == "1") {
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
            // ปุ่มลบ
            rowHtml += "  <td>";
            rowHtml += "    <button type=\"button\" class=\"btn btn-danger btn-sm remove-tr\"><i class=\"mdi mdi-minus\"></i></button>";
            rowHtml += "  </td>";
            rowHtml += "</tr>";

            $("#dynamicAddRemove").append(rowHtml);
            $("#selUser" + index).select2();
            updateMemberOptions();
            updateHeadOptions();
        }

        $(document).on("click", ".remove-tr", function() {
            $(this).closest("tr").remove();
            updateMemberOptions();
            updateHeadOptions();
        });

        $(document).on("change", ".member-select", function() {
            var $userSelect = $(this);
            var userType = $userSelect.find(":selected").data("usertype");
            var $row = $userSelect.closest("tr");
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
            updateMemberOptions();
            updateHeadOptions();
        });

        // ฟังก์ชันอัปเดต option ของ member ให้ disable ค่าที่ถูกเลือกใน head ด้วย
        function updateMemberOptions() {
            var selectedValues = [];
            // รวมค่า head ที่เลือกอยู่
            var headVal = $("#head0").val();
            if(headVal) {
                selectedValues.push(headVal);
            }
            $(".member-select").each(function() {
                var value = $(this).val();
                if(value) {
                    selectedValues.push(value);
                }
            });
            $(".member-select").each(function() {
                var $this = $(this);
                $this.find("option").each(function() {
                    if(selectedValues.indexOf($(this).val()) !== -1 && $(this).val() !== $this.val()){
                        $(this).prop("disabled", true);
                    } else {
                        $(this).prop("disabled", false);
                    }
                });
                $this.trigger('change.select2');
            });
        }

        // ฟังก์ชันอัปเดต option ของ Head ให้ disable ค่าที่ถูกเลือกใน member ด้วย
        function updateHeadOptions() {
            var selectedMemberValues = [];
            $(".member-select").each(function() {
                var value = $(this).val();
                if(value) {
                    selectedMemberValues.push(value);
                }
            });
            var headSelect = $("#head0");
            headSelect.find("option").each(function() {
                if(selectedMemberValues.indexOf($(this).val()) !== -1 && $(this).val() !== headSelect.val()){
                    $(this).prop("disabled", true);
                } else {
                    $(this).prop("disabled", false);
                }
            });
            headSelect.trigger("change.select2");
        }

        // ----------- นักวิจัยรับเชิญ (Visiting Scholars) -----------
        var v = 0;
        $("#add-btn-visiting").click(function() {
            v++;
            var entryHtml = "";
            entryHtml += "<div class='visiting-scholar-entry border p-3 mb-3'>";
            // บรรทัดแรก: รายชื่อ
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
            
            // บรรทัด Role: เลือกประเภท
            entryHtml += "<div class='form-group'>";
            entryHtml += "  <label>ประเภท</label>";
            entryHtml += "  <select class='form-control' name='visiting[" + v + "][role]'>";
            entryHtml += "      <option value='4'>Visiting Scholar</option>";
            entryHtml += "      <option value='3'>Postdoctoral</option>";
            entryHtml += "  </select>";
            entryHtml += "</div>";
            
            // บรรทัดที่สอง: ชื่อและนามสกุล
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
            // บรรทัดที่สาม: สังกัดและอัปโหลดรูป
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
            
            // บรรทัดที่สี่: วุฒิการศึกษาและตำแหน่งทางวิชาการ
            entryHtml += "<div class='form-row'>";
            entryHtml += "  <div class='form-group col-md-4'>";
            entryHtml += "    <label>วุฒิการศึกษาระดับปริญญาเอก</label>";
            entryHtml += "    <select name='visiting[" + v + "][doctoral_degree]' class='form-control' onchange='checkDoctoralForPostdoc()'>";
            entryHtml += "      <option value='0'>ไม่มี</option>";
            entryHtml += "      <option value='1'>มี</option>";
            entryHtml += "    </select>";
            entryHtml += "  </div>";
            entryHtml += "  <div class='form-group col-md-4'>";
            entryHtml += "    <label>ตำแหน่งทางวิชาการ (EN)</label>";
            entryHtml += "    <select name='visiting[" + v + "][academic_ranks_en]' class='form-control academic-ranks-en'>";
            entryHtml += "      <option value=''>-</option>";
            entryHtml += "      <option value='Professor'>Professor</option>";
            entryHtml += "      <option value='Associate Professor'>Associate Professor</option>";
            entryHtml += "      <option value='Assistant Professor'>Assistant Professor</option>";
            entryHtml += "      <option value='Lecturer'>Lecturer</option>";
            entryHtml += "    </select>";
            entryHtml += "  </div>";
            entryHtml += "  <div class='form-group col-md-4'>";
            entryHtml += "    <label>ตำแหน่งทางวิชาการ (TH)</label>";
            entryHtml += "    <select name='visiting[" + v + "][academic_ranks_th]' class='form-control academic-ranks-th'>";
            entryHtml += "      <option value=''>-</option>";
            entryHtml += "      <option value='ศาสตราจารย์'>ศาสตราจารย์</option>";
            entryHtml += "      <option value='รองศาสตราจารย์'>รองศาสตราจารย์</option>";
            entryHtml += "      <option value='ผู้ช่วยศาสตราจารย์'>ผู้ช่วยศาสตราจารย์</option>";
            entryHtml += "      <option value='อาจารย์'>อาจารย์</option>";
            entryHtml += "    </select>";
            entryHtml += "  </div>";
            entryHtml += "</div>";
            
            entryHtml += "<button type='button' class='btn btn-danger btn-sm remove-visiting'>ลบ</button>";
            entryHtml += "</div>";

            $("#visitingContainer").append(entryHtml);
            
            // เรียกใช้ฟังก์ชันตรวจสอบวุฒิการศึกษาเพื่อควบคุมการเลือก Postdoctoral
            checkDoctoralForPostdoc();
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
            updateVisitingOptions();
        });

        $(document).on("click", ".remove-visiting", function() {
            $(this).closest(".visiting-scholar-entry").remove();
            updateVisitingOptions();
        });

        // การจับคู่ตำแหน่งทางวิชาการภาษาอังกฤษและภาษาไทย
        $(document).on("change", ".academic-ranks-en", function() {
            var value = $(this).val();
            var $thSelect = $(this).closest('.form-row').find('.academic-ranks-th');
            
            // Map English ranks to Thai ranks
            var rankMap = {
                "": "",
                "Professor": "ศาสตราจารย์",
                "Associate Professor": "รองศาสตราจารย์",
                "Assistant Professor": "ผู้ช่วยศาสตราจารย์",
                "Lecturer": "อาจารย์"
            };
            
            $thSelect.val(rankMap[value]);
        });

        $(document).on("change", ".academic-ranks-th", function() {
            var value = $(this).val();
            var $enSelect = $(this).closest('.form-row').find('.academic-ranks-en');
            
            // Map Thai ranks to English ranks
            var rankMap = {
                "": "",
                "ศาสตราจารย์": "Professor",
                "รองศาสตราจารย์": "Associate Professor",
                "ผู้ช่วยศาสตราจารย์": "Assistant Professor",
                "อาจารย์": "Lecturer"
            };
            
            $enSelect.val(rankMap[value]);
        });

        // ตรวจสอบวุฒิปริญญาเอกเพื่อกำหนดสิทธิ์การเลือก Postdoctoral
        function checkDoctoralForPostdoc() {
            $(".visiting-scholar-entry").each(function() {
                var $entry = $(this);
                var doctoralDegree = $entry.find("select[name$='[doctoral_degree]']").val();
                var $roleSelect = $entry.find("select[name$='[role]']");
                
                // หากไม่มีวุฒิปริญญาเอก (0 หรือ ว่าง) ให้ปิดการเลือก Postdoctoral
                if (doctoralDegree != "1") {
                    // ถ้าเลือก Postdoctoral อยู่ ให้เปลี่ยนเป็น Visiting Scholar
                    if ($roleSelect.val() == "3") {
                        $roleSelect.val("4");
                    }
                    // ปิดการใช้งานตัวเลือก Postdoctoral
                    $roleSelect.find("option[value='3']").prop("disabled", true);
                } else {
                    // เปิดการใช้งานตัวเลือก Postdoctoral
                    $roleSelect.find("option[value='3']").prop("disabled", false);
                }
            });
        }

        // เพิ่ม event listener สำหรับการเปลี่ยนแปลงวุฒิการศึกษา
        $(document).on("change", "select[name$='[doctoral_degree]']", function() {
            checkDoctoralForPostdoc();
        });

        function updateVisitingOptions() {
            var selectedValues = [];
            $(".visiting-author-select").each(function() {
                var value = $(this).val();
                if(value && value !== "manual"){
                    selectedValues.push(value);
                }
            });
            $(".visiting-author-select").each(function() {
                var $this = $(this);
                $this.find("option").each(function() {
                    if(selectedValues.indexOf($(this).val()) !== -1 && $(this).val() !== $this.val()){
                        $(this).prop("disabled", true);
                    } else {
                        $(this).prop("disabled", false);
                    }
                });
                $this.trigger('change.select2');
            });
        }
    });
</script>
@stop
