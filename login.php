<?php
// Start session
session_start();
// Connect to the database
include("connection.php");

// Check user inputs
$errors = ''; // Initialize errors variable
$missingEmail = '<p><strong>Please enter your email address!</strong></p>';
$missingPassword = '<p><strong>Please enter your password!</strong></p>';

// Get email and password
if(empty($_POST["loginemail"])) {
    $errors .= $missingEmail;
} else {
    $email = filter_var($_POST["loginemail"], FILTER_SANITIZE_EMAIL);
}

if(empty($_POST["loginpassword"])) {
    $errors .= $missingPassword;
} else {
    $password = filter_var($_POST["loginpassword"], FILTER_SANITIZE_STRING);
}

// If there are any errors
if($errors) {
    $resultMessage = '<div class="alert alert-danger">' . $errors .'</div>';
    echo $resultMessage;
} else {
    // Prepare variables for the query
    $email = mysqli_real_escape_string($link, $email);
    $password = mysqli_real_escape_string($link, $password);
    $password = hash('sha256', $password);

    // Run query: Check combination of email & password exists
    $sql = "SELECT * FROM users WHERE email=? AND password=? AND activation='activated'";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(!$result) {
        echo '<div class="alert alert-danger">Error running the query!</div>';
        exit;
    }

    // If email & password don't match print error
    $count = mysqli_num_rows($result);
    if($count !== 1) {
        echo '<div class="alert alert-danger">Wrong Username or Password</div>';
    } else {
        // Log the user in: Set session variables
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];

        if(empty($_POST['rememberme'])) {
            echo "success";
        } else {
            // Create two variables $authenticator1 and $authenticator2
            $authenticator1 = bin2hex(openssl_random_pseudo_bytes(10));
            $authenticator2 = openssl_random_pseudo_bytes(20);

            // Store them in a cookie
            $cookieValue = $authenticator1 . ":" . bin2hex($authenticator2);
            setcookie(
                "rememberme",
                $cookieValue,
                time() + 1296000,
                '/',
                '',
                true,
                true
            );

            // Run query to store them in rememberme table
            $f2authenticator2 = hash('sha256', $authenticator2);
            $user_id = $_SESSION['user_id'];
            $expiration = date('Y-m-d H:i:s', time() + 1296000);

            $sql = "INSERT INTO rememberme
                    (`authenticator1`, `f2authenticator2`, `user_id`, `expires`)
                    VALUES
                    (?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssis", $authenticator1, $f2authenticator2, $user_id, $expiration);
            $result = mysqli_stmt_execute($stmt);

            if(!$result) {
                echo  '<div class="alert alert-danger">There was an error storing data to remember you next time.</div>';
            } else {
                echo "success";
            
    
            }
        }
    }
}
?>
