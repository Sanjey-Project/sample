<?php
include("includes/config_elasticsearch.php");

$errors = []; // Array to store validation errors

if(isset($_POST['submit'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phno = $_POST['phno'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];

    // Check if full name and username are the same
    if($fullname === $username) {
        $errors[] = "Full Name and Username should not be the same.";
    }

    // Check if password and confirm password match
    if($password !== $confirmpassword) {
        $errors[] = "Password and Confirm Password do not match.";
    }

    // Check if there are already users with the same username or phone number using Elasticsearch
    if($es->indexExists(INDEX_ADMINISTRATORS)) {
        $checkQuery = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['term' => ['userName' => $username]],
                        ['term' => ['phno' => intval($phno)]]
                    ],
                    'minimum_should_match' => 1
                ]
            ],
            'size' => 1
        ];
        
        $checkResult = $es->search(INDEX_ADMINISTRATORS, $checkQuery);
        
        if($checkResult['success']) {
            $totalHits = isset($checkResult['data']['hits']['total']['value']) ? 
                         $checkResult['data']['hits']['total']['value'] : 
                         (isset($checkResult['data']['hits']['total']) ? intval($checkResult['data']['hits']['total']) : 0);
            
            $hasHits = isset($checkResult['data']['hits']['hits']) && 
                      is_array($checkResult['data']['hits']['hits']) && 
                      count($checkResult['data']['hits']['hits']) > 0;
            
            if($totalHits > 0 || $hasHits) {
                $errors[] = "Username or Phone Number already exists.";
            }
        }
    }

    // If there are no validation errors, proceed to save the user's information
    if(empty($errors)) {
        // Get the next available ID
        $newId = 1;
        if($es->indexExists(INDEX_ADMINISTRATORS)) {
            $maxIdQuery = [
                'query' => ['match_all' => []],
                'sort' => [['id' => ['order' => 'desc']]],
                'size' => 1
            ];
            
            $maxIdResult = $es->search(INDEX_ADMINISTRATORS, $maxIdQuery);
            
            if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
                $newId = intval($maxId) + 1;
            }
        }
        
        // Prepare document for Elasticsearch
        $document = [
            'id' => $newId,
            'fullName' => $fullname,
            'userName' => $username,
            'email' => $email,
            'phno' => intval($phno),
            'password' => $password,
            'updationDate' => date('Y-m-d H:i:s')
        ];
        
        // Index the document (INSERT equivalent)
        $insertResult = $es->index(INDEX_ADMINISTRATORS, $newId, $document);
        
        if($insertResult['success']) {
            echo"<script>
            alert('registration successful');
            </script>";
            header("location:adminlogin.php");
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
    
    // Display validation errors
    if(!empty($errors)) {
        foreach($errors as $error) {
            echo "<p style='color: red; padding: 10px; background: rgba(255,0,0,0.1); border-radius: 5px; margin: 10px 0;'>$error</p>";
        }
    }
}
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" href="style/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1>Registration</h1>
            <div class="input-box">
                <div class="input-field">
                    <input type="text"  name="fullname" placeholder="Full Name" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-field">
                    <input type="text"  name="username" placeholder="UserName" required>
                    <i class='bx bxs-user'></i>
                </div>
                </div>

                <div class="input-box">
                <div class="input-field">
                    <input type="email" name="email"placeholder="Email Id" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <div class="input-field">
                    <input type="number" name="phno" placeholder="Phno" required>
                    <i class='bx bxs-phone'></i>
                </div>
                </div>

                <div class="input-box">
                <div class="input-field">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div>
                <div class="input-field">
                    <input type="password" name="confirmpassword" placeholder="Confirm Password" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div>
            </div>

            <label>
                <input type="checkbox" required>I hereby declare that the information provided is true and correct.
            </label>

            <button type="submit" class="btn" name="submit">Register</button>
            <div class="register-link">
                <p>Already have an account?
                    <a href="adminlogin.php">Login</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>