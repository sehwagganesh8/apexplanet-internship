# ApexPlanet Internship Projects

Full Stack Web Development (PHP & MySQL) internship report projects by
**Balusu Sehwag Ganesh** (23981A05U8), ApexPlanet Software Pvt. Ltd.

This repository contains the code deliverables for all five tasks of the
internship program (20 May 2026 – 19 July 2026):

| Task | Directory | Deliverable |
|------|-----------|-------------|
| 1 | `task1-portfolio/` | Personal portfolio website (HTML/CSS/JS) |
| 2 | `task2-login-ui/` | Responsive login & registration UI (Bootstrap 5) |
| 3 | `task3-user-management/` | User management system (PHP + MySQL, CRUD + auth) |
| 4 | `task4-bookstore/` | Real-world full-stack app with admin panel |
| 5 | `task5-elearning/` | Capstone e-learning portal with deployment |

## Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5, AJAX, Chart.js
- **Backend:** PHP 8.x with sessions, form handling, file uploads
- **Database:** SQLite (development) / MySQL (production)
- **Tools:** PHP built-in dev server, Git, GitHub

## Running Locally

Install PHP 8.x (with `pdo_sqlite`, `mysqli`, `gd`, `mbstring` extensions)
and run from each task directory:

```bash
cd task3-user-management
php -S localhost:8080
# open http://localhost:8080
```

The database is created automatically on first run (`database.sqlite`).