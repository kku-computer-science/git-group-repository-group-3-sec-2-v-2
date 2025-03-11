*** Settings ***
Library     SeleniumLibrary

*** Variables ***
${BROWSER}          Chrome
${URL}              https://projectsoften.cpkkuhost.com

*** Keywords ***
Open Website
    [Documentation]    เปิดเว็บไซต์และตั้งค่าหน้าต่างเบราว์เซอร์
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s

Select Login Button
    [Documentation]    คลิกปุ่ม Login และเปลี่ยนไปยังหน้าล็อกอิน
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    ${handles}=    Get Window Handles
    Switch Window    ${handles}[-1]
    Wait Until Element Is Visible    name=username    timeout=30s

Select Login Fail
    [Documentation]    ทดสอบการเข้าสู่ระบบผิดพลาด
    Input Text    name=username    putklang_w@kku.ac.th
    Input Text    name=password    1234
    Click Button    xpath=//button[@type='submit']
    Sleep    2s
    Wait Until Page Contains    Login Failed: Your user ID or password is incorrect    timeout=30s

Select Login Success
    [Documentation]    ทดสอบการเข้าสู่ระบบสำเร็จ (ถ้า IP ยังไม่ถูกบล็อก)
    Input Text    name=username    putklang_w@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Sleep    2s
    Wait Until Page Contains    Too many login attempts. Please try again    timeout=30s

*** Test Cases ***
TC001 Open Event Registration Page
    [Documentation]    เปิดเว็บไซต์
    Open Website

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
