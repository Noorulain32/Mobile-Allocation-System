<?php
include('conn.php'); // Database connection
// Function to fetch total meter readers
function fetchTotalMeterReaders($conn, $circle = '', $division = '', $subdivision = '') {
    $query = "SELECT 
                SUM(total_meter_reader) as total_meter_readers
              FROM record
              WHERE 1=1";

    if ($circle) {
        $query .= " AND circle = ?";
    }
    if ($division) {
        $query .= " AND division = ?";
    }
    if ($subdivision) {
        $query .= " AND sub_division = ?";
    }

    $stmt = $conn->prepare($query);
    
    $types = '';
    $params = [];
    if ($circle) {
        $types .= 's';
        $params[] = $circle;
    }
    if ($division) {
        $types .= 's';
        $params[] = $division;
    }
    if ($subdivision) {
        $types .= 's';
        $params[] = $subdivision;
    }

    if ($types) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $stmt->bind_result($total_meter_readers);
    $stmt->fetch();
    $stmt->close();

    return $total_meter_readers ? $total_meter_readers : 0;
}

$circleTotal = 0;
$divisionTotal = 0;
$subdivisionTotal = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Capture form data
    $other_offices = trim($_POST['otheroffices']);
    $circle = trim($_POST['circlename']);
    $circle_code = trim($_POST['circleCode']);
    $division = trim($_POST['division']);
    $division_code = trim($_POST['divisionCode']);
    $subdivision = trim($_POST['subdivision']);
    $subdivision_code = trim($_POST['subdivisionCode']);
    $meter_reader = trim($_POST['meter_reader']);

    // Capture "Other" values
    $other_circle_name = trim($_POST['other_circle_name']);
    $other_circle_code = trim($_POST['other_circle_code']);
    $other_division_name = trim($_POST['other_division_name']);
    $other_division_code = trim($_POST['other_division_code']);
    $other_subdivision_name = trim($_POST['other_subdivision_name']);
    $other_subdivision_code = trim($_POST['other_subdivision_code']);

    // Validate form data
    if (empty($circle) && empty($other_circle_name)) $errors[] = "Circle is required.";
    if (empty($division) && empty($other_division_name)) $errors[] = "Division is required.";
    if (empty($subdivision) && empty($other_subdivision_name)) $errors[] = "Sub Division is required.";
    if (empty($meter_reader) || !is_numeric($meter_reader)) $errors[] = "Total Meter Reader is required and must be a number.";

    if (count($errors) == 0) {
        // Determine values to insert
        $insert_circle = ($circle === 'other') ? $other_circle_name : $circle;
        $insert_circle_code = ($circle === 'other') ? $other_circle_code : $circle_code;
        $insert_division = ($division === 'other') ? $other_division_name : $division;
        $insert_division_code = ($division === 'other') ? $other_division_code : $division_code;
        $insert_subdivision = ($subdivision === 'other') ? $other_subdivision_name : $subdivision;
        $insert_subdivision_code = ($subdivision === 'other') ? $other_subdivision_code : $subdivision_code;

        // Insert the main form data into the record table
        $sql = "INSERT INTO record (other_office, circle, circle_code, division, division_code, sub_division, sub_division_code, total_meter_reader) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssss", $other_offices, $insert_circle, $insert_circle_code, $insert_division, $insert_division_code, $insert_subdivision, $insert_subdivision_code, $meter_reader);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['message'] = "Record added successfully.";
                } else {
                    $errors[] = "Failed to add record. Affected rows: " . $stmt->affected_rows;
                }
            } else {
                $errors[] = "Failed to execute statement: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errors[] = "Failed to prepare statement: " . $conn->error;
        }
    }

    if (count($errors) > 0) {
        $_SESSION['errors'] = $errors;
    }

    header("Location: record.php"); // Redirect to the form page
    exit();
}elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $circle = isset($_GET['circle']) ? trim($_GET['circle']) : '';
    $division = isset($_GET['division']) ? trim($_GET['division']) : '';
    $subdivision = isset($_GET['subdivision']) ? trim($_GET['subdivision']) : '';

    $circleTotal = fetchTotalMeterReaders($conn, $circle);
    $divisionTotal = fetchTotalMeterReaders($conn, $circle, $division);
    $subdivisionTotal = fetchTotalMeterReaders($conn, $circle, $division, $subdivision);
}
?>



