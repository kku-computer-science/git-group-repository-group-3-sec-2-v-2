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

TC002 Researcher Group Navigation
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC003 View Research Group Details
    Wait Until Element Is Visible    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']    timeout=60s
    Click Element    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']
    Sleep    2s
    Wait Until Page Contains    AIDA    timeout=60s

TC004 Login Button
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

TC005 Login System
    Input Text    name=username    ngamnij@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC006 Research Groups
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC007 Edit Research Groups
    Wait Until Element Is Visible    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element                    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains        แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC008 Add Member
    Wait Until Element Is Visible    xpath=//button[@id='add-btn2']    timeout=30s
    Scroll Element Into View         xpath=//button[@id='add-btn2']
    Execute JavaScript               document.getElementById('add-btn2').click();
    Sleep    2s
    Wait Until Element Is Visible    xpath=//select[@id='selUser6']    timeout=10s

