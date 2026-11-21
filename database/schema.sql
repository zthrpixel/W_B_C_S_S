CREATE DATABASE IF NOT EXISTS visual_schedule_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE visual_schedule_system;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    age TINYINT UNSIGNED NULL,
    address VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('caregiver', 'therapist', 'teacher') NOT NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE children (
    child_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender VARCHAR(30) NULL,
    profile_image VARCHAR(255) NULL,
    development_domain VARCHAR(150) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_children_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    activity_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(255) NULL,
    category VARCHAR(100) NULL,
    default_duration INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_activities_user
        FOREIGN KEY (created_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    created_by INT NOT NULL,
    schedule_name VARCHAR(150) NOT NULL,
    schedule_date DATE NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_schedules_child
        FOREIGN KEY (child_id)
        REFERENCES children(child_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_schedules_user
        FOREIGN KEY (created_by)
        REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE schedule_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    activity_id INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    item_order INT NOT NULL,
    reminder_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_schedule_items_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES schedules(schedule_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_schedule_items_activity
        FOREIGN KEY (activity_id)
        REFERENCES activities(activity_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    assigned_by INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    priority ENUM('low', 'normal', 'high')
        NOT NULL DEFAULT 'normal',
    status ENUM('pending', 'in_progress', 'completed')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tasks_child
        FOREIGN KEY (child_id)
        REFERENCES children(child_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_tasks_user
        FOREIGN KEY (assigned_by)
        REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE task_completions (
    completion_id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    completed_by INT NULL,
    completion_date DATE NOT NULL,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT NULL,

    CONSTRAINT fk_completions_task
        FOREIGN KEY (task_id)
        REFERENCES tasks(task_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_completions_user
        FOREIGN KEY (completed_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE communication_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE communication_cards (
    card_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    card_name VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) NULL,
    spoken_text VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cards_category
        FOREIGN KEY (category_id)
        REFERENCES communication_categories(category_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE progress_records (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    progress_date DATE NOT NULL,
    total_tasks INT NOT NULL DEFAULT 0,
    completed_tasks INT NOT NULL DEFAULT 0,
    completion_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    remarks TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_progress_child
        FOREIGN KEY (child_id)
        REFERENCES children(child_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT unique_child_progress_date
        UNIQUE (child_id, progress_date)
) ENGINE=InnoDB;
