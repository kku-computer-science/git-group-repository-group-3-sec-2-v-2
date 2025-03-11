*** Settings ***
Library     SeleniumLibrary
Library     RequestsLibrary

*** Variables ***
${BROWSER}          Chrome
${BASE_URL}              https://projectsoften.cpkkuhost.com

*** Test Cases ***

Test SQL Injection Security
    ${response}    GET    ${BASE_URL}/sql
    Log    ${response.status_code}
    Log    ${response.text}
    Should Be Equal As Strings    ${response.status_code}    200
    Should Contain    ${response.text}    SQL Security Test Passed

Test XSS Security
    ${response}    GET    ${BASE_URL}/xss
    Log    ${response.status_code}
    Log    ${response.text}
    Should Be Equal As Strings    ${response.status_code}    200
    Should Contain    ${response.text}    XSS Security Test Passed

Test DDoS Security
    ${response}    GET    ${BASE_URL}/ddos
    Log    ${response.status_code}
    Log    ${response.text}
    Should Be Equal As Strings    ${response.status_code}    200
    Should Contain    ${response.text}    DDoS Security Test Passed

Test Brute Force Security
    ${response}    GET    ${BASE_URL}/brute    headers=${HEADERS}
    Log    ${response.status_code}
    Log    ${response.text}
    Should Be Equal As Strings    ${response.status_code}    200
    Should Contain    ${response.text}    Brute Force Test Passed

Test Failed Login Security
    ${response}    GET    ${BASE_URL}/failed-login
    Log    ${response.status_code}
    Log    ${response.text}
    Should Be Equal As Strings    ${response.status_code}    200
    Should Contain    ${response.text}    Failed Login Security Test Passed
