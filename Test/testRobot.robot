*** Settings ***
Library     SeleniumLibrary

*** Variables ***
${BROWSER}      Chrome
${URL}          https://sesec2group3.cpkkuhost.com

*** Test Cases ***

TC001 Open Event Registration Page
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC002 Login Button
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

TC003 Login System
    Input Text    name=username    pusadee@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC004 Manage Publications
    Sleep    5s
    Wait Until Page Contains    Manage Publications    timeout=60s
    Scroll Element Into View    xpath=//a[@data-bs-toggle='collapse' and @aria-controls='ManagePublications']
    Click Element               xpath=//a[@data-bs-toggle='collapse' and @aria-controls='ManagePublications']
    Sleep    2s
    Wait Until Element Is Visible    xpath=//span[contains(text(),'Manage Publications')]    timeout=60s

TC005 Public Research Page
    [Documentation]    ตรวจสอบว่าหน้า Published Research เปิดขึ้นสำเร็จ
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Published research')]    timeout=60s
    Scroll Element Into View         xpath=//a[contains(text(),'Published research')]
    Wait Until Element Is Enabled    xpath=//a[contains(text(),'Published research')]    timeout=10s
    Click Element                    xpath=//a[contains(text(),'Published research')]
    Sleep    2s  # ป้องกันโหลดช้า
    Log To Console    *** Checking URL ***
    Wait Until Location Contains     /papers    timeout=60s
    Wait Until Page Contains         Published Research    timeout=60s

TC006 Call Paper
    [Documentation]    ตรวจสอบว่าหน้า Call for Papers เปิดขึ้นสำเร็จ
    Wait Until Element Is Visible    xpath=//a[contains(.,'Call Paper')]    timeout=60s
    Scroll Element Into View         xpath=//a[contains(.,'Call Paper')]
    Wait Until Element Is Enabled    xpath=//a[contains(.,'Call Paper')]    timeout=10s
    Click Element                    xpath=//a[contains(.,'Call Paper')]
    Sleep    2s  # ป้องกันโหลดช้า
    Log To Console    *** Checking URL ***
    Wait Until Location Contains     /callscopus    timeout=60s
    Wait Until Page Contains         Call for Papers    timeout=60s

TC007 Research Groups
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Element Is Visible    xpath=//h1[contains(text(),'Research Groups List')]    timeout=60s

TC008 Edit Research Groups
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-outline-success') and @title='Edit']
    Click Element    xpath=//a[contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains    Edit Research Group    timeout=60s

TC009 Add Post-Doctoral
    Wait Until Element Is Visible    id=add-btn-postdoc    timeout=60s
    Scroll Element Into View         id=add-btn-postdoc
    Click Button                     id=add-btn-postdoc
    Sleep    5s
    Wait Until Page Contains          Add Post-Doctoral    timeout=60s

TC010 Logout
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=30s
    Location Should Be    ${URL}/login

TC011 Open Event Registration Page
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC012 Researcher Group Navigation
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC013 Search by Research Group
    Wait Until Element Is Visible    id=searchInput    timeout=60s
    Scroll Element Into View         id=searchInput
    Input Text                       id=searchInput    AGT
    Press Keys                       id=searchInput    ENTER
    Wait Until Page Contains          AGT Lab          timeout=60s

TC014 Researcher Group Navigation
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC015 Search by Research Group
    Wait Until Element Is Visible    id=searchInput    timeout=60s
    Scroll Element Into View         id=searchInput
    Input Text                       id=searchInput    Pipat
    Press Keys                       id=searchInput    ENTER
    Wait Until Page Contains          Pipat          timeout=60s

TC016 View Research Group Details
    Wait Until Element Is Visible    xpath=//h5[contains(@class,'group-name')]    timeout=60s
    Click Element    xpath=//h5[contains(@class,'group-name')]
    Sleep    2s
    Wait Until Page Contains    AGT Lab    timeout=60s

TC017 Researchers Navigation
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Researchers')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Researchers')]
    Wait Until Page Contains    OUR RESEARCH    timeout=30s

TC018 View Researcher Profile
    [Documentation]    ตรวจสอบว่าหน้าโปรไฟล์ของ Punyaphol Horata โหลดสำเร็จ
    Wait Until Element Is Visible    xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]    timeout=60s
    Scroll Element Into View         xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]
    Wait Until Element Is Enabled    xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]    timeout=10s
    Click Element                    xpath=//a[.//span[contains(text(),'Punyaphol Horata')]]
    Sleep    2s
    Log To Console    *** Checking URL ***
    Wait Until Location Contains     /detail/    timeout=60s
    Wait Until Page Contains         Punyaphol Horata    timeout=60s

TC019 Search Research by Researcher
    Wait Until Element Is Visible    xpath=//input[@type='search' and contains(@class,'form-control')]    timeout=60s
    Scroll Element Into View         xpath=//input[@type='search' and contains(@class,'form-control')]
    Input Text                       xpath=//input[@type='search' and contains(@class,'form-control')]    Punyaphol
    Press Keys                       xpath=//input[@type='search' and contains(@class,'form-control')]    ENTER
    Wait Until Page Contains          Punyaphol    timeout=60s

TC020 Search Research by Title
    Wait Until Element Is Enabled    xpath=//input[@type='search' and contains(@class,'form-control')]    timeout=60s
    Scroll Element Into View         xpath=//input[@type='search' and contains(@class,'form-control')]
    Execute JavaScript               document.querySelector("input[type='search']").focus();
    Input Text                       xpath=//input[@type='search' and contains(@class,'form-control')]    Enhanced Local Receptive Fields based Extreme Learning Machine using Dominant Patterns Selection
    Press Keys                       xpath=//input[@type='search' and contains(@class,'form-control')]    ENTER
    Wait Until Page Contains          Enhanced Local Receptive    timeout=60s

TC021 View Research Details
    Wait Until Page Contains Element    xpath=//a[contains(text(),'Enhanced Local Receptive Fields based Extreme Learning Machine using Dominant Patterns Selection')]    timeout=60s
    Scroll Element Into View            xpath=//a[contains(text(),'Enhanced Local Receptive Fields based Extreme Learning Machine using Dominant Patterns Selection')]
    Wait Until Element Is Visible       xpath=//a[contains(text(),'Enhanced Local Receptive Fields based Extreme Learning Machine using Dominant Patterns Selection')]    timeout=30s
    Wait Until Element Is Enabled       xpath=//a[contains(text(),'Enhanced Local Receptive Fields based Extreme Learning Machine using Dominant Patterns Selection')]    timeout=30s
    Sleep                              2s
    Click Element                      xpath=//a[contains(text(),'Enhanced Local Receptive Fields based Extreme Learning Machine using Dominant Patterns Selection')]
    Page Should Contain                 Enhanced Local Receptive Fields based Extreme Learning Machine using Dominant Patterns Selection
   

[Teardown]    Close Browser