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
    Input Text    name=username    punhor1@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

Select Login_Staff
    [Documentation]    เข้าสู่ระบบ Staff
    Input Text    name=username    staff@gmail.com
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

Select Logout
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=60s
    Location Should Be    ${URL}/

*** Test Cases ***
TC001 Open Event Registration Page
    [Documentation]    Open Website
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC002 Research Group Navigation
    [Documentation]    Click Research Group on Nav Bar
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC003 View Research Group Details
    [Documentation]    View AGT Detail
    Wait Until Element Is Visible    xpath=//div[@class='overlay']//h5[text()='Advanced GIS Technology (AGT)']    timeout=60s
    Click Element    xpath=//div[@class='overlay']//h5[text()='Advanced GIS Technology (AGT)']
    Sleep    3s
    Wait Until Page Contains    AGT    timeout=60s

TC004 Researcher Navigation
    [Documentation]    Click Researcher on Nav Bar
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Researchers')]    timeout=30s
    Click Element    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchers' and contains(text(),'Researchers')]
    Wait Until Page Contains    Researchers    timeout=30s

TC005 View Researcher Profile
    [Documentation]    View Profile Punyaphol Horata
    Wait Until Element Is Visible    xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]    timeout=30s
    Scroll Element Into View         xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]
    Wait Until Element Is Enabled    xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]    timeout=10s
    Click Element                    xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]
    Sleep    3s
    Log To Console    *** Checking URL ***
    Wait Until Location Contains     /detail/    timeout=30s
    Wait Until Page Contains         Punyaphol Horata    timeout=30s

TC006 Login1
    Select Login Button

TC007 Login_HeadGroup
    Select Login_HeadGroup

TC008 Research Group
    [Documentation]    Click Research Group
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=30s
    
TC009 View Research Group
    [Documentation]    View Research Group Details
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]
    Sleep    3s
    Wait Until Page Contains        รายละเอียดกลุ่มวิจัย    timeout=60s

TC011 Click User Profile
    [Documentation]    Click User Profile
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']
    Sleep    3s
    Wait Until Page Contains        User Profile    timeout=60s

TC012 Click Account Tab
    [Documentation]    View Profile Details
    Wait Until Element Is Visible    xpath=//a[@id='account-tab']    timeout=60s
    Scroll Element Into View        xpath=//a[@id='account-tab']
    Click Element                    xpath=//a[@id='account-tab']
    Sleep    3s
    Wait Until Page Contains        Account    timeout=60s

TC014 Logout1
    Select Logout

TC015 Login2
    Select Login Button

TC016 Login_Staff
    Select Login_Staff

TC017 Research Group Staff
    [Documentation]    Click Research Group
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=30s

TC018 View Research Group Staff
    [Documentation]    View Research Group Details
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]
    Sleep    3s
    Wait Until Page Contains        รายละเอียดกลุ่มวิจัย    timeout=60s

TC019 Click User Profile Staff
    [Documentation]    Click User Profile
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/profile']
    Sleep    3s
    Wait Until Page Contains        User Profile    timeout=60s

TC020 Click Account Tab Staff
    [Documentation]    View Profile Details
    Wait Until Element Is Visible    xpath=//a[@id='account-tab']    timeout=60s
    Scroll Element Into View        xpath=//a[@id='account-tab']
    Click Element                    xpath=//a[@id='account-tab']
    Sleep    3s
    Wait Until Page Contains        Account    timeout=60s

TC021 Logout2
    Select Logout

TC022 Login3
    Select Login Button

TC023 Login_Admin
    Select Login_Admin

TC024 Research Group Admin
    [Documentation]    Click Research Group
    Wait Until Page Contains Element    xpath=//span[@class='menu-title' and text()='Research Group']    timeout=60s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Wait Until Element Is Visible    xpath=//span[@class='menu-title' and text()='Research Group']    timeout=30s
    Click Element                    xpath=//span[@class='menu-title' and text()='Research Group']
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC025 View Research Group Admin
    [Documentation]    View Research Group Details
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/3' and contains(@class,'btn-outline-primary')]
    Sleep    3s
    Wait Until Page Contains        รายละเอียดกลุ่มวิจัย    timeout=60s

TC026 Click Users Link
    [Documentation]    Click User
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/users']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/users']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/users']
    Sleep    3s
    Wait Until Page Contains        Users    timeout=60s

TC027 View Profile Admin
    [Documentation]    View Profile Details
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/users/1' and contains(@class, 'btn-outline-primary')]    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/users/1' and contains(@class, 'btn-outline-primary')]
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/users/1' and contains(@class, 'btn-outline-primary')]
    Sleep    3s
    Wait Until Page Contains        User Profile    timeout=60s


TC028 Logout2
    Select Logout
