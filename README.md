# Web-Based Visual Schedule, Task Management and Communication System

## Project Overview

The **Web-Based Visual Schedule, Task Management and Communication System** is a web application designed to help caregivers, therapists, and teachers organize daily activities, manage tasks, monitor progress, and improve communication with children through visual-based tools.

The system provides an organized platform where users can manage child profiles, create visual schedules, assign tasks, track task completion, and use a communication board with visual cards.

## Objectives

- Provide an organized system for managing children's daily schedules.
- Allow caregivers, therapists, and teachers to create and manage child profiles.
- Simplify task assignment and completion tracking.
- Monitor children's progress through progress records.
- Support communication through visual communication cards.
- Provide a child-friendly interface for viewing schedules and activities.

## Features

### 1. User Authentication
- User registration and login.
- Secure password hashing.
- Session-based authentication.
- Logout functionality.

### 2. Caregiver Dashboard
- Centralized dashboard for navigating system modules.
- Organized access to system features.
- Account settings and profile management.

### 3. Child Profiles
- Add child profiles.
- View registered child information.
- Update existing profiles.
- Delete child records.

### 4. Visual Schedule Creator
- Create schedules for individual children.
- Organize activities by time.
- Manage scheduled activities.
- View daily activity arrangements.

### 5. Task Management
- Create and assign tasks.
- Set task priorities and due dates.
- Update task information and status.
- Track task completion.

### 6. Child View Mode
- Child-friendly interface for viewing assigned activities and tasks.
- Simplified presentation of information.

### 7. Progress Tracking
- Record completed tasks.
- Monitor task completion.
- View progress summaries.

### 8. Communication Board
- Organize communication cards into categories.
- Display visual communication cards.
- Support communication through text associated with cards.

### 9. Account Settings
- Update profile information.
- Manage available account preferences.
- Configure Child View sound preferences.

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Server-side programming and backend functionality |
| MySQL / MariaDB | Database management |
| phpMyAdmin | Database administration and management |
| XAMPP | Local development environment and web server |
| HTML5 | Webpage structure |
| CSS3 | Styling and interface design |
| JavaScript | Client-side interactions and dynamic functionality |
| GitHub | Version control and project collaboration |

## System Requirements

- Windows operating system
- XAMPP
- PHP
- MySQL or MariaDB
- Web browser (Google Chrome, Microsoft Edge, or Firefox)
- GitHub account for accessing the project repository

## Installation and Setup

### Step 1: Install XAMPP

1. Download and install XAMPP.
2. Open the XAMPP Control Panel.
3. Start **Apache** and **MySQL**.

### Step 2: Download the Project

1. Clone or download this repository.
2. Extract the project folder if downloaded as a ZIP file.
3. Place the project folder inside the XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\W_B_C_S_S
```

### Step 3: Set Up the Database

1. Open your browser.
2. Navigate to:

```text
http://localhost/phpmyadmin
```

3. Create or import the project database.
4. Import the provided SQL schema file from the `database` folder.
5. Verify that the required tables have been created.

### Step 4: Configure the Database Connection

Open the database configuration file:

```text
back_end/config/database.php
```

Make sure the database connection settings match your local XAMPP configuration.

Example:

```php
$host = "localhost";
$dbname = "visual_schedule_system";
$username = "root";
$password = "";
```

### Step 5: Run the Application

Open your browser and navigate to:

```text
http://localhost/W_B_C_S_S/front_end/index.php
```

Register an account or log in using an existing account to access the system.

## Project Structure

```text
W_B_C_S_S/
│
├── back_end/
│   ├── auth/
│   ├── children/
│   ├── config/
│   ├── communication/
│   ├── progress/
│   ├── schedules/
│   └── tasks/
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
├── database/
│   └── schema.sql
│
├── documentation/
│   ├── screenshots/
│   ├── technical_documentation/
│   └── user_manual/
│
└── README.md
```

*Note: The folder structure may be updated as the project develops.*

## User Roles

The system supports the following account roles:

- **Caregiver** – Manages child profiles, schedules, tasks, and progress.
- **Therapist** – Uses the system to organize activities and monitor progress.
- **Teacher** – Uses the system to manage schedules, tasks, and child activities.

## Security

The system incorporates basic security practices, including:

- Password hashing using PHP password hashing functions.
- Prepared SQL statements for database operations.
- Session-based authentication.
- Access checks for protected pages.
- Input validation and output escaping.

## Project Collaboration

This project was developed collaboratively using GitHub.

Team members work on assigned modules through separate feature branches. Changes are submitted through Pull Requests and reviewed before being merged into the main branch.

## Documentation

Additional project documentation can be found in the `documentation` directory:

- **User Manual** – Instructions on how to use the system.
- **Technical Documentation** – Details about the system architecture, database, technologies, and implementation.
- **Screenshots** – Visual references of the application interface.

## Project Status

This project was developed as an academic project for educational purposes. Features and documentation may continue to be improved as development and testing progress.

## License

This project is intended for academic and educational use.
