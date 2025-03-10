*** Settings ***
Library     SeleniumLibrary

*** Variables ***
${BROWSER}      Chrome
${URL}          https://projectsoften.cpkkuhost.com

*** Keywords ***
Select User from DropDown1
    [Documentation]    เลือก "สิรภัทร" จาก DropDown
    Wait Until Element Is Visible    xpath=//span[@id='select2-selUser6-container']    timeout=10s
    Click Element                    xpath=//span[@id='select2-selUser6-container']
    Sleep    1s  # รอให้ Drop Down แสดงผล
    Click Element                    xpath=//li[contains(@id, 'select2-selUser6-result') and contains(text(),'สิรภัทร เชี่ยวชาญวัฒนา')]
    Sleep    1s

Enter First and Last Name
    [Documentation]    กรอกชื่อนามสกุล "Loan Thi-Thuy Ho" ลงในช่อง Input
    
    # รอให้ช่องกรอกชื่อและนามสกุลแสดงผล
    Wait Until Element Is Visible    xpath=//input[@name='visiting[1][first_name]']    timeout=10s
    Wait Until Element Is Visible    xpath=//input[@name='visiting[1][last_name]']    timeout=10s
    
    # กรอกชื่อ "Loan Thi-Thuy"
    Input Text    xpath=//input[@name='visiting[1][first_name]']    Loan Thi-Thuy
    
    # กรอกนามสกุล "Ho"
    Input Text    xpath=//input[@name='visiting[1][last_name]']    Ho

    Sleep    1s  # รอให้ระบบอัปเดตค่า


Select Permission from DropDown
    [Documentation]    เลือก "View and Edit" จาก DropDown

    # รอให้ Drop Down ปรากฏ
    Wait Until Element Is Visible    xpath=//select[@name="moreFields[1][can_edit]"]    timeout=10s

    # เลือก "View and Edit" ตาม Label ที่เห็นบน UI
    Select From List By Label        xpath=//select[@name="moreFields[1][can_edit]"]    View and Edit

    Sleep    1s  # รอให้ระบบอัปเดต



*** Test Cases ***

TC001 Open Event Registration Page
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC002 Researcher Group Navigation
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC003 View Research Group Details
    Wait Until Element Is Visible    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']    timeout=60s
    Click Element    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']
    Sleep    2s
    Wait Until Page Contains    AIDA    timeout=60s

TC004 Login Button
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

TC005 Login Head
    Input Text    name=username    Ngamnij@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC006 Research Groups
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC007 Edit Research Groups1
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains        แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC008 Add Member
    Wait Until Element Is Visible    xpath=//button[@id='add-btn2']    timeout=60s
    Scroll Element Into View         xpath=//button[@id='add-btn2']
    Execute JavaScript               document.getElementById('add-btn2').click();
    Sleep    2s
    Wait Until Element Is Visible    xpath=//span[@id='select2-selUser5-container']    timeout=60s

TC009 DropDown
    Select User from DropDown1

TC010 Add Success
    [Documentation]    ใช้ค่าเลือกจาก DropDown แล้วกด Submit
    Select User from DropDown1  # เรียก Keyword แทน
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Wait Until Page Contains    Research group updated successfully    timeout=10s

TC011 Logout1
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=60s

TC012 Login Member1
    Input Text    name=username    Urachart@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC013 Research Groups1
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC014 Logout2
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=60s
    Location Should Be    ${URL}/login

TC015 Login Admin
    Input Text    name=username    Admin@gmail.com
    Input Text    name=password    12345678
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC016 Research Groups
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC017 Edit Research Group
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains        แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC018 Change Permission Button
    Select Permission from DropDown

TC019 Submit Button
    Select Permission from DropDown
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Wait Until Page Contains    Research group updated successfully    timeout=10s

TC020 Logout3
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=60s
    Location Should Be    ${URL}/login

TC021 Login Member2
    Input Text    name=username    Urachart@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC022 Research Groups2
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC023 Edit Research Groups2
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains        แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC024 Add Member2
    [Documentation]    กดปุ่มเพิ่มนักวิจัยรับเชิญและรอให้กล่องปรากฏ
    # รอให้ปุ่ม "เพิ่มนักวิจัยรับเชิญ" ปรากฏและกด
    Wait Until Element Is Visible    xpath=//button[@id='add-btn-visiting']    timeout=60s
    Scroll Element Into View         xpath=//button[@id='add-btn-visiting']
    Click Element                    xpath=//button[@id='add-btn-visiting']
    Sleep    2s  # รอให้ UI อัปเดต
    # รอให้กล่องที่มีคลาส "visiting-scholar-entry" ปรากฏ
    Wait Until Element Is Visible    xpath=//div[contains(@class, 'visiting-scholar-entry')]    timeout=60s


TC025 Verify Input Fields Appear
    # ตรวจสอบว่า Input Fields สำหรับกรอกชื่อและนามสกุลแสดงผล
    Wait Until Element Is Visible    xpath=//input[@name='visiting[1][first_name]']    timeout=30s
    Wait Until Element Is Visible    xpath=//input[@name='visiting[1][last_name]']    timeout=30s

TC026 Enter Name
    [Documentation]    กรอกชื่อ "Loan Thi-Thuy" และนามสกุล "Ho"
    Enter First and Last Name

TC027 Add Success
    [Documentation]    ใช้ค่าที่กรอกจาก Input Fields แล้วกด Submit
    
    # ทำขั้นตอนจาก TC026 ก่อน
    Enter First and Last Name

    # กดปุ่ม Submit
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]

    # รอให้ข้อความ "Research group updated successfully" ปรากฏ
    Wait Until Page Contains    Research group updated successfully    timeout=30s


TC028 Open Event Registration Page2
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC029 Researcher Group Navigation2
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC030 View Research Group Details2
    Wait Until Element Is Visible    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']    timeout=60s
    Click Element    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']
    Sleep    5s
    Wait Until Page Contains    AIDA    timeout=60s