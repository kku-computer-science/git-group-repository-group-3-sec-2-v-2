*** Settings ***
Library    SeleniumLibrary
Library    Collections
Library    RequestsLibrary
Library    OperatingSystem

*** Variables ***
${BROWSER}        Chrome
${URL}            https://projectsoften.cpkkuhost.com
${WIFI_OLD}       kku-wifi
${WIFI_NEW}       eduroam

*** Keywords ***
Get Current IP
    [Documentation]    ดึง IP ปัจจุบันจาก API
    Create Session    my_session    https://api64.ipify.org
    ${response}=    GET On Session    my_session    /
    ${ip}=    Convert To String    ${response.text}
    RETURN    ${ip}

Disconnect Wi-Fi
    [Documentation]    บังคับตัด Wi-Fi โดยปิด Network Adapter
    Run    netsh wlan disconnect
    Sleep    5s
    Run    netsh interface set interface "Wi-Fi" disable
    Sleep    5s
    Run    netsh interface set interface "Wi-Fi" enable
    Sleep    10s

Remove KKU-WiFi
    [Documentation]    ลบ KKU-WiFi ออกจากระบบชั่วคราว
    Run    netsh wlan delete profile name="${WIFI_OLD}"

Connect Wi-Fi
    [Arguments]    ${SSID}
    [Documentation]    เชื่อมต่อ Wi-Fi ที่กำหนด
    Run    netsh wlan connect name="${SSID}"
    Sleep    10s

Wait Until Internet Is Available
    [Documentation]    ตรวจสอบว่าเชื่อมต่ออินเทอร์เน็ตได้หรือยัง
    Wait Until Keyword Succeeds    30s    5s    Run    ping -n 1 google.com

*** Test Cases ***
TC001 Open Event Registration Page
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC002 Change Wi-Fi and Check IP
    [Documentation]    เปลี่ยน Wi-Fi และตรวจสอบว่า IP เปลี่ยนหรือไม่

    ${old_ip}=    Get Current IP
    Log    IP เก่าของคุณคือ: ${old_ip}

    # 1. ตัดการเชื่อมต่อ Wi-Fi เดิม
    Disconnect Wi-Fi
    Remove KKU-WiFi

    # 2. เชื่อมต่อ eduroam
    Connect Wi-Fi    ${WIFI_NEW}
    Sleep    10s
    Wait Until Internet Is Available

    # ตรวจสอบ IP ใหม่
    ${new_ip}=    Get Current IP
    Log    IP ใหม่ของคุณคือ: ${new_ip}

    # ตรวจสอบว่า IP เปลี่ยนหรือไม่
    Should Not Be Equal    ${old_ip}    ${new_ip}    IP ไม่เปลี่ยน กรุณาตรวจสอบการเชื่อมต่อ

