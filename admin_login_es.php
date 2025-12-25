<?php
session_start();
//include("includes/config.php");
include("test/includes/config_elasticsearch.php");
if (isset($_POST['submit'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Elasticsearch query
        $params = [
            'index' => 'admin',
            'body'  => [
                'query' => [
                    'term' => [
                        'userName' => $username
                    ]
                ]
            ]
        ];

        $response = $esClient->search($params);

        // Check if user exists
        if ($response['hits']['total']['value'] > 0) {

            $adminData = $response['hits']['hits'][0]['_source'];

            // Verify password
            if (password_verify($password, $adminData['password'])) {

                $_SESSION['alogin']   = $username;
                $_SESSION['username'] = $username;

                header("Location: dashboard.php");
                exit;
            }
        }

        // If login fails
        echo "<p>Invalid username or password.</p>";

    } catch (Exception $e) {
        echo "<p>Login error: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="style/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1> Admin Login</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="UserName" required> 
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <button type="submit" name="submit" class="btn">LOG IN</button>
            <div class="register-link">
                <p>Dont have an account?
                    <a href="adminregister.php">Register</a>
                </p>
            </div>
            <div class="register-link">
                <p>Other Logins?
                    <a href="login.php">Click Here</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>