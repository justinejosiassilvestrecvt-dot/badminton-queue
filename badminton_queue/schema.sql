CREATE DATABASE IF NOT EXISTS badminton_queue;
USE badminton_queue;

-- USER ENTITY
CREATE TABLE users (
  user_id        INT AUTO_INCREMENT PRIMARY KEY,
  first_name     VARCHAR(50)  NOT NULL,
  last_name      VARCHAR(50)  NOT NULL,
  age            TINYINT UNSIGNED NOT NULL,
  gender         VARCHAR(20)  NOT NULL,
  address        VARCHAR(255) NOT NULL,
  contact_number VARCHAR(20)  NOT NULL,
  email          VARCHAR(100) NOT NULL UNIQUE,
  username       VARCHAR(50)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PARENT ENTITY (ONE court ...)
CREATE TABLE courts (
  court_id        INT AUTO_INCREMENT PRIMARY KEY,
  court_name      VARCHAR(100) NOT NULL,
  location        VARCHAR(150) NOT NULL,
  status          ENUM('Available','In Use','Maintenance') NOT NULL DEFAULT 'Available',
  date_added      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  added_by        INT NULL,
  last_updated_by INT NULL,
  last_updated    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (added_by) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (last_updated_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- CHILD ENTITY (... has MANY queue entries)
CREATE TABLE queue_entries (
  queue_id        INT AUTO_INCREMENT PRIMARY KEY,
  court_id        INT NOT NULL,
  player_name     VARCHAR(100) NOT NULL,
  skill_level     ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  game_type       ENUM('Singles','Doubles') NOT NULL DEFAULT 'Doubles',
  status          ENUM('Waiting','Playing','Done') NOT NULL DEFAULT 'Waiting',
  date_added      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  added_by        INT NULL,
  last_updated_by INT NULL,
  last_updated    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (court_id) REFERENCES courts(court_id) ON DELETE CASCADE,
  FOREIGN KEY (added_by) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (last_updated_by) REFERENCES users(user_id) ON DELETE SET NULL
);
