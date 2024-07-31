<?php
include('header.php');
include('conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otheroffices = $_POST['otheroffices'];
    $circlename = $_POST['circlename'];
    $division = $_POST['division'];
    $subdivision = $_POST['subdivision'];
    $imei_no = $_POST['imei_no'];
    $meter_reader = $_POST['meter_reader'];
    $previous_mobile = $_POST['previous_mobile'];
    $company_name = $_POST['company_name'];
    $model_name = $_POST['model_name'];
    $dated = $_POST['dated'];
    $remarks = $_POST['remarks'];

    // Insert data into request_form table
    $sql = "INSERT INTO against_damage (otheroffices, circlename, division, subdivision, imei_no, meter_reader, previous_mobile, company_name, model_name, dated, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,? )";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Bind parameters and execute query
    $stmt->bind_param("sssssssssss", $otheroffices, $circlename, $division, $subdivision, $imei_no, $meter_reader, $previous_mobile, $company_name, $model_name, $dated, $remarks);
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

    if (isset($_FILES["letterfile"])) {
        foreach ($_FILES["letterfile"]["name"] as $key => $name) {
            if (!empty($name)) {
                $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $formattedDate = date("Y-m-d", strtotime($dated));
                $damage_letterno = $_POST['letterno'][$key]; // Get the unique letter number for each file
                $filename = "{$damage_letterno}" . $formattedDate . "$key." . $fileType;
                $target_dir = "uploads/against_damage/" . date("Y", strtotime($dated)) . "/" . date("d-m-Y", strtotime($dated)) . "/{$subdivision}/{$damage_letterno}/";
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
                        $sql = "INSERT INTO damage_files (damage_id, damage_letter, damage_letterno) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);

                        if ($stmt === false) {
                            die('MySQL prepare error: ' . $conn->error);
                        }

                        // Bind parameters and execute query
                        $stmt->bind_param("iss", $request_id, $uploadletter, $damage_letterno);
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
    }

    // Insert new mobile information into allocation table
    $n_company_name = $_POST['n_company_name'];
    $n_model_name = $_POST['n_model_name'];
    $allocated_quantity = $_POST['allocated_quantity'];
    $dated = $_POST['dated'];
    $otheroffices = $_POST['otheroffices'];
    $circlename = $_POST['circlename'];
    $division = $_POST['division'];
    $subdivision = $_POST['subdivision'];
    
    $sql = "INSERT INTO allocation (other_office, circle, division, sub_division, company_name, model_name, allocated_quantity, dated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    
    // Bind parameters and execute query
    $stmt->bind_param("ssssssis", $otheroffices, $circlename, $division, $subdivision, $n_company_name, $n_model_name, $allocated_quantity, $dated);
    
    if ($stmt->execute()) {
        echo "<script>alert('New mobile information stored successfully.'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to store new mobile information.');</script>";
    }



    
    $stmt->close();
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Against Damage</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">

   
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img src="images/iesco_logo.png" alt="Logo">
            <label><h1>Against Damage</h1></label>
            <div id="againstForm">
                <form action="against_damage.php" method="post" enctype="multipart/form-data">
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

                    <h2>Damaged Mobile</h2>
                    <label for="imei_no">IMEI No:</label>
                    <input type="text" id="imei_no" name="imei_no" placeholder="IMEI No" required>

                    <label for="meter_reader">Employee Name:</label>
                    <input type="text" id="meter_reader" name="meter_reader" placeholder="Name" required>

                
                    <label for="remarks">Remarks:</label>
                    <input type="text" id="remarks" name="remarks" placeholder="Remarks" required>
                   
                    <!-- <label for="quantity">Quantity:</label>
                    <input type="text" id="quantity" name="quantity" readonly required>
                    <label for="remaining_quantity">Remaining Quantity:</label>
                    <input type="text" id="remaining_quantity" name="remaining_quantity" readonly required>
                    -->
                    <label for="company_name">Company Name</label>
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

                    <label for="model_name">Model Name</label>
                    <input type="text" id="model_name" name="model_name" placeholder="Model Name" required>

                    <h2>New Mobile</h2>

                    <label for="n_company_name">Company Name</label>
                    <select name="n_company_name" id="n_company_name" placeholder="Company Name" required onchange="toggleNOtherCompanyInput()">
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

                    <div id="n_other_company" style="display:none;">
                    <label for="n_other_company_name">Other Company Name:</label>
                    <input type="text" id="n_other_company_name" name="n_other_company_name" placeholder="Enter Other Company Name">
                    </div>

                    <label for="model_name">Model Name</label>
                    <input type="text" id="model_name" name="model_name" placeholder="Model Name" required>



                    <label for="allocated_quantity">Allocated Quantity:</label>
                    <input type="text" id="allocated_quantity" name="allocated_quantity" placeholder="Allocated Quantity" required>

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

        function toggleNOtherCompanyInput() {
            const companySelect = document.getElementById("n_company_name");
            const otherCompanyInput = document.getElementById("n_other_company");

            if (companySelect.value === "other") {
                otherCompanyInput.style.display = "block";
            } else {
                otherCompanyInput.style.display = "none";
            }
        }
        function updateQuantity() {
            const company_name = document.getElementById("company_name").value;
            const model_name = document.getElementById("model_name").value;

            if (company_name && model_name) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "new_allocation.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                        const quantity = xhr.responseText;
                        document.getElementById("quantity").value = quantity;
                        // Optionally calculate and update remaining quantity
                        // Example: document.getElementById("remaining_quantity").value = quantity - allocated_quantity;
                    }
                };
                
                const params = "company_name=" + encodeURIComponent(company_name) + "&model_name=" + encodeURIComponent(model_name);
                xhr.send(params);
            } else {
                // Handle case when company_name or model_name is not selected
                // Optionally clear or reset the quantity fields
                document.getElementById("quantity").value = ""; // Clear quantity field
                // Optionally clear remaining_quantity field
            }
        }
    </script>
</body>
</html>