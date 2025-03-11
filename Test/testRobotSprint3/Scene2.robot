*** Settings ***
Library     SeleniumLibrary

*** Variables ***
${BROWSER}      Chrome
${URL}          https://projectsoften.cpkkuhost.com

*** Keywords ***
Select Permission from DropDown
    [Documentation]    เลือก "View and Edit" จาก DropDown

    # รอให้ Drop Down ปรากฏ
    Wait Until Element Is Visible    xpath=//select[@name="moreFields[2][can_edit]"]    timeout=10s

    # เลือก "View and Edit" ตาม Label ที่เห็นบน UI
    Select From List By Label        xpath=//select[@name="moreFields[2][can_edit]"]    View and Edit

    Sleep    1s  # รอให้ระบบอัปเดต

*** Test Cases ***
TC001 Open Event Registration Page
    Open Browser    ${URL}    ${BROWSER}    options=add_argument("--force-device-scale-factor=0.9")
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

TC003 Login Member
    Input Text    name=username    wpaweena@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Sleep    2s
    Wait Until Page Contains    Dashboard    timeout=30s

TC004 Research Groups1
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC005 Logout2
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=60s
    Location Should Be    ${URL}/

TC006 Login Button
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

TC007 Login Admin
    Input Text    name=username    Admin@gmail.com
    Input Text    name=password    12345678
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC008 Research Groups
    Wait Until Page Contains Element    xpath=//span[@class='menu-title' and text()='Research Group']    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Wait Until Element Is Visible    xpath=//span[@class='menu-title' and text()='Research Group']    timeout=30s
    Click Element                    xpath=//span[@class='menu-title' and text()='Research Group']
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC009 Edit Research Group
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains        แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC010 Change Permission Button
    Select Permission from DropDown

TC011 Submit Button
    Select Permission from DropDown
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Wait Until Page Contains    Research group updated successfully    timeout=10s

TC012 Logout3
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=60s
    Location Should Be    ${URL}/

TC013 Login Button2
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

TC014 Login Member2
    Input Text    name=username    wpaweena@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC015 Research Groups
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript               window.scrollTo(0, document.body.scrollHeight);
    Scroll Element Into View         xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element                    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC016 Edit Research Groups2
    Wait Until Element Is Visible    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View        xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element                    xpath=//a[@href='https://projectsoften.cpkkuhost.com/researchGroups/22/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains        แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC017 Delete Member
    [Documentation]    กดปุ่มลบสมาชิก
    Wait Until Element Is Visible    xpath=//button[contains(@class, 'remove-visiting')]    timeout=60s
    Scroll Element Into View         xpath=//button[contains(@class, 'remove-visiting')]
    Click Element                    xpath=//button[contains(@class, 'remove-visiting')]
    Sleep    2s

TC018 Submit Button
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Wait Until Page Contains    Research group updated successfully    timeout=10s  

TC019 Logout3
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Wait Until Page Contains    Login    timeout=60s
    Location Should Be    ${URL}/

TC020 Researcher Group Navigation2
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC021 View Research Group Details2
    Wait Until Element Is Visible    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']    timeout=60s
    Click Element    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']
    Sleep    5s
    Wait Until Page Contains    AIDA    timeout=60s
    