<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'creator') {
    header("Location: dashboard.php");
    exit;
}

include 'db.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $tags = $_POST['tags'];
    $creator_id = $_SESSION['user_id'];

   
$title = $conn->real_escape_string($title);
$description = $conn->real_escape_string($description);
$category = $conn->real_escape_string($category);
$tags = $conn->real_escape_string($tags);
$creator_id = intval($creator_id);


$sql_insert_course = "
    INSERT INTO courses (title, description, category, tags, creator_id) 
    VALUES ('$title', '$description', '$category', '$tags', $creator_id)
";

if ($conn->query($sql_insert_course)) {
    $course_id = $conn->insert_id;


    if (!empty($_POST['lessons'])) {
        foreach ($_POST['lessons'] as $lesson) {
            $lesson_title = $conn->real_escape_string($lesson['title']);
            $lesson_content = $conn->real_escape_string($lesson['content']);
            $sql_insert_lesson = "
                INSERT INTO lessons (course_id, title, content) 
                VALUES ($course_id, '$lesson_title', '$lesson_content')
            ";
            $conn->query($sql_insert_lesson);
        }
    }


    if (!empty($_POST['quizzes'])) {
        foreach ($_POST['quizzes'] as $quiz) {
            $quiz_title = $conn->real_escape_string($quiz['title']);
            $sql_insert_quiz = "
                INSERT INTO quizzes (course_id, title) 
                VALUES ($course_id, '$quiz_title')
            ";
            if ($conn->query($sql_insert_quiz)) {
                $quiz_id = $conn->insert_id;

                if (!empty($quiz['questions'])) {
                    foreach ($quiz['questions'] as $question) {
                        $question_text = $conn->real_escape_string($question['question']);
                        $options = $conn->real_escape_string($question['options']);
                        $correct_option = $conn->real_escape_string($question['correct_option']);
                        $sql_insert_question = "
                            INSERT INTO questions (quiz_id, question, options, correct_option) 
                            VALUES ($quiz_id, '$question_text', '$options', '$correct_option')
                        ";
                        $conn->query($sql_insert_question);
                    }
                }
            }
        }
    }

   
    if (!empty($_POST['assignments'])) {
        foreach ($_POST['assignments'] as $assignment) {
            $assignment_title = $conn->real_escape_string($assignment['title']);
            $assignment_description = $conn->real_escape_string($assignment['description']);
            $due_date = $conn->real_escape_string($assignment['due_date']);
            $sql_insert_assignment = "
                INSERT INTO assignments (course_id, title, description, due_date) 
                VALUES ($course_id, '$assignment_title', '$assignment_description', '$due_date')
            ";
            $conn->query($sql_insert_assignment);
        }
    }
} else {
    echo "<p class='error-message'>Error creating course: " . $conn->error . "</p>";
}

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course</title>
    <link rel="stylesheet" href="CSS/create_course.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    header {
        background-color: #4CAF50;
        color: white;
        padding: 1em;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    header h1 {
        margin: 0;
        font-size: 1.5em;
    }

    nav a {
        color: white;
        text-decoration: none;
        background: #333;
        padding: 10px 15px;
        border-radius: 5px;
        font-size: 0.9em;
        transition: background-color 0.3s;
    }

    nav a:hover {
        background-color: #555;
    }

    .container {
        max-width: 900px;
        margin: 20px auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 10px;
    }

    button:hover {
        background-color: #45a049;
    }
    </style>
</head>

<body>
    <header>
        <h1>Create Course</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Create a New Course</h2>
        <form action="create_course.php" method="POST">

            <h3>Course Details</h3>
            <input type="text" name="title" placeholder="Course Title" required>
            <textarea name="description" placeholder="Course Description" required></textarea>
            <input type="text" name="category" placeholder="Category">
            <input type="text" name="tags" placeholder="Tags (comma-separated)">


            <div class="dynamic-section" id="lessons">
                <h3>Lessons</h3>
                <div class="lesson">
                    <input type="text" name="lessons[0][title]" placeholder="Lesson Title" required>
                    <textarea name="lessons[0][content]" placeholder="Lesson Content" required></textarea>
                </div>
            </div>
            <button type="button" onclick="addLesson()">Add Another Lesson</button>


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