*** Settings ***
Library     SeleniumLibrary
Library     Collections
Library     OperatingSystem
Library     RequestsLibrary

*** Variables ***
${BROWSER}          Chrome
${URL}              https://projectsoften.cpkkuhost.com
${OLD_IP}           None
${NEW_IP}           None
${WIFI_OLD}         YourOldWiFiName
${WIFI_NEW}         YourNewWiFiName
${WIFI_PASSWORD}    YourNewWiFiPassword

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

Select Login Admin
    [Documentation]    ทดสอบการเข้าสู่ระบบ System Admin
    Input Text    name=username    Admin@gmail.com
    Input Text    name=password    12345678
    Click Button    xpath=//button[@type='submit']
    Sleep    3s
    Wait Until Page Contains    Dashboard    timeout=30s

Get Current IP
    [Documentation]    ดึง IP ปัจจุบันจาก API
    ${response}=    GET    https://api64.ipify.org
    ${ip}=    Convert To String    ${response.content}
    [Return]    ${ip}

Change Wi-Fi (Windows)
    [Documentation]    ตัดการเชื่อมต่อ Wi-Fi และเชื่อมต่อใหม่ (Windows)
    Run    netsh wlan disconnect
    Sleep    5s
    Run    netsh wlan connect name=${WIFI_NEW}
    Sleep    10s

Verify IP Change
    [Documentation]    ตรวจสอบว่า IP เปลี่ยนไปแล้ว
    ${OLD_IP}=    Get Current IP
    Change Wi-Fi (Windows)   # เปลี่ยนเป็น Change Wi-Fi (Linux/macOS) ถ้าใช้ Linux/macOS
    ${NEW_IP}=    Get Current IP
    Should Not Be Equal    ${OLD_IP}    ${NEW_IP}    IP address did not change!

*** Test Cases ***
TC001 Open Event Registration Page
    [Documentation]    เปิดเว็บไซต์
    Open Website

TC002 Login Button
    Select Login Button

TC003 Login Fail
    FOR    ${i}    IN RANGE    10
        Select Login Fail
    END

TC004 Login Success
    Select Login Success

TC005 Open Registration Page
    Open Website
    ${handles}=    Get Window Handles
    Wait Until Keyword Succeeds    10s    1s    Switch Window    ${handles}[-1]
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์
    Switch Window    ${handles}[0]

TC006 Change IP And Retry Login
    [Documentation]    บังคับเปลี่ยน IP และลองล็อกอินใหม่
    ${OLD_IP}=    Get Current IP
    Change Wi-Fi (Windows)   # เปลี่ยนเป็น Change Wi-Fi (Linux/macOS) ถ้าใช้ Linux/macOS
    ${NEW_IP}=    Get Current IP
    Should Not Be Equal    ${OLD_IP}    ${NEW_IP}    IP address did not change!
    Select Login Success
