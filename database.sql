

CREATE DATABASE IF NOT EXISTS online_learning;
USE online_learning;

CREATE TABLE IF NOT EXISTS users (
    user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    social_login VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('learner', 'creator') DEFAULT 'learner'
);

CREATE TABLE IF NOT EXISTS courses (
    course_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    tags VARCHAR(255),
    creator_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(user_id)
);




CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    learner_id INT(11) NOT NULL,
    course_id INT(11) NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_id) REFERENCES users(user_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);



CREATE TABLE IF NOT EXISTS lessons (
    lesson_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    order_no INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);


CREATE TABLE IF NOT EXISTS assignments (
    assignment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);



CREATE TABLE IF NOT EXISTS quizzes (
    quiz_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

CREATE TABLE IF NOT EXISTS questions (
    question_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT(11) NOT NULL,
    question TEXT NOT NULL,
    options TEXT NOT NULL,
    correct_option VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id)
);

CREATE TABLE IF NOT EXISTS quiz_answers (
    answer_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    quiz_id INT(11) NOT NULL,
    question_id INT(11) NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id),
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

CREATE TABLE IF NOT EXISTS completed_courses (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    learner_id INT(11) NOT NULL,
    course_id INT(11) NOT NULL,
    course_title VARCHAR(255) NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_id) REFERENCES users(user_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);





ALTER TABLE users
ADD COLUMN verified_email INT(11) DEFAULT 0,
ADD COLUMN token VARCHAR(255) DEFAULT NULL;
