*** Settings ***
Library     SeleniumLibrary
Library    Collections

*** Variables ***
${BROWSER}      Chrome
${URL}          https://projectsoften.cpkkuhost.com

*** Keywords ***
Select Login Button
    [Documentation]    ปุ่ม Login
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

Select Login Fail
    [Documentation]   เข้าสู่ระบบผิดพลาด
    Input Text    name=username    putklang_w@kku.ac.th
    Input Text    name=password    1234
    Click Button    xpath=//button[@type='submit']
    Sleep    2s
    Wait Until Page Contains    Login Failed: Your user ID or password is incorrect    timeout=30s

Select Login Success
    [Documentation]    เข้าสู่ระบบสำเร็จ
    Input Text    name=username    putklang_w@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Sleep    2s
    Wait Until Page Contains    Too many login attempts. Please try again    timeout=30s

Select Login_Admin
    [Documentation]    เข้าสู่ระบบ System Admin
    Input Text    name=username    Admin@gmail.com
    Input Text    name=password    12345678
    Click Button    xpath=//button[@type='submit']
    Sleep    3s
    Wait Until Page Contains    Dashboard    timeout=30s
    

*** Test Cases ***
TC001 Open Event Registration Page
    [Documentation]    เปิดเว็บไซต์
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    ${main_window}=    Get Window Handles
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC002 Login Button
    Select Login Button

TC003 Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail
    Select Login Fail

TC004 Login Success
    Select Login Success

TC005 Open Registration Page
    [Documentation]    เปิดเว็บไซต์
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    ${main_window}=    Get Window Handles
    Wait Until Keyword Succeeds    10s    1s    Switch Window    NEW
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์
    Switch Window    ${main_window}
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s

TC006 Login Button
    Select Login Button

TC007 Login Admin
    Select Login_Admin
