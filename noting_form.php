<?php
include 'conn.php';
include 'header.php';

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Noting form data
    $dated = sanitize($_POST['dated']);
    $approved_by = sanitize($_POST['approved_by']);
    $noting_file = $_FILES['noting_file'];

    // File upload handling for noting
    $fileType = strtolower(pathinfo($noting_file['name'], PATHINFO_EXTENSION));
    $targetDir = "uploads/noting/{$dated}/";
    $targetFile = $targetDir . "{$approved_by}_" . $dated . "." . $fileType;

    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            error_log("Failed to create directory: $targetDir", 3, "errors.log");
        }
    }

    // Perform checks and move uploaded file
    $uploadOk = 1;

    // Check file type
    if (!in_array($fileType, ["jpeg", "jpg", "png", "pdf"])) {
        echo "Sorry, only JPG, JPEG, PNG, and PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check file size
    if ($noting_file['size'] > (2 * 1024 * 1024)) { // 2MB limit
        echo "Sorry, your file is too large. Maximum file size allowed is 2MB.";
        $uploadOk = 0;
    }

    // Insert data into noting table
    if ($uploadOk) {
        $sql = "INSERT INTO noting (dated, approved_by, noting_file) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die('MySQL prepare error: ' . $conn->error);
        }

        $stmt->bind_param("sss", $dated, $approved_by, $targetFile);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Noting form submitted successfully.'); window.location.href = 'dashboard.php';</script>";
        } else {
            echo "<script>alert('Failed to submit the noting form.');</script>";
        }

        $stmt->close();

        // Move uploaded file to target directory
        if ($uploadOk && move_uploaded_file($noting_file['tmp_name'], $targetFile)) {
            echo "<script>alert('File uploaded successfully.');</script>";
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
            error_log("Error moving uploaded file for noting: " . $noting_file['error'], 3, "errors.log");
        }
    }
}
// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
  
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img src="images/iesco_logo.png" alt="Logo">
            <label><h1>Noting</h1></label>
            <form name="noting" action="noting_form.php" method="post" enctype="multipart/form-data">

            <label for="approved_by">Initiated By:</label>
                <select id="approved_by" name="approved_by" required>
                    <option value="">Select Approver</option>
                    <option value="CEO">CEO</option>
                    <option value="SDO">SDO</option>
                    <option value="RO">RO</option>
                    <option value="XEN">XEN</option>
                    <option value="SE">SE</option>
                </select>

                <label for="dated">Date:</label>
                <input type="date" id="dated" name="dated" placeholder="Date" required>

                <label for="noting_file">Upload Noting File:</label>
                <input type="file" id="noting_file" name="noting_file" accept="image/jpeg,application/pdf" required>

                
                <input type="submit" name="submit" value="Approve Request">
            </form>
        </div>
    </div>
    <script>
        // JavaScript for adding file input dynamically
    </script>
</body>
</html>