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

TC003 Search by Research Group
    Wait Until Element Is Visible    id=searchInput    timeout=60s
    Scroll Element Into View         id=searchInput
    Input Text                       id=searchInput    AIDA
    Press Keys                       id=searchInput    ENTER
    Wait Until Page Contains          AIDA Lab          timeout=60s

TC004 Login Button
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s