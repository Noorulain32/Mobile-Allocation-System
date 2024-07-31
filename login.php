<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare a statement with placeholders
    $sql = "SELECT userid, username, password FROM users WHERE username=? AND password=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Bind parameters and execute query
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
 
    // Get result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: Dashboard.php");
        exit();
    } else {
        echo "Invalid username or password";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/background_image.jpg');
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background-color: #fff;
            padding: 60px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }
        form img {
            width: 200px; 
            height: 90px;
            margin-bottom: 20px;
        }
        form label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            text-align: center;
        }
        .input-container {
            position: relative;
            margin-bottom: 20px;
        }
        .input-container input {
            width: calc(100% - 40px); /* Adjust width to leave space for the icon */
            padding: 10px 30px 10px 30px; /* Add padding on right to accommodate icon */
            margin: 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .input-container i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            z-index: 1; /* Ensure the icon is above the input field */
        }
        .input-container .fa-user {
            left: 10px;
        }
        .input-container .fa-lock {
            left: 10px;
        }
        .input-container .eye-icon {
            right: 10px;
            cursor: pointer;
        }
        form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: rgb(62, 89, 207);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        form input[type="submit"]:hover {
            background-color: rgb(50, 70, 160);
        }
    </style>
    <script>
        function validateForm() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (username === "" || password === "") {
                alert("Both username and password are required.");
                return false;
            }
            return true;
        }

        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordEye = document.getElementById('password-eye');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                passwordEye.classList.remove('fa-eye');
                passwordEye.classList.add('fa-eye-slash');
            } else {
                passwordField.type = "password";
                passwordEye.classList.remove('fa-eye-slash');
                passwordEye.classList.add('fa-eye');
            }
        }
    </script>
</head>
<body>
    <form action="login.php" method="post" onsubmit="return validateForm()">
        <img src="images/iesco_logo.png" alt="Logo">
        <label>Login to Continue</label>
        <div class="input-container">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Username" required>
        </div>
        <div class="input-container">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <i class="fas fa-eye eye-icon" id="password-eye" onclick="togglePassword()"></i>
        </div>
        <br>
        <input type="submit" name="submit" value="Login">
    </form>
</body>
</html>
