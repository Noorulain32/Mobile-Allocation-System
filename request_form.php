<?php
include 'conn.php';
include 'header.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $circlename = $_POST['circlename'];
    $division = $_POST['division'];
    $subdivision = $_POST['subdivision'];
    $dated = $_POST['dated'];
    $otheroffices = $_POST['otheroffices'];

    // Insert data into request_form table
    $sql = "INSERT INTO request_form (circlename, division, subdivision, dated, otheroffices) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Bind parameters and execute query
    $stmt->bind_param("sssss", $circlename, $division, $subdivision, $dated, $otheroffices);
    $stmt->execute();

    // Check for successful insertion and get the request_id
    if ($stmt->affected_rows > 0) {
        $request_id = $stmt->insert_id;
    } else {
        echo "<script>alert('Failed to submit the form.');</script>";
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Handle file uploads
    $allowedTypes = ["jpeg", "jpg", "png", "pdf"];
    $maxFileSize = 2 * 1024 * 1024; // 2MB in bytes
    $uploadOk = 1;

    foreach ($_FILES["letterfile"]["name"] as $key => $name) {
        if (!empty($name)) {
            $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $formattedDate = date("Y-m-d", strtotime($dated));
            $letterno = $_POST['letterno'][$key]; // Get the unique letter number for each file
            $filename = "{$letterno}" . $formattedDate . "$key." . $fileType;
            $target_dir = "uploads/request/" . date("Y", strtotime($dated)) . "/" . date("d-m-Y", strtotime($dated)) . "/{$subdivision}/{$letterno}/";
            $target_file = $target_dir . $filename;

            // Check if file type is allowed
            if (!in_array($fileType, $allowedTypes)) {
                echo "Sorry, only JPG, JPEG, PNG, and PDF files are allowed.";
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES["letterfile"]["size"][$key] > $maxFileSize) {
                echo "Sorry, your file is too large. Maximum file size allowed is 2MB.";
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                echo "Sorry, your file was not uploaded.";
            } else {
                // Check if the uploads directory exists, create if not
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true); // Adjust permission as needed
                }

                // Move uploaded file to target directory
                if (move_uploaded_file($_FILES["letterfile"]["tmp_name"][$key], $target_file)) {
                    $uploadletter = $target_file;

                    // Insert data into file_upload table
                    $sql = "INSERT INTO file_upload (request_id, letterfile, letterno) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        die('MySQL prepare error: ' . $conn->error);
                    }

                    // Bind parameters and execute query
                    $stmt->bind_param("iss", $request_id, $uploadletter, $letterno);
                    $stmt->execute();

                    // Check for successful insertion
                    if ($stmt->affected_rows > 0) {
                        echo "<script>alert('Form submitted successfully.'); window.location.href = 'dashboard.php';</script>";
                    } else {
                        echo "<script>alert('Failed to submit the form.');</script>";
                    }

                    $stmt->close();
                } else {
                    echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
                    error_log("Error moving uploaded file: " . $_FILES["letterfile"]["error"][$key], 3, "errors.log");
                }
            }
        }
    }

    // Close the connection
    $conn->close();
}
?>

