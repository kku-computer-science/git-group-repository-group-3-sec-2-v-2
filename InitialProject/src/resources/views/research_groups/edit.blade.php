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
                    <label class="col-sm-3 col-form-label"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></label>
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
                        <textarea name="group_main_research_th" class="form-control" style="height:90px" placeholder="หัวข้อการวิจัยหลัก (ภาษาไทย)">{{ $researchGroup->group_main_research_th }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><b>หัวข้อการวิจัยหลัก (English)</b></label>
                    <div class="col-sm-8">
                        <textarea name="group_main_research_en" class="form-control" style="height:90px" placeholder="Main research topic (English)">{{ $researchGroup->group_main_research_en }}</textarea>
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
                        <input name="link" type="url" value="{{ old('link', $researchGroup->link) }}" class="form-control" placeholder="https://example.com">
                        <small class="form-text text-muted">
                            ถ้าคุณใส่ link ระบบจะนำคุณไปยังเว็บไซต์ที่คุณระบุ
                        </small>
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

                <!-- 4) นักวิจัยรับเชิญ (Visiting Scholars) -->
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label pt-4"><b>นักวิจัยรับเชิญ</b></label>
                    <div class="col-sm-8">
                        <div id="visitingContainer">
                            @if($researchGroup->visitingScholars->isNotEmpty())
                                @foreach($researchGroup->visitingScholars as $key => $scholar)
                                    @php
                                        $pivotRecord = $researchGroup->visitingScholars()->where('author_id', $scholar->id)->first();
                                        $pivotData = $pivotRecord ? $pivotRecord->pivot : (object)['role' => $scholar->pivot->role ?? 4, 'can_edit' => 0];
                                    @endphp
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
                                                        data-affiliation="{{ $author->belong_to }}"
                                                        data-doctoral_degree="{{ $author->doctoral_degree }}"
                                                        data-academic_ranks_en="{{ $author->academic_ranks_en }}"
                                                        data-academic_ranks_th="{{ $author->academic_ranks_th }}">
                                                        {{ $author->author_fname }} {{ $author->author_lname }} ({{ $author->belong_to }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- บรรทัด Role: เลือกประเภท -->
                                        <div class="form-group">
                                            <label>ประเภท</label>
                                            <select class="form-control" name="visiting[{{ $key }}][role]">
                                                <option value="4" {{ $pivotData->role == 4 ? 'selected' : '' }}>Visiting Scholar</option>
                                                <option value="3" {{ $pivotData->role == 3 ? 'selected' : '' }}>Postdoctoral</option>
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
                                        <!-- บรรทัดที่สี่: วุฒิการศึกษาและตำแหน่งทางวิชาการ -->
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label>วุฒิการศึกษาระดับปริญญาเอก</label>
                                                <select name="visiting[{{ $key }}][doctoral_degree]" class="form-control">
                                                    <option value="0" {{ $scholar->doctoral_degree == 0 ? 'selected' : '' }}>ไม่มี</option>
                                                    <option value="1" {{ $scholar->doctoral_degree == 1 ? 'selected' : '' }}>มี</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>ตำแหน่งทางวิชาการ (EN)</label>
                                                <select name="visiting[{{ $key }}][academic_ranks_en]" class="form-control academic-ranks-en">
                                                    <option value="">-</option>
                                                    <option value="Professor" {{ $scholar->academic_ranks_en == 'Professor' ? 'selected' : '' }}>Professor</option>
                                                    <option value="Associate Professor" {{ $scholar->academic_ranks_en == 'Associate Professor' ? 'selected' : '' }}>Associate Professor</option>
                                                    <option value="Assistant Professor" {{ $scholar->academic_ranks_en == 'Assistant Professor' ? 'selected' : '' }}>Assistant Professor</option>
                                                    <option value="Lecturer" {{ $scholar->academic_ranks_en == 'Lecturer' ? 'selected' : '' }}>Lecturer</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>ตำแหน่งทางวิชาการ (TH)</label>
                                                <select name="visiting[{{ $key }}][academic_ranks_th]" class="form-control academic-ranks-th">
                                                    <option value="">-</option>
                                                    <option value="ศาสตราจารย์" {{ $scholar->academic_ranks_th == 'ศาสตราจารย์' ? 'selected' : '' }}>ศาสตราจารย์</option>
                                                    <option value="รองศาสตราจารย์" {{ $scholar->academic_ranks_th == 'รองศาสตราจารย์' ? 'selected' : '' }}>รองศาสตราจารย์</option>
                                                    <option value="ผู้ช่วยศาสตราจารย์" {{ $scholar->academic_ranks_th == 'ผู้ช่วยศาสตราจารย์' ? 'selected' : '' }}>ผู้ช่วยศาสตราจารย์</option>
                                                    <option value="อาจารย์" {{ $scholar->academic_ranks_th == 'อาจารย์' ? 'selected' : '' }}>อาจารย์</option>
                                                </select>
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
            </form>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
    $(document).ready(function() {
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
            // บรรทัดแรก: รายชื่อ
            entryHtml += "<div class='form-group'>";
            entryHtml += "  <label>รายชื่อ</label>";
            entryHtml += "  <select class='visiting-author-select form-control' name='visiting[" + v + "][author_id]'>";
            entryHtml += "      <option value=''>-- เลือกจากรายชื่อ --</option>";
            entryHtml += "      <option value='manual'>เพิ่มด้วยตัวเอง</option>";
            @foreach($authors as $author)
                entryHtml += "      <option value='{{ $author->id }}' data-first_name='{{ $author->author_fname }}' data-last_name='{{ $author->author_lname }}' data-affiliation='{{ $author->belong_to }}' data-doctoral_degree='{{ $author->doctoral_degree }}' data-academic_ranks_en='{{ $author->academic_ranks_en }}' data-academic_ranks_th='{{ $author->academic_ranks_th }}'>{{ $author->author_fname }} {{ $author->author_lname }} ({{ $author->belong_to }})</option>";
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
            entryHtml += "    <select name='visiting[" + v + "][doctoral_degree]' class='form-control'>";
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
            $("#visitingContainer").find(".visiting-author-select").last().select2({
                placeholder: "-- เลือกจากรายชื่อ --",
                allowClear: true,
            });
            updateVisitingOptions();
            checkDoctoralForPostdoc();
        });

        $(document).on("change", ".visiting-author-select", function() {
            updateVisitingOptions();
            var selectedVal = $(this).val();
            var $entry = $(this).closest(".visiting-scholar-entry");
            if(selectedVal && selectedVal !== "manual"){
                var $selectedOption = $(this).find("option:selected");
                var firstName = $selectedOption.data("first_name");
                var lastName = $selectedOption.data("last_name");
                var affiliation = $selectedOption.data("affiliation");
                var doctoralDegree = $selectedOption.data("doctoral_degree");
                var academicRanksEn = $selectedOption.data("academic_ranks_en");
                var academicRanksTh = $selectedOption.data("academic_ranks_th");
                
                $entry.find(".visiting-first-name").val(firstName);
                $entry.find(".visiting-last-name").val(lastName);
                $entry.find(".visiting-affiliation").val(affiliation);
                $entry.find("select[name$='[doctoral_degree]']").val(doctoralDegree);
                $entry.find("select[name$='[academic_ranks_en]']").val(academicRanksEn);
                $entry.find("select[name$='[academic_ranks_th]']").val(academicRanksTh);
                
                // ตรวจสอบสิทธิ์การเลือก Postdoctoral
                checkDoctoralForPostdoc();
            } else {
                $entry.find(".visiting-first-name").val("");
                $entry.find(".visiting-last-name").val("");
                $entry.find(".visiting-affiliation").val("");
                $entry.find("select[name$='[doctoral_degree]']").val("0");
                $entry.find("select[name$='[academic_ranks_en]']").val("");
                $entry.find("select[name$='[academic_ranks_th]']").val("");
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
        
        // เมื่อมีการเปลี่ยนแปลงวุฒิปริญญาเอก
        $(document).on("change", "select[name$='[doctoral_degree]']", function() {
            checkDoctoralForPostdoc();
        });
        
        // ตรวจสอบเริ่มต้นหลังโหลดหน้า
        $(document).ready(function() {
            checkDoctoralForPostdoc();
        });

        // ฟังก์ชันสำหรับตรวจสอบและแสดงข้อมูล Postdoctoral
        function updatePostdoctoralDisplay() {
            $(".visiting-scholar-entry").each(function() {
                var $entry = $(this);
                var $roleSelect = $entry.find("select[name$='[role]']");
                var $authorSelect = $entry.find(".visiting-author-select");
                
                // ดึงข้อมูลจาก author ที่เลือก
                var selectedVal = $authorSelect.val();
                if(selectedVal && selectedVal !== "manual") {
                    var $selectedOption = $authorSelect.find("option:selected");
                    var firstName = $selectedOption.data("first_name");
                    var lastName = $selectedOption.data("last_name");
                    var affiliation = $selectedOption.data("affiliation");
                    var doctoralDegree = $selectedOption.data("doctoral_degree");
                    var academicRanksEn = $selectedOption.data("academic_ranks_en");
                    var academicRanksTh = $selectedOption.data("academic_ranks_th");
                    
                    // อัพเดทข้อมูลในฟอร์ม
                    $entry.find(".visiting-first-name").val(firstName);
                    $entry.find(".visiting-last-name").val(lastName);
                    $entry.find(".visiting-affiliation").val(affiliation);
                    $entry.find("select[name$='[doctoral_degree]']").val(doctoralDegree);
                    $entry.find("select[name$='[academic_ranks_en]']").val(academicRanksEn);
                    $entry.find("select[name$='[academic_ranks_th]']").val(academicRanksTh);
                }
            });
        }

        // เรียกใช้ฟังก์ชันเมื่อมีการเปลี่ยนแปลง role หรือ author
        $(document).on("change", "select[name$='[role]']", function() {
            var $entry = $(this).closest(".visiting-scholar-entry");
            var $authorSelect = $entry.find(".visiting-author-select");
            
            // ถ้าเปลี่ยนเป็น Postdoctoral ให้ตรวจสอบข้อมูลใหม่
            if($(this).val() == "3") {
                updatePostdoctoralDisplay();
            }
        });
        $(document).on("change", ".visiting-author-select", updatePostdoctoralDisplay);
        
        // เรียกใช้ฟังก์ชันครั้งแรกหลังโหลดหน้า
        updatePostdoctoralDisplay();
    });
</script>
@stop
