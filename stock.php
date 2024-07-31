<?php
include 'conn.php';

// Handle form submission if POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Get form data
    $company_name = trim($_POST['company_name']);
    $model_name = trim($_POST['model_name']);
    $quantity = trim($_POST['quantity']);
    $procurement_date = trim($_POST['procurement_date']);
    $procurement_letterno = trim($_POST['procurement_letterno']);
    $receiving_date = trim($_POST['receiving_date']);

    // Check if any field contains only spaces
    if (empty($company_name) || ctype_space($company_name)) {
        $errors[] = "Company name cannot be empty or contain only spaces.";
    }
    if (empty($model_name) || ctype_space($model_name)) {
        $errors[] = "Model name cannot be empty or contain only spaces.";
    }
    if (empty($quantity) || ctype_space($quantity)) {
        $errors[] = "Quantity cannot be empty or contain only spaces.";
    }
    if (empty($procurement_letterno) || ctype_space($procurement_letterno)) {
        $errors[] = "Procurement letter number cannot be empty or contain only spaces.";
    }
    if (empty($procurement_date) || empty($receiving_date)) {
        $errors[] = "Both procurement date and receiving date are required.";
    }

    // Check if there are errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: stock.php");
        exit();
    }

    // Check if 'Other' is selected and get the other company name
    if ($company_name == "other") {
        $company_name = trim($_POST['other_company_name']);
        if (empty($company_name) || ctype_space($company_name)) {
            $errors[] = "Other company name cannot be empty or contain only spaces.";
            $_SESSION['errors'] = $errors;
            header("Location: stock.php");
            exit();
        }
    }
    // File upload directory
    $uploadDir = "uploads/procurement/" . date('Y') . '/' . date('m-d') . '/';

    // Create the directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle file uploads
    $files = $_FILES['procurement_letter'];
    $uploadedFiles = [];

    for ($i = 0; $i < count($files['name']); $i++) {
        $fileTmpPath = $files['tmp_name'][$i];
        $fileName = $files['name'][$i];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $letterno = $procurement_letterno; // Using procurement_letterno for filename

        // Sanitize file name
        $newFileName = $letterno . '__' . $procurement_date . '.' . $fileExtension;
        $uploadFilePath = $uploadDir . $newFileName;

        // Check if file is a PDF or JPEG
        $allowedfileExtensions = ['pdf', 'jpeg', 'jpg','png'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                $uploadedFiles[] = $uploadFilePath;
            } else {
                $errors[] = "Error moving the uploaded file: $fileName.";
            }
        } else {
            $errors[] = "Upload failed. Only PDF, PNG, JPG and JPEG files are allowed: $fileName.";
        }
    }

    // Check for file upload errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: stock.php");
        exit();
    }

    // Prepare and bind parameters for database insertion
    $stmt = $conn->prepare("INSERT INTO stock (company_name, model_name, quantity, procurement_date, procurement_letterno, receiving_date, procurement_letter) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $procurement_letter = implode(",", $uploadedFiles); // Store the paths of the uploaded files as a comma-separated string
    $stmt->bind_param("ssissss", $company_name, $model_name, $quantity, $procurement_date, $procurement_letterno, $receiving_date, $procurement_letter);

    // Execute the query
    if ($stmt->execute()) {
        $_SESSION['message'] = "New record created successfully";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();

    // Redirect to prevent form resubmission
    header("Location: stock.php");
    exit();
}

$conn->close(); // Close database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    
   
</head>
<body>
<?php include('header.php'); ?>

<div class="container">
    <div class="form-container">
        <img src="images/iesco_logo.png" alt="Logo">

        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']);
        }

        if (isset($_SESSION['errors'])) {
            foreach ($_SESSION['errors'] as $error) {
                echo "<div class='alert alert-danger'>" . $error . "</div>";
            }
            unset($_SESSION['errors']);
        }
        ?>

        <form action="stock.php" method="post" enctype="multipart/form-data" onsubmit="return validateDates()">

            <label for="company_name">Company Name:</label>
            <select name="company_name" id="company_name" placeholder="Company Name" required onchange="toggleOtherCompanyInput()">
                <option value="">Select Company Name</option>
                <option value="samsung">Samsung</option>
                <option value="tecno">Tecno</option>
                <option value="redmi">Redmi</option>
                <option value="infinix">Infinix</option>
                <option value="oppo">Oppo</option>
                <option value="huawei">Huawei</option>
                <option value="honor">Honor</option>
                <option value="other">Other</option>
            </select>

            <div id="other_company" style="display:none;">
                <label for="other_company_name">Other Company Name:</label>
                <input type="text" id="other_company_name" name="other_company_name" placeholder="Enter Other Company Name">
            </div>

            <label for="model_name">Model Name:</label>
            <input type="text" id="model_name" name="model_name" placeholder="Model Name" required>

            <label for="quantity">Quantity:</label>
            <input type="text" id="quantity" name="quantity" placeholder="Quantity" required>

            <label for="procurement_letterno">Procurement Letter No.:</label>
            <input type="text" id="procurement_letterno" name="procurement_letterno" placeholder="Procurement Letterno" required>

            <label for="procurement_date">Procurement Date:</label>
            <input type="date" id="procurement_date" name="procurement_date" placeholder="Procurement Date" required>

            <label for="receiving_date">Receiving Date:</label>
            <input type="date" id="receiving_date" name="receiving_date" placeholder="Receiving Date" required>

            <label for="procurement_letter">Procurement Letter:</label>
            <input type="file" id="procurement_letter" name="procurement_letter[]" multiple required>

            <input type="submit" name="submit" value="Enter">
        </form>
    </div>
</div>

<script>
    function validateDates() {
        var procurementDate = document.getElementById("procurement_date").value;
        var receivingDate = document.getElementById("receiving_date").value;

        var procDateParts = procurementDate.split("-");
        var recvDateParts = receivingDate.split("-");

        var procDate = new Date(procDateParts[0], procDateParts[1] - 1, procDateParts[2]);
        var recvDate = new Date(recvDateParts[0], recvDateParts[1] - 1, recvDateParts[2]);

        if (procDate > recvDate) {
            alert("Please enter valid dates. The procurement date cannot be after receiving date.");
            return false; // Prevent form submission
        }
        return true; // Allow form submission
    }

    function toggleOtherCompanyInput() {
        const companySelect = document.getElementById("company_name");
        const otherCompanyInput = document.getElementById("other_company");

        if (companySelect.value === "other") {
            otherCompanyInput.style.display = "block";
        } else {
            otherCompanyInput.style.display = "none";
        }
    }
</script>
</body>
</html>