<?php
session_start();
include 'db.php';

// Fetch all available courses
$courses_stmt = $conn->prepare("SELECT course_id, title, description, category FROM courses");
$courses_stmt->execute();
$courses_stmt->bind_result($course_id, $course_title, $course_description, $course_category);

$courses = [];
while ($courses_stmt->fetch()) {
    $courses[] = [
        'course_id' => $course_id,
        'title' => $course_title,
        'description' => $course_description,
        'category' => $course_category
    ];
}
$courses_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Learning Platform</title>
    <link rel="stylesheet" href="CSS/index.css">

</head>

<body>
    <header>
        <h1>Welcome to the Online Learning Platform</h1>
    </header>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
        <a href="create_course.php">Create Course</a>
        <a href="dashboard.php">Dashboard</a>
        <?php else: ?>
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>

    <section class="section">
        <h2>Explore Our Courses</h2>
        <div class="card-container">
            <?php foreach ($courses as $course): ?>
            <a href="course.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>"
                style="text-decoration: none;">
                <div class="card">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($course['category']); ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="cta">
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php">Get Started</a>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Online Learning Platform. All Rights Reserved.</p>
    </footer>
</body>

</html>