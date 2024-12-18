<?php
session_start();
include 'db.php';

$courses_sql = "SELECT course_id, title, description, category FROM courses";
$result = $conn->query($courses_sql);

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = [
        'course_id' => $row['course_id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'category' => $row['category']
    ];
}

$result->free();  

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnHub - Online Learning Platform</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
    :root {
        --primary-color: #4a90e2;
        --secondary-color: #3498db;
        --light-bg: #f4f7f6;
    }

    body {
        background-color: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .head-section {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 4rem 0;
        text-align: center;
    }

    .course-card {


        margin-bottom: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .course-card:hover {

        scale: 1.05;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .course-card .card-body {
        display: flex;
        flex-direction: column;
    }

    .navbar {
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-cta {
        background-color: #2ecc71;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-cta:hover {
        background-color: #27ae60;
        transform: scale(1.05);
    }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <strong>Online Learning</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create_course.php">Create Course</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link  btn btn-success text-black" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-black" href="register.php">Sign Up</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <div class="head-section">
        <div class="container">

            <p class="lead">Discover Courses </p>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-lg btn-cta text-white mt-3">Start Learning Today</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Courses Section -->
    <div class="container py-5">
        <h2 class="text-center mb-4">Explore Our Courses</h2>
        <div class="row">
            <?php foreach ($courses as $course): ?>
            <div class="col-md-4">
                <a href="course.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>"
                    class="text-decoration-none text-dark">
                    <div class="card course-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                            <span class="badge bg-info">
                                <?php echo htmlspecialchars($course['category']); ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>