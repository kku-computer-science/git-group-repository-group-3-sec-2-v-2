*** Settings ***
Library     SeleniumLibrary
Resource    Scene3.robot

*** Variables ***
${BROWSER}          Chrome
${URL}              https://projectsoften.cpkkuhost.com

*** Keywords ***
Select Click Update Button
    # รอให้ปุ่ม Update ปรากฏ
    Wait Until Page Contains Element    xpath=//button[@type='submit' and contains(text(), 'Update')]    timeout=10s
    # คลิกปุ่ม Update
    Click Button    Update
    Sleep    3s

Select Click OK Button
    # รอให้ปุ่ม OK ปรากฏ
    Wait Until Page Contains Element    xpath=//button[contains(@class, 'swal-button--confirm')]    timeout=10s
    # คลิกปุ่ม OK
    Click Element    xpath=//button[contains(@class, 'swal-button--confirm')]
    Sleep    3s

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

TC003 Login Admin
    [Documentation]    ทดสอบการเข้าสู่ระบบ System Admin
    Input Text    name=username    ngamnij@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Sleep    3s
    Wait Until Page Contains    Dashboard    timeout=30s

TC004 Click User Profile
    [Documentation]    Click User Profile
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']
    Sleep    3s
    Wait Until Page Contains        User Profile    timeout=60s

TC005 Click Account Tab
    [Documentation]    View Profile Details
    Wait Until Element Is Visible    xpath=//a[@id='account-tab']    timeout=60s
    Scroll Element Into View        xpath=//a[@id='account-tab']
    Click Element                    xpath=//a[@id='account-tab']
    Sleep    3s
    Wait Until Page Contains        Account    timeout=60s

TC006 Blocked
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    Select Click Update Button
    Select Click OK Button
    
    