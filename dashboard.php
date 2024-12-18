<?php
session_start();
require_once 'vendor/autoload.php';

include 'db.php';
include 'config.php';

$clientID = $cid;
$clientSecret = $csc;
$redirectUri = 'http://localhost/OnlineLearningPlatform/dashboard.php';

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

$user_id = null;
$name = "";
$userinfo = [];

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (isset($token['access_token'])) {
            $client->setAccessToken($token['access_token']);

            $google_oauth = new Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            $userinfo = [
                'email' => $conn->real_escape_string($google_account_info['email']),
                'first_name' => $google_account_info['givenName'],
                'last_name' => $google_account_info['familyName'],
                'gender' => $google_account_info['gender'],
                'full_name' => $conn->real_escape_string($google_account_info['name']),
                'picture' => $google_account_info['picture'],
                'verifiedEmail' => $google_account_info['verifiedEmail'],
                'token' => $google_account_info['id'],
            ];

            $sql = "SELECT * FROM users WHERE email = '{$userinfo['email']}'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $userinfo = $result->fetch_assoc();
                $token = $userinfo['token'];
                $_SESSION['user_id'] = $userinfo['user_id'];
            } else {
                $sql = "
                    INSERT INTO users (name, email, social_login, verified_email, token)
                    VALUES ('{$userinfo['full_name']}', '{$userinfo['email']}', 'google', {$userinfo['verifiedEmail']}, '{$userinfo['token']}')
                ";
                if ($conn->query($sql)) {
                    $token = $userinfo['token'];
                    $_SESSION['user_id'] = $conn->insert_id;
                } else {
                    header("Location: error.php");
                    exit;
                }
            }

            $_SESSION['user_token'] = $token;

            $sql = "SELECT name FROM users WHERE token = '{$_SESSION['user_token']}'";
            $result = $conn->query($sql);
            if ($result && $row = $result->fetch_assoc()) {
                $name = $row['name'];
            }
        } else {
            header("Location: login.php");
            exit;
        }
    } catch (Exception $e) {
        error_log("Error fetching access token: " . $e->getMessage());
        header("Location: error.php");
        exit;
    }
} elseif (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $sql = "SELECT name FROM users WHERE user_id = $user_id";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $name = $row['name'];
    }
} else {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
    $sql_check = "SELECT * FROM enrollments WHERE learner_id = $user_id AND course_id = $course_id";
    $result_check = $conn->query($sql_check);

    if ($result_check && $result_check->num_rows == 0) {
        $sql_enroll = "INSERT INTO enrollments (learner_id, course_id) VALUES ($user_id, $course_id)";
        $conn->query($sql_enroll);
    }
}

$courses = [];
$sql_courses = "SELECT course_id, title, description, category FROM courses";
$result_courses = $conn->query($sql_courses);
if ($result_courses) {
    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row;
    }
}

$enrolled_courses = [];
$sql_enrolled = "
    SELECT c.course_id, c.title, c.description
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.course_id 
    WHERE e.learner_id = $user_id
";

$result_enrolled = $conn->query($sql_enrolled);
if ($result_enrolled) {
    while ($row = $result_enrolled->fetch_assoc()) {
        $enrolled_courses[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learner Dashboard</title>
    <link rel="stylesheet" href="CSS/dashboard.css">
</head>

<body>
    <header>
        <h1>Learner Dashboard</h1>
        <div class="nav">
            <p>Logged in as: <strong><?php echo htmlspecialchars($name); ?></strong></p>
            <a href="completed_courses_list.php" style="margin-right: 10px;">Completed Courses</a>
            <a href="changepassword.php" style="margin-right: 10px;">Change Password</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <div class="section">
        <h2>Available Courses</h2>
        <div class="card-container">
            <?php foreach ($courses as $course): ?>

            <?php 
            $is_enrolled = false;
            foreach ($enrolled_courses as $enrolled_course) {
                if ($enrolled_course['course_id'] == $course['course_id']) {
                    $is_enrolled = true;
                    break;
                }
            }
            ?>
            <a href="course.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>"
                style="text-decoration: none;">
                <div class="card">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($course['category']); ?></p>
                    <?php if (!$is_enrolled): ?>
                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                        <button type="submit">Enroll</button>
                    </form>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section">
        <h2>Enrolled Courses</h2>
        <div class="card-container">
            <?php if (!empty($enrolled_courses)): ?>
            <?php foreach ($enrolled_courses as $course): ?>
            <a href="course.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>"
                style="text-decoration: none;">
                <div class="card">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                </div>
            </a>
            <?php endforeach; ?>
            <?php else: ?>
            <p>No enrolled courses yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>