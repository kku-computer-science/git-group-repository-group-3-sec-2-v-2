# Research Information Management System

A comprehensive research information management system for the Department of Computer Science.

## Overview

This system manages the research database for the Department of Computer Science. Key features include:

- **Login system** for faculty and staff
- Manage **research papers** and **academic articles**
- Manage **books** and **textbooks**
- Manage **patents** and other academic works
- Fetch research data from **Scopus API**
- Export data in **PDF** and **Excel** formats

---

## Installation

### Step-by-step guide:

1. **Clone the project:**
    ```bash
    git clone https://github.com/kku-computer-science/git-group-repository-group-3-sec-2-v-2.git
    ```

2. **Install dependencies:**
    ```bash
    composer install
    ```

3. **Copy the environment configuration file:**
    ```bash
    cp .env.example .env
    ```

4. **Generate the application key:**
    ```bash
    php artisan key:generate
    ```

5. **Configure the database:**
   - Open the `.env` file and set your database connection details.

6. **Run migrations:**
    ```bash
    php artisan migrate
    ```

7. **Optional: Use DevContainers for development:**
    - This project supports DevContainers for a consistent development environment.
    - Ensure you have Docker and Visual Studio Code installed.
    - Open the project in Visual Studio Code and select "Reopen in Container" to start working.

---

## Technologies Used

- **Backend Framework:** PHP/Laravel
- **Database:** MySQL
- **Frontend:** Bootstrap, jQuery
- **APIs:** Scopus API
- **Libraries:**
  - FPDF Library (for PDF generation)
  - PHPSpreadsheet (for Excel export)

---

## Main File Structure
```
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Exports/
│   ├── helpers.php
│   ├── Http/
│   ├── Imports/
│   ├── Models/
│   ├── Policies/
│   └── Providers/
├── bootstrap/
├── config/
├── database/
├── lib/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── .editorconfig
├── .env
├── .env.example
├── .gitattributes
├── .gitignore
├── .phpunit.result.cache
├── .styleci.yml
├── artisan
├── composer.json
├── composer.lock
├── docker-compose-odd.yml
├── laravel_v2.sql
├── laraveldb.sql
├── package.json
├── phpunit.xml
├── server.php
├── test2.sql
└── webpack.mix.js
```

---

## Usage

1. **Log in** to the system using your KKU-Mail credentials.
2. **Navigate** through the system menus to manage desired data.
3. Perform operations such as **Add**, **Edit**, or **Delete** based on your permissions.
4. Use the Scopus API to **fetch research data automatically**.
5. **Export reports** as PDF or Excel files for sharing and documentation.

---

## Developers

Developed by: **Department of Computer Science, College of Computing, Khon Kaen University**

---

## License

This project is licensed under the **MIT License**.

