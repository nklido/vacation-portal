# Vacation Portal

[![codecov](https://codecov.io/gh/nklido/vacation-portal/graph/badge.svg?token=075DXTPSF1)](https://codecov.io/gh/nklido/vacation-portal)

A backend-focused demo application for managing employee vacation requests, built with PHP 8.3, MariaDB 10.5, and a minimal Angular v18 frontend.

## Overview
This project simulates a simple leave management system:

- Employees submit vacation requests.
- Managers approve or reject them.
- All data is stored and accessed through the backend API.

## ⚙️ Backend Highlights

 - PHP 8.3 (no framework)
 - Object-oriented structure
 - Composer-managed libraries
 - REST-like API with clear routes and HTTP methods
 - JWT authentication & role-based authorization
 - Repository pattern for DB access
 - Custom request/response abstractions
 - Global error handling
 - Domain-driven validation
 - PHPUnit test coverage with Codecov
 - CI pipeline with GitHub Actions for testing and coverage reporting
## 🐳 Quick Start with Docker

Clone the repository and run:
```bash
docker compose up --build
```

The application will be available at:

- Backend: http://localhost:8000
- Frontend: http://localhost:4200


## 🔐 Default Accounts
| Role     | Email                                             | Password |
| -------- | ------------------------------------------------- |----------|
| Manager  | [manager@example.com](mailto:manager@example.com) | password |
| Employee | [john@example.com](mailto:john@example.com)       | password |
| Employee | [jane@example.com](mailto:jane@example.com)       | password |

Database is automatically seeded at container startup.

## 🧪 Running Tests
You can run tests with:

```bash
docker compose run backend composer test
```

Example output:
```
PHPUnit 10.5.48 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.23
Configuration: /app/phpunit.xml

................................................................. 65 / 68 ( 95%)
...                                                               68 / 68 (100%)

Time: 00:03.212, Memory: 10.00 MB

OK (68 tests, 167 assertions)
```