*** Settings ***
Library     SeleniumLibrary

*** Variables ***
${BROWSER}      Chrome
${URL}          https://sesec2group3.cpkkuhost.com

*** Test Cases ***

TC001 Open Event Registration Page
    [Documentation]    เปิดเว็บไซต์ https://sesec2group3.cpkkuhost.com และแสดงหน้า Home ของระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Wait Until Page Contains    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์    timeout=30s
    Page Should Contain    ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์

TC002 Researcher Group Navigation
    [Documentation]    เลือกเมนู Research Group จาก Nav Bar และแสดง Research Group ทั้งหมดของวิทยาลัยการคอมพิวเตอร์
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Research Group')]
    Wait Until Page Contains    Research Group    timeout=30s

TC003 View Research Group Details
    [Documentation]    เลือกเมนู AIDA GROUP และแสดงหน้าของข้อมูล AIDA Group
    Wait Until Element Is Visible    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']    timeout=60s
    Click Element    xpath=//div[@class='overlay']//h5[text()='Applied Intelligence and Data Analytics (AIDA)']
    Sleep    2s
    Wait Until Page Contains    AIDA    timeout=60s

TC004 Login Button
    [Documentation]    กดเข้าสู่ระบบที่ปุ่ม LOGIN บน Nav Bar และแสดงหน้าฟอร์มเข้าสู่ระบบ
    Wait Until Element Is Visible    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']    timeout=30s
    Scroll Element Into View    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Click Element    xpath=//a[contains(@class,'btn-solid-sm') and text()='Login']
    Sleep    2s
    Switch Window    NEW
    Wait Until Element Is Visible    name=username    timeout=30s

TC005 Login Head
    [Documentation]    เข้าสู่ระบบในฐานะ Head Group ด้วย Username: Ngamnij@kku.ac.th และ Password: 123456789 แล้วแสดงหน้า Dashboard
    Input Text    name=username    Ngamnij@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC006 Research Groups
    [Documentation]    เลือกเมนู Research Group จาก Side Bar และแสดงหน้าข้อมูล Research Group ทั้งหมดที่ รศ. ดร.งามนิจ อาจอินทร์ เป็นสมาชิก
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight)
    Scroll Element Into View    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC007 Edit Research Groups
    [Documentation]    เลือก Edit AIDA Group ที่ รศ.ดร.งามนิจ อาจอินทร์ เป็น Head Research Group และแสดงหน้าแก้ไขข้อมูล AIDA Group
    Wait Until Element Is Visible    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains    แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC008 Add Member
    [Documentation]    เพิ่มสมาชิกกลุ่มวิจัย "สิรภัทร" แล้วกดปุ่ม Submit และแสดง Status update ข้อมูลสำเร็จ
    Wait Until Element Is Visible    xpath=//button[@id='add-btn2']    timeout=30s
    Scroll Element Into View    xpath=//button[@id='add-btn2']
    Execute JavaScript    document.getElementById('add-btn2').click()
    Sleep    2s
    Wait Until Element Is Visible    xpath=//select[@id='selUser6']    timeout=10s
    Click Element    xpath=//span[@id='select2-selUser6-container']
    Sleep    1s
    Execute JavaScript    document.evaluate("//li[contains(@id, 'select2-selUser6-result') and contains(text(),'สิรภัทร')]", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.scrollIntoView({behavior: 'smooth', block: 'center'})
    Sleep    1s
    Click Element    xpath=//li[contains(@id, 'select2-selUser6-result') and contains(text(),'สิรภัทร')]
    Sleep    1s
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Wait Until Page Contains    Research group updated successfully    timeout=10s

TC009 Login Member
    [Documentation]    เข้าสู่ระบบในฐานะ Member ด้วย Username: Urachart@kku.ac.th และ Password: 123456789 แล้วแสดงหน้า Dashboard
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Switch Window    NEW
    Wait Until Page Contains    Login    timeout=30s
    Input Text    name=username    Urachart@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC010 Research Groups
    [Documentation]    เลือกเมนู Research Group จาก Side Bar และแสดงหน้าข้อมูล Research Group ทั้งหมดที่ รศ. ดร.อุรฉัตร โคแก้ว เป็นสมาชิก
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight)
    Scroll Element Into View    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC011 View AIDA Group
    [Documentation]    เลือก AIDA Group ที่ รศ. ดร.อุรฉัตร โคแก้ว เป็นสมาชิก และแสดงหน้าของข้อมูล AIDA Group
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Applied Intelligence and Data Analytics (AIDA)')]    timeout=60s
    Click Element    xpath=//a[contains(text(),'Applied Intelligence and Data Analytics (AIDA)')]
    Sleep    2s
    Wait Until Page Contains    AIDA    timeout=60s

