# Web-Based Visual Schedule, Task Management and Communication System

## Project Overview

The **Web-Based Visual Schedule, Task Management and Communication System** is a web-based application designed to help caregivers, teachers, and therapists organize schedules, manage tasks, monitor progress, and communicate with children through a visual and interactive interface.

The system aims to make daily activities easier to organize and follow by providing visual schedules, task tracking, child profiles, progress records, and a communication board.

## Features

- **User Authentication** – Allows users to register, log in, and securely manage their accounts.
- **Caregiver Dashboard** – Provides an overview of the system's available modules and activities.
- **Child Profile Management** – Allows users to create, view, update, and delete child profiles.
- **Visual Schedule Creator** – Helps users organize daily activities and schedules.
- **Task Management** – Allows users to create, assign, update, and monitor tasks.
- **Child View Mode** – Provides a simplified interface for children to view their activities.
- **Progress Tracking** – Records completed tasks and monitors progress.
- **Communication Board** – Uses visual communication cards and categories to support interaction.
- **Account Settings** – Allows users to update their profile information and preferences.

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend development and server-side processing |
| CSS | Website styling and layout |
| HTML | Webpage structure |
| JavaScript | Frontend interactions and dynamic functionality |
| MySQL / MariaDB | Database management |
| phpMyAdmin | Database administration |
| XAMPP | Local development server |
| GitHub | Source code management and collaboration |
| Java | Additional programming language used during project development |

## Development Tools

- Visual Studio Code
- XAMPP Control Panel
- phpMyAdmin
- GitHub

## Installation and Setup

Follow these steps to run the project locally.

### 1. Install XAMPP

Download and install XAMPP, which provides the Apache web server and MySQL/MariaDB database environment.

### 2. Clone or Download the Repository

Download the project repository and place the extracted project folder inside:

`C:\xampp\htdocs\`

### 3. Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### 4. Create the Database

1. Open your browser.
2. Visit `http://localhost/phpmyadmin/`.
3. Create a database named `visual_schedule_system`.
4. Import the provided database schema file from the project's `database/` directory.

### 5. Configure the Database Connection

Check the database configuration file:

`back_end/config/database.php`

Make sure the host, database name, username, and password match your local XAMPP configuration.

### 6. Run the Application

Open your browser and visit:

`http://localhost/W_B_C_S_S/front_end/index.php`

Register an account or log in to access the system.

## Project Structure

```text
W_B_C_S_S/
│
├── database/
│   └── schema.sql
│
├── front_end/
│   ├── css/
│   ├── js/
│   ├── index.php
│   ├── register.php
│   ├── dashboard.php
│   ├── children.php
│   ├── schedule.php
│   ├── task.php
│   ├── child_view.php
│   ├── progress.php
│   ├── communication.php
│   └── settings.php
│
├── back_end/
│   ├── auth/
│   ├── config/
│   ├── children/
│   ├── schedules/
│   ├── tasks/
│   ├── progress/
│   └── communication/
│
├── documentation/
│   ├── screenshots/
│   ├── user_manual/
│   └── technical_documentation/
│
└── README.md
```

*Note: The folder structure above represents the main project organization. Individual files and folders may change as development continues.*

## Project Team

Developed as an academic project by the project team.

**Project Title:** Web-Based Visual Schedule, Task Management and Communication System

**Development Environment:** Localhost using XAMPP

## Project Status

The system is currently under development and testing. Features are being integrated and evaluated to ensure proper functionality and compatibility across the different modules.

## License

This project was developed for educational and academic purposes.
