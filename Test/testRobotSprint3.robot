*** Settings ***
Library     SeleniumLibrary

*** Variables ***
${BROWSER}      Chrome
${URL}          https://projectsoften.cpkkuhost.com

*** Keywords ***
Select Login_HeadGroup
    [Documentation]    เข้าสู่ระบบ อ.วาสนา
    Input Text    name=username    putklang_w@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

Select Login_Admin
    [Documentation]    เข้าสู่ระบบ System Admin
    Input Text    name=username    Admin@gmail.com
    Input Text    name=password    12345678
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

Select Login_Researcher
    [Documentation]    เข้าสู่ระบบ อ.ปัญญาพล
    Input Text    name=username    punhor1@gmail.com
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s


*** Test Cases ***
TC001 Open Event Registration Page
    [Documentation]    เปิดเว็บไซต์
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์