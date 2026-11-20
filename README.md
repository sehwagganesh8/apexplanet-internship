# ApexPlanet Full Stack Web Development Internship

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

Repository containing the five internship task deliverables by
**Balusu Sehwag Ganesh** (Roll No. **23981A05U8**) for
**ApexPlanet Software Pvt. Ltd.**

**Internship period:** 20 May 2026 – 19 July 2026

## Projects

| Task | Project | Key Features | Live Demo |
|------|---------|--------------|-----------|
| 1 | [Personal Portfolio](task1-portfolio/) | Responsive portfolio, projects, skills, and contact sections | [Open](https://apexplanet-internship-task1-portfol.vercel.app/) |
| 2 | [Login & Registration UI](task2-login-ui/) | Bootstrap 5 interface, password toggle, animations, and client-side validation | [Open](https://apexplanet-internship-task2-login-u.vercel.app/) |
| 3 | [User Management System](task3-user-management/) | Session authentication, registration, role support, CRUD operations, and dashboard | [Open](https://apexintern.gamer.free/task3-user-management/) |
| 4 | [Online Bookstore](task4-bookstore/) | Admin panel, book and order management, image uploads, statistics, and database-backed CRUD | [Open](https://apexintern.gamer.free/task4-bookstore/) |
| 5 | [E-Learning Portal](task5-elearning/) | OTP verification, courses, students, role-based access, analytics, and Chart.js dashboards | [Open](https://apexintern.gamer.free/task5-elearning/) |

## Repository Structure

```text
.
├── task1-portfolio/
├── task2-login-ui/
├── task3-user-management/
├── task4-bookstore/
├── task5-elearning/
└── README.md
```

## Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend:** PHP 8.x, PHP sessions, AJAX
- **Database:** SQLite for local development and MySQL for hosted deployments
- **Libraries:** Chart.js and Font Awesome
- **Development:** PHP built-in server, Git, and GitHub
- **Hosting:** Vercel and a PHP/MySQL web server

## Run a Project Locally

1. Install PHP 8.x with `pdo_sqlite`, `mysqli`, `gd`, and `mbstring` enabled.
2. Open a terminal in the repository.
3. Run the required task directory:

```powershell
cd task3-user-management
php -S localhost:8080
```

4. Open `http://localhost:8080`.

Database-backed projects create or initialize their local SQLite database on first use. Replace `task3-user-management` with another task directory to run that project.

## Key Progress

- Built responsive static and interactive frontend interfaces.
- Implemented PHP authentication and session-based access control.
- Developed CRUD operations for users, books, orders, and learning data.
- Integrated image uploads and server-side form handling.
- Added dashboards and visual analytics with Chart.js.
- Deployed task projects to Vercel and a production PHP/MySQL server.