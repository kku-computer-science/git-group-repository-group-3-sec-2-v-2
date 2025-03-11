*** Settings ***
Library     SeleniumLibrary
Resource    Scene3.robot

*** Variables ***
${BROWSER}          Chrome
${URL}              https://projectsoften.cpkkuhost.com
${IP_TO_UNBLOCK}      202.12.97.154

*** Keywords ***
Select Open Website
    [Documentation]    เปิดเว็บไซต์และตั้งค่าหน้าต่างเบราว์เซอร์
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s

Select Login Admin
    [Documentation]    ทดสอบการเข้าสู่ระบบ System Admin
    Input Text    name=username    Admin@gmail.com
    Input Text    name=password    12345678
    Click Button    xpath=//button[@type='submit']
    Sleep    3s
    Wait Until Page Contains    Dashboard    timeout=30s

Select Login Button
    [Documentation]    คลิกปุ่ม Login และเปลี่ยนไปยังหน้าล็อกอิน
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    ${handles}=    Get Window Handles
    Switch Window    ${handles}[-1]
    Wait Until Element Is Visible    name=username    timeout=30s

*** Test Cases ***
TC001 Open Website
    Select Open Website

TC002 Login
    Select Login Button

TC003 Login_Admin
    Select Login_Admin

TC004 Click Manage Blocked IPs Button
    Wait Until Page Contains Element    xpath=//a[contains(@href, '/admin/security/blocked-ips')]    timeout=10s
    Click Link    xpath=//a[contains(@href, '/admin/security/blocked-ips')]
    Sleep    3s

TC005 Unblock Specific IP
    # รอให้ปุ่ม Unblock ปรากฏ
    Wait Until Page Contains Element    xpath=//button[contains(@onclick, "unblockIP('${IP_TO_UNBLOCK}')")]    timeout=10s
    # คลิกปุ่ม Unblock
    Click Element    xpath=//button[contains(@onclick, "unblockIP('${IP_TO_UNBLOCK}')")]

    Sleep    3s  # รอให้ระบบอัปเดต