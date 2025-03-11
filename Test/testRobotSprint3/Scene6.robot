*** Settings ***
Library     SeleniumLibrary
Resource    Scene3.robot

*** Variables ***
${BROWSER}          Chrome
${URL}              https://projectsoften.cpkkuhost.com

*** Test Cases ***
TC001 Open Website
    [Documentation]    เปิดเว็บไซต์และตั้งค่าหน้าต่างเบราว์เซอร์
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s

TC002 Login Button
    [Documentation]    ปุ่ม Login
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

TC003 Login Success
    [Documentation]    ทดสอบการเข้าสู่ระบบสำเร็จ (ถ้า IP ยังไม่ถูกบล็อก)
    Input Text    name=username    putklang_w@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Sleep    2s
    Wait Until Page Contains    Dashboard    timeout=30s
