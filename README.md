# Web-Based Visual Schedule, Task Management and Communication System

A web-based system designed to help caregivers, therapists, and teachers organize children's daily activities, manage tasks, monitor progress, and communicate through a visual communication board.

## Project Overview

The **Visual Schedule System** provides a centralized platform for managing children's daily routines and activities. It aims to make scheduling, task management, and progress monitoring more organized and accessible through a simple web-based interface.

The system allows authorized users to create child profiles, prepare visual schedules, assign tasks, track completion, and use communication cards to support interaction.

## Features

### 1. User Authentication
- User registration and login.
- Password hashing for account security.
- Session-based authentication.
- Logout functionality.
- Support for caregiver, therapist, and teacher roles.

### 2. Caregiver Dashboard
- Centralized dashboard for accessing system modules.
- Navigation to different system features.
- Account and system settings.

### 3. Child Profiles
- Create and manage child profiles.
- Store basic child information.
- View and update existing profiles.

### 4. Visual Schedule Creator
- Create and manage daily schedules.
- Organize activities by time.
- Associate schedules with child profiles.

### 5. Task Management
- Create and assign tasks.
- Set task priorities and due dates.
- Monitor task statuses.
- Record completed tasks.

### 6. Child View Mode
- Provides a simplified interface for children.
- Displays assigned activities and tasks.
- Supports a more accessible visual experience.

### 7. Progress Tracking
- Monitor completed and pending tasks.
- Record daily progress.
- View task completion summaries.

### 8. Communication Board
- Organize communication cards by category.
- Display picture-based communication options.
- Support text-to-speech functionality where implemented.

### 9. Account Settings
- Update profile information.
- Configure Child View sound preferences.

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend development |
| MySQL / MariaDB | Database management |
| PDO | Database connectivity |
| HTML5 | Page structure |
| CSS3 | Interface styling |
| JavaScript | Interactive functionality |
| Bootstrap | Responsive interface components, where used |
| XAMPP | Local development environment |
| GitHub | Version control and collaboration |

## Requirements

Before running the project, make sure you have:

- XAMPP installed.
- PHP 8 or a compatible version.
- MySQL or MariaDB.
- A modern web browser.
- A code editor such as Visual Studio Code.

## Installation and Setup

### Step 1: Clone or Download the Repository

Clone the repository:

```bash
git clone YOUR_REPOSITORY_URL
```

Alternatively, download the repository as a ZIP file and extract it into your XAMPP `htdocs` directory.

### Step 2: Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### Step 3: Create the Database

1. Open phpMyAdmin.
2. Create or select the database named:

```sql
visual_schedule_system
```

3. Import the project's database schema:

```text
database/schema.sql
```

### Step 4: Configure the Database Connection

Open:

```text
back_end/config/database.php
```

Check the database credentials and update them if necessary.

Example local configuration:

```php
$host = "localhost";
$dbname = "visual_schedule_system";
$username = "root";
$password = "";
```

### Step 5: Run the Project

Open your browser and visit:

```text
http://localhost/W_B_C_S_S/front_end/
```

Register an account and log in to access the system.

## Project Structure

```text
W_B_C_S_S/
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
├── database/
│   └── schema.sql
│
├── documentation/
│   ├── screenshots/
│   ├── user_manual/
│   └── technical_documentation/
│
└── README.md
```

## Database Tables

The system uses the following database tables:

| Table | Description |
|---|---|
| `users` | Stores registered user accounts and roles. |
| `children` | Stores child profile information. |
| `activities` | Stores available schedule activities. |
| `schedules` | Stores created schedules. |
| `schedule_items` | Stores individual activities within schedules. |
| `tasks` | Stores assigned tasks and their statuses. |
| `task_completions` | Records completed tasks. |
| `communication_categories` | Stores communication card categories. |
| `communication_cards` | Stores individual communication cards. |
| `progress_records` | Stores daily progress summaries. |

## User Roles

The system supports three account roles:

- **Caregiver** – Manages child profiles, schedules, tasks, and progress.
- **Therapist** – Uses the system to organize activities and monitor progress.
- **Teacher** – Uses the system to manage schedules, tasks, and child activities.

Actual access restrictions depend on the permissions implemented in the application.

## Testing

The system should be tested for:

- User registration and login.
- Session management and logout.
- Child profile management.
- Schedule creation and management.
- Task assignment and completion.
- Child View Mode functionality.
- Progress recording and summaries.
- Communication board functionality.
- Account settings and saved preferences.

## Security Considerations

The project uses several basic security practices:

- Password hashing through PHP's password hashing functions.
- PDO prepared statements for database queries.
- Session-based authentication.
- User ownership checks for protected records.
- Input validation and output escaping.

## Documentation

Additional project documentation can be found in the `documentation/` directory:

- **Screenshots** – Visual references of the system.
- **User Manual** – Instructions for using the application.
- **Technical Documentation** – System architecture, database structure, and implementation details.

## Development Team

This project was developed as an academic Modeling and Simulation project.

**Project Title:** Web-Based Visual Schedule, Task Management and Communication System

**Development Stack:** PHP, MySQL, HTML, CSS, and JavaScript.

## 📄 License

This project was developed for educational and academic purposes.
