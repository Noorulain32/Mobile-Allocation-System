<?php
include 'conn.php';

// Function to retrieve quantity from stock table
function getStockQuantity($conn, $company_name, $model_name) {
    $sql = "SELECT quantity FROM stock WHERE company_name = '$company_name' AND model_name = '$model_name'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['quantity'];
    } else {
        return 0; // Return 0 if no quantity found
    }
}

// Handle AJAX request to get quantity
if (isset($_POST['ajax']) && $_POST['ajax'] == 'get_quantity' && isset($_POST['company_name']) && isset($_POST['model_name'])) {
    $company_name = $_POST['company_name'];
    $model_name = $_POST['model_name'];
    
    $quantity = getStockQuantity($conn, $company_name, $model_name);
    echo $quantity; // Return only the quantity
    exit;
}

if (isset($_POST['submit'])) {
    $other_office = $_POST['otheroffices'];
    $circle = $_POST['circlename'];
    $division = $_POST['division'];
    $subdivision = $_POST['subdivision'];
    $quantity = $_POST['quantity'];
    $remaining_quantity = $_POST['remaining_quantity'];
    $allocated_quantity = $_POST['allocated_quantity'];
    $company_name = $_POST['company_name'];
    if ($company_name == "other") {
        $company_name = $_POST['other_company_name'];
    }
    $model_name = $_POST['model_name'];
    $dated = $_POST['dated'];

    // Retrieve quantity from stock table
    $quantity = getStockQuantity($conn, $company_name, $model_name);

    $allocated_quantity = $_POST['allocated_quantity'];
    $remaining_quantity = $quantity - $allocated_quantity; // Calculate remaining quantity

    $base_url = 'http://localhost/mobile_allocation_system/';
    
    // File upload directory
    $uploadDir = "uploads/allocation/". date('Y') . '/' . date('m-d') . '/';

    // Create the directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle file uploads
    $files = $_FILES['allocate_letterfile'];
    $letternoArr = $_POST['allocate_letterno'];

    for ($i = 0; $i < count($files['name']); $i++) {
        $fileTmpPath = $files['tmp_name'][$i];
        $fileName = $files['name'][$i];
        $fileSize = $files['size'][$i];
        $fileType = $files['type'][$i];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $letterno = $letternoArr[$i];

        // Sanitize file name
        $newFileName = $letterno . '_' . $dated . '.' . $fileExtension;

        // Check if file is a PDF or JPEG
        $allowedfileExtensions = array('pdf', 'jpeg', 'png', 'jpg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $uploadFileDir)) {
                echo "File is successfully uploaded.\n";
            } else {
                echo "Error moving the uploaded file.\n";
            }
        } else {
            echo "Upload failed. Only PDF and JPEG files are allowed.\n";
        }
    }

    // Insert into database
    $sql = "INSERT INTO allocation (other_office, circle, division, sub_division, quantity, remaining_quantity, allocated_quantity, company_name, model_name, allocate_letterfile, allocate_letterno, dated)
            VALUES ('$other_office', '$circle', '$division', '$subdivision', '$quantity', '$remaining_quantity', '$allocated_quantity', '$company_name', '$model_name', '$uploadFileDir', '$letterno', '$dated')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Form submitted successfully.'); window.location.href = 'new_allocation.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>

<!-- Html code here -->
<!-- HTML code here -->
<!DOCTYPE html>
<html lang="en">
<head>
     <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocation</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img src="images/iesco_logo.png" alt="Logo">
            <label><h1>Allocation</h1></label>
            <div id="requestForm">
                <form action="new_allocation.php" method="post" enctype="multipart/form-data">
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

                    <label for="company_name">Company Name</label>
                    <select name="company_name" id="company_name" placeholder="Company Name" required onchange="toggleOtherCompanyInput(); updateQuantity();">
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

                    <label for="model_name">Model Name</label>
                    <input type="text" id="model_name" name="model_name" placeholder="Model Name" required onkeyup="updateQuantity()">

                    <label for="quantity">Quantity:</label>
                    <input type="text" id="quantity" name="quantity" readonly required>
                                        
                    <label for="remaining_quantity">Remaining Quantity:</label>
                    <input type="text" id="remaining_quantity" name="remaining_quantity" readonly required>

                    <label for="allocated_quantity">Allocated Quantity:</label>
                    <input type="text" id="allocated_quantity" name="allocated_quantity" placeholder="Allocated Quantity" required oninput="calculateRemainingQuantity()">

                    <div id="fileInputContainer">
                        <label for="allocate_letterfile">Allocation Letter File:</label>
                        <div class="file-input-container">
                            <input type="file" class="file-input" name="allocate_letterfile[]" accept="image/jpeg, application/pdf" required>
                            <input type="text" class="letter-no-input" name="allocate_letterno[]" placeholder="Allocate Letter No" required>
                        </div>
                    </div>
                   
                    <label for="dated">Date:</label>
                    <input type="date" id="dated" name="dated" placeholder="Date" required>

                    <input type="submit" name="submit" value="Submit Request">
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
      <script>
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
    
        function toggleOtherCompanyInput() {
            const companySelect = document.getElementById("company_name");
            const otherCompanyInput = document.getElementById("other_company");

            if (companySelect.value === "other") {
                otherCompanyInput.style.display = "block";
            } else {
                otherCompanyInput.style.display = "none";
            }
        }
        function updateQuantity() {
    var company_name = document.getElementById("company_name").value;
    var model_name = document.getElementById("model_name").value;

    if (company_name !== "" && model_name !== "") {
        $.ajax({
            url: "new_allocation.php", // Ensure this is the correct file
            method: "POST",
            data: {
                ajax: "get_quantity",
                company_name: company_name,
                model_name: model_name
            },
            success: function(response) {
                document.getElementById("quantity").value = response.trim(); // Ensure no extra spaces
                calculateRemainingQuantity(); // Recalculate remaining quantity
            }
        });
    }
}

        // Function to calculate remaining quantity based on allocated quantity
        function calculateRemainingQuantity() {
            var quantity = document.getElementById("quantity").value;
            var allocated_quantity = document.getElementById("allocated_quantity").value;
            var remaining_quantity = quantity - allocated_quantity;
        
            if (!isNaN(remaining_quantity)) {
                document.getElementById("remaining_quantity").value = remaining_quantity;
            }
        }
    </script>
</body>
</html>