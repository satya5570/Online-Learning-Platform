<?php
session_start();

// Redirect if user is not logged in or not a course creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'creator') {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'db.php';

// Process the course creation form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Course details
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $tags = $_POST['tags'];
    $creator_id = $_SESSION['user_id'];

    // Insert course details into the database
    $stmt = $conn->prepare("INSERT INTO courses (title, description, category, tags, creator_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $title, $description, $category, $tags, $creator_id);

    if ($stmt->execute()) {
        $course_id = $conn->insert_id; // Get the ID of the newly created course

        // Insert lessons
        if (!empty($_POST['lessons'])) {
            foreach ($_POST['lessons'] as $lesson) {
                $lesson_title = $lesson['title'];
                $lesson_content = $lesson['content'];
                $lesson_stmt = $conn->prepare("INSERT INTO lessons (course_id, title, content) VALUES (?, ?, ?)");
                $lesson_stmt->bind_param("iss", $course_id, $lesson_title, $lesson_content);
                $lesson_stmt->execute();
            }
        }

        // Insert quizzes
        if (!empty($_POST['quizzes'])) {
            foreach ($_POST['quizzes'] as $quiz) {
                $quiz_title = $quiz['title'];
                $quiz_stmt = $conn->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
                $quiz_stmt->bind_param("is", $course_id, $quiz_title);
                $quiz_stmt->execute();
                $quiz_id = $conn->insert_id;

                if (!empty($quiz['questions'])) {
                    foreach ($quiz['questions'] as $question) {
                        $question_text = $question['question'];
                        $options = $question['options'];
                        $correct_option = $question['correct_option'];
                        $question_stmt = $conn->prepare("INSERT INTO questions (quiz_id, question, options, correct_option) VALUES (?, ?, ?, ?)");
                        $question_stmt->bind_param("isss", $quiz_id, $question_text, $options, $correct_option);
                        $question_stmt->execute();
                    }
                }
            }
        }

        // Insert assignments
        if (!empty($_POST['assignments'])) {
            foreach ($_POST['assignments'] as $assignment) {
                $assignment_title = $assignment['title'];
                $assignment_description = $assignment['description'];
                $due_date = $assignment['due_date'];
                $assignment_stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)");
                $assignment_stmt->bind_param("isss", $course_id, $assignment_title, $assignment_description, $due_date);
                $assignment_stmt->execute();
            }
        }

        echo "<p class='success-message'>Course and related content created successfully!</p>";
    } else {
        echo "<p class='error-message'>Error creating course: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course</title>
    <link rel="stylesheet" href="CSS/enroll.css">
</head>

<body>
    <!-- Header -->
    <header>
        <h1>Create Course</h1>
        <a href="logout.php">Logout</a> <!-- Logout Button -->
    </header>

    <!-- Navigation Bar -->
    <nav>
        <a href="create_course.php">Create Course</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="browse_courses.php">Browse Courses</a>
        <a href="logout.php">Logout</a> <!-- Another Logout Link -->
    </nav>

    <div class="container">
        <h2>Create a New Course</h2>
        <form action="create_course.php" method="POST">
            <!-- Course Details -->
            <h3>Course Details</h3>
            <input type="text" name="title" placeholder="Course Title" required>
            <textarea name="description" placeholder="Course Description" required></textarea>
            <input type="text" name="category" placeholder="Category">
            <input type="text" name="tags" placeholder="Tags (comma-separated)">

            <!-- Lessons -->
            <div class="dynamic-section" id="lessons">
                <h3>Lessons</h3>
                <div class="lesson">
                    <input type="text" name="lessons[0][title]" placeholder="Lesson Title" required>
                    <textarea name="lessons[0][content]" placeholder="Lesson Content" required></textarea>
                </div>
            </div>
            <button type="button" onclick="addLesson()">Add Another Lesson</button>

            <!-- Quizzes -->
            <div class="dynamic-section" id="quizzes">
                <h3>Quizzes</h3>
                <div class="quiz">
                    <input type="text" name="quizzes[0][title]" placeholder="Quiz Title" required>
                    <textarea name="quizzes[0][questions][0][question]" placeholder="Question" required></textarea>
                    <input type="text" name="quizzes[0][questions][0][options]" placeholder="Options (comma-separated)"
                        required>
                    <input type="text" name="quizzes[0][questions][0][correct_option]" placeholder="Correct Option"
                        required>
                </div>
            </div>
            <button type="button" onclick="addQuiz()">Add Another Quiz</button>

            <!-- Assignments -->
            <div class="dynamic-section" id="assignments">
                <h3>Assignments</h3>
                <div class="assignment">
                    <input type="text" name="assignments[0][title]" placeholder="Assignment Title" required>
                    <textarea name="assignments[0][description]" placeholder="Assignment Description"
                        required></textarea>
                    <input type="date" name="assignments[0][due_date]" required>
                </div>
            </div>
            <button type="button" onclick="addAssignment()">Add Another Assignment</button>

            <button type="submit">Create Course</button>
        </form>
    </div>

    <script>
    function addLesson() {
        const lessonsDiv = document.getElementById("lessons");
        const lessonCount = lessonsDiv.children.length;
        const newLesson = document.createElement("div");
        newLesson.classList.add("lesson");
        newLesson.innerHTML = `
                <input type="text" name="lessons[${lessonCount}][title]" placeholder="Lesson Title" required>
                <textarea name="lessons[${lessonCount}][content]" placeholder="Lesson Content" required></textarea>
            `;
        lessonsDiv.appendChild(newLesson);
    }

    function addQuiz() {
        const quizzesDiv = document.getElementById("quizzes");
        const quizCount = quizzesDiv.children.length;
        const newQuiz = document.createElement("div");
        newQuiz.classList.add("quiz");
        newQuiz.innerHTML = `
                <input type="text" name="quizzes[${quizCount}][title]" placeholder="Quiz Title" required>
                <textarea name="quizzes[${quizCount}][questions][0][question]" placeholder="Question" required></textarea>
                <input type="text" name="quizzes[${quizCount}][questions][0][options]" placeholder="Options (comma-separated)" required>
                <input type="text" name="quizzes[${quizCount}][questions][0][correct_option]" placeholder="Correct Option" required>
            `;
        quizzesDiv.appendChild(newQuiz);
    }

    function addAssignment() {
        const assignmentsDiv = document.getElementById("assignments");
        const assignmentCount = assignmentsDiv.children.length;
        const newAssignment = document.createElement("div");
        newAssignment.classList.add("assignment");
        newAssignment.innerHTML = `
                <input type="text" name="assignments[${assignmentCount}][title]" placeholder="Assignment Title" required>
                <textarea name="assignments[${assignmentCount}][description]" placeholder="Assignment Description" required></textarea>
                <input type="date" name="assignments[${assignmentCount}][due_date]" required>
            `;
        assignmentsDiv.appendChild(newAssignment);
    }
    </script>
</body>

</html>