TC012 Login Admin
    [Documentation]    เข้าสู่ระบบในฐานะ System - Admin ด้วย Username: Admin@gmail.com และ Password: 12345678 แล้วแสดงหน้า Dashboard
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Switch Window    NEW
    Wait Until Page Contains    Login    timeout=30s
    Input Text    name=username    Admin@gmail.com
    Input Text    name=password    12345678
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC013 Research Groups
    [Documentation]    เลือกเมนู Research Group จาก Side Bar และแสดงหน้าข้อมูล Research Group ทั้งหมดของวิทยาลัยการคอมพิวเตอร์
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight)
    Scroll Element Into View    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC014 Edit Research Group
    [Documentation]    เลือก Edit AIDA Group และแสดงหน้าแก้ไขข้อมูล AIDA Group
    Wait Until Element Is Visible    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains    แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC015 Change Permission
    [Documentation]    เพิ่มสิทธิ์ can Edit ให้ รศ.ดร.อุรฉัตร โคแก้ว และแสดง Status update ข้อมูลสำเร็จ
    Wait Until Element Is Visible    xpath=//select[@id='userPermission']    timeout=30s
    Select From List By Label    xpath=//select[@id='userPermission']    can Edit
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Wait Until Page Contains    Research group updated successfully    timeout=10s

TC016 Login Member Again
    [Documentation]    เข้าสู่ระบบในฐานะ Member อีกครั้ง ด้วย Username: Urachart@kku.ac.th และ Password: 123456789 แล้วแสดงหน้า Dashboard
    Wait Until Element Is Visible    xpath=//a[contains(text(),'Logout')]    timeout=30s
    Click Element    xpath=//a[contains(text(),'Logout')]
    Switch Window    NEW
    Wait Until Page Contains    Login    timeout=30s
    Input Text    name=username    Urachart@kku.ac.th
    Input Text    name=password    123456789
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    timeout=30s

TC017 Research Groups Again
    [Documentation]    เลือกเมนู Research Group จาก Side Bar และแสดงหน้าข้อมูล Research Group ทั้งหมดที่ รศ. ดร.อุรฉัตร โคแก้ว เป็นสมาชิก
    Wait Until Page Contains Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=90s
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight)
    Scroll Element Into View    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Wait Until Element Is Visible    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]    timeout=30s
    Click Element    xpath=//a[@class='nav-link']/span[contains(@class,'menu-title') and contains(text(),'Research Group')]
    Sleep    2s
    Wait Until Page Contains    Research Information Management System    timeout=60s

TC018 Edit Research Group as Member
    [Documentation]    เลือก Edit AIDA Group ที่ รศ. ดร.อุรฉัตร โคแก้ว เป็น Research Group Member และแสดงหน้าแก้ไขข้อมูล AIDA Group
    Wait Until Element Is Visible    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']    timeout=60s
    Scroll Element Into View    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Click Element    xpath=//a[@href='https://sesec2group3.cpkkuhost.com/researchGroups/10/edit' and contains(@class,'btn-outline-success') and @title='Edit']
    Sleep    2s
    Wait Until Page Contains    แก้ไขข้อมูลกลุ่มวิจัย    timeout=60s

TC019 Add Member Again
    [Documentation]    เพิ่มสมาชิกกลุ่มวิจัย แล้วกดปุ่ม Submit และแสดง Status update ข้อมูลสำเร็จ
    Wait Until Element Is Visible    xpath=//button[@id='add-btn2']    timeout=30s
    Scroll Element Into View    xpath=//button[@id='add-btn2']
    Execute JavaScript    document.getElementById('add-btn2').click()
    Sleep    2s
    Wait Until Element Is Visible    xpath=//select[@id='selUser6']    timeout=10s
    Click Element    xpath=//span[@id='select2-selUser6-container']
    Sleep    1s
    Execute JavaScript    document.evaluate("//li[contains(@id, 'select2-selUser6-result') and contains(text(),'สมาชิกใหม่')]", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.scrollIntoView({behavior: 'smooth', block: 'center'})
    Sleep    1s
    Click Element    xpath=//li[contains(@id, 'select2-selUser6-result') and contains(text(),'สมาชิกใหม่')]
    Sleep    1s
    Click Element    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Wait Until Page Contains    Research group updated successfully    timeout=10s