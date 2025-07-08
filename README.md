<a name="readme-top">

<br/>

<br />
<div align="center">
  <a href="https://https://github.com/Mikaelaa05">
    <img src="assets/img/Kessoku_Band_Logo_Clear.png" alt="Nyebe" width="130" height="100">
  </a>

  <h3 align="center">AD-Task-3</h3>
</div>

<div align="center">
  A full-stack PHP + PostgreSQL + MongoDB web application with Dockerized development and user authentication.
</div>

<br/>

[![wakatime](https://wakatime.com/badge/user/018ee98a-e312-42fb-ac75-7e01d98002aa/project/329812e0-14f1-4c63-bb76-11e38eb7aa64.svg)](https://wakatime.com/badge/user/018ee98a-e312-42fb-ac75-7e01d98002aa/project/329812e0-14f1-4c63-bb76-11e38eb7aa64)

---

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#overview">Overview</a>
      <ol>
        <li>
          <a href="#key-components">Key Components</a>
        </li>
        <li>
          <a href="#technology">Technology</a>
        </li>
        <li>
          <a href="#quick-start">Quick Start</a>
        </li>
      </ol>
    </li>
    <li>
      <a href="#rule,-practices-and-principles">Rules, Practices and Principles</a>
    </li>
    <li>
      <a href="#resources">Resources</a>
    </li>
  </ol>
</details>

---

## Overview

AD-Task-3 is a modern PHP web application template for rapid development with Docker, PostgreSQL, and MongoDB. It features a modular structure, user authentication (login, signup, logout), and a ready-to-use database seeding/migration system. The project is designed for portability—anyone can clone, set up, and run it on their own machine with minimal configuration.

**Key Features:**
- User registration, login, and logout with secure password hashing
- PostgreSQL and MongoDB integration (with Docker Compose)
- Database migration and seeding utilities
- Modular PHP structure (components, layouts, handlers, utils)
- Environment variable management via `.env`
- Ready for deployment or classroom demonstration

### Key Components

| Component             | Purpose                                                               | Technologies & Interactions                                                                 |
| --------------------- | --------------------------------------------------------------------- | ------------------------------------------------------------------------------------------- |
| **Auth Module**       | Handles user registration, login, logout, and session management.     | PHP sessions, PostgreSQL (users table), password hashing/verification.                      |
| **Database Checkers** | Verifies DB connectivity on load.                                     | PHP, PDO for PostgreSQL, MongoDB PHP extension.                                             |
| **Seeder/Migration**  | Automates DB schema creation and dummy data population.               | PHP CLI scripts, Composer scripts, SQL model files, static data arrays.                     |
| **Frontend UI**       | Responsive pages for login, signup, dashboard, and error handling.    | HTML, CSS, PHP includes (components/layouts), minimal JS.                                   |
| **Dockerized Stack**  | Ensures consistent environment for all users.                         | Docker Compose, Dockerfile, .env, Apache, PHP extensions.                                   |

---

### Technology

#### Language
![HTML](https://img.shields.io/badge/HTML-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

#### Framework/Library
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

#### Databases
![MongoDB](https://img.shields.io/badge/MongoDB-47A248?style=for-the-badge&logo=mongodb&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?style=for-the-badge&logo=postgresql&logoColor=white)

#### Deployment
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)

---

## Quick Start

### 1. Clone the repository
```sh
git clone https://github.com/Mikaelaa05/AD-Task-3.git
cd AD-Task-3
```

### 2. Copy the environment file
```sh
cp .env.example .env
# Or create .env manually using the provided keys
```

### 3. Install Composer dependencies
```sh
composer install
```

### 4. Start Docker containers
```sh
docker compose up
```

### 5. Run database migrations and seeders
```sh
composer postgresql:migrate
composer postgresql:seed
```

### 6. Access the app
- Visit [http://localhost:9000](http://localhost:9000) in your browser.

### 7. Default users
- See `staticData/dummies/users.staticData.php` for seeded users.
- Or register a new user via the Sign Up page.

---

## Rules, Practices and Principles

1. Always use `AD-` in the front of the Title of the Project for the Subject followed by your custom naming.
2. Do not rename `.php` files if they are pages; always use `index.php` as the filename.
3. Add `.component` to the `.php` files if they are components code; example: `footer.component.php`.
4. Add `.util` to the `.php` files if they are utility codes; example: `account.util.php`.
5. Place Files in their respective folders.
6. Different file naming Cases
   | Naming Case | Type of code         | Example                           |
   | ----------- | -------------------- | --------------------------------- |
   | Pascal      | Utility              | Account.util.php                  |
   | Camel       | Components and Pages | index.php or footer.component.php |
8. Renaming of Pages folder names are a must, and relates to what it is doing or data it holding.
9. Use proper label in your github commits: `feat`, `fix`, `refactor` and `docs`
10. File Structure to follow below.

```
AD-ProjectName
└─ assets
|   └─ css
|   |   └─ name.css
|   └─ img
|   |   └─ name.jpeg/.jpg/.webp/.png
|   └─ js
|       └─ name.js
└─ components
|   └─ name.component.php
|   └─ templates
|      └─ name.component.php
└─ handlers
|   └─ name.handler.php
└─ layout
|   └─ name.layout.php
└─ pages
|  └─ pageName
|     └─ assets
|     |  └─ css
|     |  |  └─ name.css
|     |  └─ img
|     |  |  └─ name.jpeg/.jpg/.webp/.png
|     |  └─ js
|     |     └─ name.js
|     └─ index.php
└─ staticData
|  └─ name.staticdata.php
└─ utils
|   └─ name.utils.php
└─ vendor
└─ .gitignore
└─ bootstrap.php
└─ composer.json
└─ composer.lock
└─ index.php
└─ readme.md
└─ router.php
```
> The following should be renamed: name.css, name.js, name.jpeg/.jpg/.webp/.png, name.component.php (but not the part of the `component.php`), Name.utils.php (but not the part of the `utils.php`)

---

## Resources

| Title                   | Purpose                                                               | Link                                                                       |
| ----------------------- | --------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| GitHub Copilot          | In-IDE code suggestions and boilerplate generation.                   | [https://github.com/features/copilot](https://github.com/features/copilot) |
| Docker Documentation    | Reference for Docker and Compose setup.                               | [https://docs.docker.com/](https://docs.docker.com/)                       |
| PHP Official Docs       | Reference for PHP language and extensions.                            | [https://www.php.net/manual/en/](https://www.php.net/manual/en/)           |
| PostgreSQL Docs         | Reference for PostgreSQL database.                                    | [https://www.postgresql.org/docs/](https://www.postgresql.org/docs/)       |
| MongoDB Docs            | Reference for MongoDB database.                                       | [https://www.mongodb.com/docs/](https://www.mongodb.com/docs/)             |
| System Documentation    | Internal docs from PHP, MongoDB, and PostgreSQL used in development.  | — (see `/docs` folder in repo)                                             |

---

**For any issues, see the `/docs` folder or open an issue on GitHub.**