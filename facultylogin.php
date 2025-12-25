<?php
session_start();
include("includes/config_elasticsearch.php");

if(isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Elasticsearch query to find faculty by facultyName and facultyCode
    $query = [
        'query' => [
            'bool' => [
                'must' => [
                    ['term' => ['facultyName.keyword' => $username]],
                    ['term' => ['facultyCode' => $password]]
                ]
            ]
        ],
        'size' => 1
    ];
    
    $result = $es->search(INDEX_FACULTY, $query);
    
    if($result['success'] && isset($result['data']['hits']['hits'][0])) {
        $row = $result['data']['hits']['hits'][0]['_source'];
        $facultyid = $row['id'];
        $_SESSION['id'] = $facultyid;
        header("Location: dashboardfaculty.php");
        exit;
    } else {
        echo "<p>Invalid username or password.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Faculty</title>
    <link rel="stylesheet" href="style/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Faculty Login</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="UserName" required> 
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <button type="submit" name="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Other Logins?
                    <a href="login.php">Click Here</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>
