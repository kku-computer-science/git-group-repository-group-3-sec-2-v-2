*** Settings ***
Library     SeleniumLibrary

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

TC002 Research Group Navigation
    [Documentation]    เลือก Research Group จาก Nav Bar
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC003 Search by Research Group
    [Documentation]    Search AGT Group
    Wait Until Element Is Visible    id=searchInput    timeout=30s
    Scroll Element Into View         id=searchInput
    Input Text                       id=searchInput    AGT
    Press Keys                       id=searchInput    ENTER
    Wait Until Page Contains          AGT Lab          timeout=30s

TC004 View Research Group Details
    [Documentation]    ดูรายละเอียด AGT Group
    Wait Until Element Is Visible    xpath=//h5[contains(@class,'group-name')]    timeout=30s
    Click Element    xpath=//h5[contains(@class,'group-name')]
    Sleep    2s
    Wait Until Page Contains    AGT Lab    timeout=30s

TC005 Researcher Navigation
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Researcher')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Researcher')]
    Wait Until Page Contains    Researcher    timeout=30s

TC006 Login
    Select Login Button

TC007 Login_HeadGroup
    Select Login_HeadGroup

TC008 Research Group
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=30s