<!-- html code -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <style>
    .form-container {
    max-width: 800px;
    margin: auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.form-group {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.form-group label {
    flex: 1;
    margin-right: 10px;
    font-weight: bold;
}

.form-group input,
.form-group select {
    flex: 2;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group input[type="text"] {
    width: 100%;
}

.form-section {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    background-color: #fff;
    border-radius: 8px;
}

.form-group.double {
    flex-direction: row;
    align-items: center;
}

.form-group.double .form-group-item {
    flex: 1;
    margin-right: 10px;
}

.form-group.double:last-of-type .form-group-item {
    margin-right: 0;
}

.total-readers {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.total-readers .form-group {
    flex: 1;
    margin-right: 10px;
}

.total-readers .form-group:last-of-type {
    margin-right: 0;
}

.additional-field {
    display: none; /* Initially hidden */
}

/* .additional-field .form-group {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
} */

.additional-field .form-group label {
    margin-right: 10px;
}

.additional-field .form-group input {
    flex: 2;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 10px
}

input[type="submit"] {
    background-color: #3448A1;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
    font-size: 16px;
}

input[type="submit"]:hover {
    background-color: #2a3b7d;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-size: 16px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

img {
    max-width: 100%;
    height: auto;
    margin-bottom: 20px;
}

</style>


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

<div id="requestForm">
                <form action="record.php" method="post" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="otheroffices">Other Offices:</label>
                        <input type="text" id="otheroffices" name="otheroffices" placeholder="Other offices">
                    </div>

                    <div class="form-group double">
                        <div class="form-group-item">
                            <label for="circlename">Circle:</label>
                            <select name="circlename" id="circlename" onchange="updateDivisions(); toggleOtherField('circle', this.value); fetchTotals();" required>
                                <option value="">Select Circle</option>
                                <option value="islamabad">Islamabad</option>
                                <option value="attock">Attock</option>
                                <option value="rawalpindi city">Rawalpindi City</option>
                                <option value="jhelum">Jhelum</option>
                                <option value="chakwal">Chakwal</option>
                                <option value="rawalpindi cantt">Rawalpindi Cantt</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group-item">
                            <label for="circleCode">Circle Code:</label>
                            <input type="text" id="circleCode" name="circleCode" placeholder="Circle Code">
                        </div>
                    </div>

                    <div id="other_circle" class="additional-field">
                        <div class="form-group">
                            <label for="other_circle_name">New Circle Name:</label>
                            <input type="text" id="other_circle_name" name="other_circle_name" placeholder="Enter New Circle">
                        <!-- </div>
                        <div class="form-group"> -->
                            <label for="other_circle_code">New Circle Code:</label>
                            <input type="text" id="other_circle_code" name="other_circle_code" placeholder="Enter New Circle Code">
                        </div>
                    </div>

                    <div class="form-group double">
                        <div class="form-group-item">
                            <label for="division">Division:</label>
                            <select id="division" name="division" onchange="updateSubDivisions(); toggleOtherField('division', this.value); fetchTotals();" required>
                                <option value="">Select Division</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group-item">
                            <label for="divisionCode">Division Code:</label>
                            <input type="text" id="divisionCode" name="divisionCode" placeholder="Division Code">
                        </div>
                    </div>

                    <div id="other_division" class="additional-field">
                        <div class="form-group">
                            <label for="other_division_name">New Division Name:</label>
                            <input type="text" id="other_division_name" name="other_division_name" placeholder="Enter New Division">
                        <!-- </div>
                        <div class="form-group"> -->
                            <label for="other_division_code">New Division Code:</label>
                            <input type="text" id="other_division_code" name="other_division_code" placeholder="Enter New Division Code">
                        </div>
                    </div>

                    <div class="form-group double">
                        <div class="form-group-item">
                            <label for="subdivision">Sub Division:</label>
                            <select id="subdivision" name="subdivision" onchange="toggleOtherField('subdivision', this.value); fetchTotals();" required>
                                <option value="">Select Sub Division</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group-item">
                            <label for="subdivisionCode">Sub Division Code:</label>
                            <input type="text" id="subdivisionCode" name="subdivisionCode" placeholder="Sub Division Code">
                        </div>
                    </div>

                    <div id="other_subdivision" class="additional-field">
                        <div class="form-group">
                            <label for="other_subdivision_name">New Sub Division Name:</label>
                            <input type="text" id="other_subdivision_name" name="other_subdivision_name" placeholder="Enter New Sub Division">
                        <!-- </div>
                        <div class="form-group"> -->
                            <label for="other_subdivision_code">New Sub Division Code:</label>
                            <input type="text" id="other_subdivision_code" name="other_subdivision_code" placeholder="Enter New Sub Division Code">
                        </div>
                    </div>

                    <label for="meter_reader">Total Meter Reader:</label>
                    <input type="text" id="meter_reader" name="meter_reader" placeholder="Total Meter Reader" onchange="calculateMeterReaders()">
                  <!-- Total Meter Readers Display -->
                  <div class="total-readers">
                        <div class="form-group">
                            <label for="totalMeterReadersCircle">Circle Total:</label>
                            <input type="text" id="totalMeterReadersCircle" value="<?php echo htmlspecialchars($circleTotal); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="totalMeterReadersDivision">Division Total:</label>
                            <input type="text" id="totalMeterReadersDivision" value="<?php echo htmlspecialchars($divisionTotal); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="totalMeterReadersSubDivision">Sub Division Total:</label>
                            <input type="text" id="totalMeterReadersSubDivision" value="<?php echo htmlspecialchars($subdivisionTotal); ?>" readonly>
                        </div>
                    </div>

                    <input type="submit" name="submit" value="Submit Request">
                </form>
            </div>
        </div>
    </div>

    <script>
        
        const divisions = {
            islamabad: ["Islamabad I", "Islamabad II", "Bara Kahu"],
            attock: ["Taxila", "Attock", "Pindi Gheb"],
            "rawalpindi city": ["Satellite Town", "Rawalpindi City", "Westridge"],
            jhelum: ["Jhelum I", "Jhelum II", "Gujjar Khan"],
            chakwal: ["Talagang", "Chakwal", "Pind Dadan Khan", "Dhudhail"],
            "rawalpindi cantt": ["Rawalpindi Cantt", "Tariqabad", "Rawat", "Mandra"]
        };

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
        function fetchTotals() {
    const circle = document.getElementById('circlename').value;
    const division = document.getElementById('division').value;
    const subdivision = document.getElementById('subdivision').value;

    const url = `record.php?circle=${encodeURIComponent(circle)}&division=${encodeURIComponent(division)}&subdivision=${encodeURIComponent(subdivision)}`;

    fetch(url)
        .then(response => response.text())
        .then(text => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            
            document.getElementById('totalMeterReadersCircle').value = doc.querySelector('#totalMeterReadersCircle').value || 0;
            document.getElementById('totalMeterReadersDivision').value = doc.querySelector('#totalMeterReadersDivision').value || 0;
            document.getElementById('totalMeterReadersSubDivision').value = doc.querySelector('#totalMeterReadersSubDivision').value || 0;
        })
        .catch(error => console.error('Error fetching totals:', error));
}


        function updateDivisions() {
    const circle = document.getElementById("circlename").value;
    const divisionSelect = document.getElementById("division");

    // Clear current options
    divisionSelect.innerHTML = '<option value="">Select Division</option>';

    if (divisions[circle]) {
        divisions[circle].forEach(division => {
            const option = document.createElement("option");
            option.value = division;
            option.text = division;
            divisionSelect.add(option);
        });
    }

    // Add "Other" option at the end
    const otherOption = document.createElement("option");
    otherOption.value = "other";
    otherOption.text = "Other";
    divisionSelect.add(otherOption);
    updateSubDivisions();
    fetchTotals();
}

function updateSubDivisions() {
    const division = document.getElementById("division").value;
    const subDivisionSelect = document.getElementById("subdivision");

    // Clear current options
    subDivisionSelect.innerHTML = '<option value="">Select Sub Division</option>';

    if (subDivisions[division]) {
        subDivisions[division].forEach(subDivision => {
            const option = document.createElement("option");
            option.value = subDivision;
            option.text = subDivision;
            subDivisionSelect.add(option);
        });
    }

    // Add "Other" option at the end
    const otherOption = document.createElement("option");
    otherOption.value = "other";
    otherOption.text = "Other";
    subDivisionSelect.add(otherOption);
    fetchTotals();
}

function toggleOtherField(fieldType, value) {
            const otherFieldMap = {
                'circle': 'other_circle',
                'division': 'other_division',
                'subdivision': 'other_subdivision'
            };
            const otherFieldId = otherFieldMap[fieldType];
            if (value === 'other') {
                document.getElementById(otherFieldId).style.display = 'block';
            } else {
                document.getElementById(otherFieldId).style.display = 'none';
            }
        }


        function calculateMeterReaders() {
    const meterReaderInput = parseInt(document.getElementById('meter_reader').value) || 0;

    if (isNaN(meterReaderInput)) {
        alert('Please enter a valid number for Total Meter Reader.');
        return;
    }

    // Update total meter readers
    document.getElementById('totalMeterReadersCircle').value = meterReaderInput; 
    document.getElementById('totalMeterReadersDivision').value = meterReaderInput; 
    document.getElementById('totalMeterReadersSubDivision').value = meterReaderInput;
}


</script>
</body>
</html>