<!-- Html code here -->
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
            <div id="requestForm">
                <form action="request_form.php" method="post" enctype="multipart/form-data">
                    <label for="otheroffices">Other Offices:</label>
                    <input type="text" id="otheroffices" name="otheroffices" placeholder="Other offices">
                    
                    <label for="circlename">Circle:</label>
                    <select name="circlename" id="circlename" onchange="updateDivisions()" required>
                        <option value="">Select Circle</option>
                        <option value="islamabad">Islamabad</option>
                        <option value="attock">Attock</option>
                        <option value="rawalpindi city">Rawalpindi City</option>
                        <option value="jhelum">Jhelum</option>
                        <option value="chakwal">Chakwal</option>
                        <option value="rawalpindi cantt">Rawalpindi Cantt</option>
                    </select>

                    <label for="division">Division:</label>
                    <select id="division" name="division" onchange="updateSubDivisions()" required>
                        <option value="">Select Division</option>
                    </select>

                    <label for="subdivision">Sub Division:</label>
                    <select id="subdivision" name="subdivision" required>
                        <option value="">Select Sub Division</option>
                    </select>

                    <!-- <label for="letterno">Letter No.:</label>
                    <input type="text" id="letterno" name="letterno" placeholder="Letter No." required> -->

                    <label for="dated">Date:</label>
                    <input type="date" id="dated" name="dated" placeholder="Date" required>

                    <div id="fileInputContainer">
                        <label for="letterfile">Upload Letter:</label>
                        <div class="file-input-container">
                            <input type="file" class="file-input" name="letterfile[]" accept="image/jpeg, application/pdf" required>
                            <input type="text" class="letter-no-input" name="letterno[]" placeholder="Letter No." required>
                            <span class="icon" onclick="addFileInput()">+</span>
                        </div>
                    </div>

                    <input type="submit" name="submit" value="Submit Request">
                </form>
            </div>
    <script>
        function toggleForm() {
            const approvalDropdown = document.getElementById("approvalDropdown");
            const requestForm = document.getElementById("requestForm");
            const approvalRadio = document.querySelector('input[name="approval"]:checked').value;

            if (approvalRadio === "approved") {
                approvalDropdown.style.display = "block";
                requestForm.style.display = "none";
            } else {
                approvalDropdown.style.display = "none";
                requestForm.style.display = "block";
            }
        }

                //all the divisions
        const divisions = {
            islamabad: ["Islamabad I", "Islamabad II", "Bara Kahu"],
            attock: ["Taxila", "Attock", "Pindi Gheb"],
            "rawalpindi city": ["Satellite Town", "Rawalpindi City", "Westridge"],
            jhelum: ["Jhelum I", "Jhelum II", "Gujjar Khan"],
            chakwal: ["Talagang", "Chakwal", "Pind Dadan Khan", "Dhudhail"],
            "rawalpindi cantt": ["Rawalpindi Cantt", "Tariqabad", "Rawat", "Mandra"]
        };
            //all the subdivisions
        const subDivisions = {
            "Islamabad I": ["G-6", "F-6", "G-7", "Rawal", "Khana Dak", "Nilore", "Tarlai"],
            "Islamabad II": ["F-8", "G-9", "I-9", "I-10", "G-11", "F-11"],
            "Bara Kahu": ["Bara Kahu Urban", "Murree", "Jhiga Gali", "Patriata", "Bara Kahu Rural"],
            Taxila: ["Taxila", "Margalla", "Wah Cantt", "Hassan Abdal", "Sangjani"],
            Attock: ["Hazro", "Hattian", "Ghor Ghushti", "Attock City", "Shadi Khan", "Attock Cantt"],
            "Pindi Gheb": ["Pindi Gheb", "Basal", "Fateh Jang", "Jand", "Khour", "Fateh Jang Rural", "Chhab"],
            "Satellite Town": ["F-Block", "Chandni Chowk", "Muslim Town", "Gangal", "Dhoke Kala Khan", "Gulzar-e-Quaid"],
            "Rawalpindi City": ["Zafar-ul-Haq Road", "Committee Chowk", "Ganj Mandi", "Bhabra Bazar", "Gawal Mandi", "Pir Wadhai", "Khyalbian Sir Syed", "Asghar Mall"],
            Westridge: ["Tench Bhata", "Westridge", "Kamal Abad Park", "Tarnol", "Seham", "Dhoke Ratta"],
            "Jhelum I": ["Jhelum Urban", "Jhelum Cantt", "Rajar", "Sarai Alamgir", "Civil Lines"],
            "Jhelum II": ["Jhelum Rural", "Dina-I", "Dina-II", "Domeli", "Sanghoi", "Dina City"],
            "Gujjar Khan": ["Gujjar Khan City", "Sohawa", "Bewal", "Guliana", "Bhadana"],
            Talagang: ["Talagang", "D.S. Bilawal", "Bagwal", "Talagang Rural", "Tamman"],
            Chakwal: ["Tariq Shaheed", "Kalar Kahar", "Chakwal City", "Ghaziabad", "Main Bazar"],
            "Pind Dadan Khan": ["Pind Dadan Khan", "Dharyala Jalip", "Pinawal", "Choa Saidan Shah", "Lillah", "Kahoun"],
            Dhudhail: ["Dhadial", "Khanpur", "Daulatala", "Tariq Shaheed"],
            "Rawalpindi Cantt": ["Pindi Saddar", "Pindi Civil Line", "Chaklala", "Korang", "Swan", "Jhanda Chichi", "Morgah"],
            Tariqabad: ["Tariqabad", "Adiala", "Dhamyal", "Quaid-e-Azam Colony", "R A Bazar", "Chakri"],
            Rawat: ["Rawat", "Kahuta", "Kallar Syedan", "Choa Khalsa", "Nara Matore", "Sagri"],
            Mandra: ["Mandra", "Wadala", "Sukhu", "Jatli", "Chak Beli Khan"]
        };

        function updateSubDivisions() {
            const divisionSelect = document.getElementById("division");
            const subdivisionSelect = document.getElementById("subdivision");
            const selectedDivision = divisionSelect.value;

            subdivisionSelect.innerHTML = '<option value="">Select Sub Division</option>';

            const division = divisions[selectedDivision];

            if (division) {
                division.forEach(function (subDivision) {
                    const option = document.createElement("option");
                    option.value = subDivision;
                    option.text = subDivision;
                    subdivisionSelect.appendChild(option);
                });
            }
        }

        function updateDivisions() {
            const circleSelect = document.getElementById("circlename");
            const divisionSelect = document.getElementById("division");
            const selectedCircle = circleSelect.value;

            divisionSelect.innerHTML = '<option value="">Select Division</option>';

            const circleDivisions = divisions[selectedCircle];

            if (circleDivisions) {
                circleDivisions.forEach(function (division) {
                    const option = document.createElement("option");
                    option.value = division;
                    option.text = division;
                    divisionSelect.appendChild(option);
                });
            }
        }

        function updateSubDivisions() {
            const divisionSelect = document.getElementById("division");
            const subDivisionSelect = document.getElementById("subdivision");
            const selectedDivision = divisionSelect.value;

            // Clear previous options
            subDivisionSelect.innerHTML = '<option value="">Select Sub Division</option>';

            // Get the subdivisions for the selected division
            const divisionSubDivisions = subDivisions[selectedDivision];

            // Populate the subdivisions dropdown
            if (divisionSubDivisions) {
                divisionSubDivisions.forEach(function (subDivision) {
                    const option = document.createElement("option");
                    option.value = subDivision;
                    option.text = subDivision;
                    subDivisionSelect.appendChild(option);
                });
            }
        }
        function addFileInput() {
            const fileInputContainer = document.getElementById("fileInputContainer");
            const newFileInputContainer = document.createElement("div");
            newFileInputContainer.classList.add("file-input-container");

            const newFileInput = document.createElement("input");
            newFileInput.type = "file";
            newFileInput.classList.add("file-input");
            newFileInput.name = "letterfile[]";
            newFileInput.accept = "image/jpeg, application/pdf";
            newFileInput.required = true; // Ensure the new input is also required

            const newLetterNoInput = document.createElement("input");
            newLetterNoInput.type = "text";
            newLetterNoInput.classList.add("letter-no-input");
            newLetterNoInput.name = "letterno[]";
            newLetterNoInput.placeholder = "Letter No.";
            newLetterNoInput.required = true;

            const removeIcon = document.createElement("span");
            removeIcon.classList.add("icon");
            removeIcon.innerHTML = "-";
            removeIcon.onclick = () => {
                newFileInputContainer.remove();
            };

            newFileInputContainer.appendChild(newFileInput);
            newFileInputContainer.appendChild(newLetterNoInput);
            newFileInputContainer.appendChild(removeIcon);

            fileInputContainer.appendChild(newFileInputContainer);
        }
    </script>
</body>
</html>