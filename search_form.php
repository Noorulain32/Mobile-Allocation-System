
<?php
include 'conn.php';
include 'header.php';

// Handle Delete Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete']) && $_POST['delete'] == '1') {
    $requestId = $_POST['request_id'];

    // Prepare and execute the DELETE query
    $sql = "DELETE FROM request_form WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $stmt->close();

    // Optionally redirect to the same page to reflect changes
    header("Location: search_form.php");
    exit();
}

// Handle Edit Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit']) && $_POST['edit'] == '1') {
    $requestId = $_POST['request_id'];
    $newValue = $_POST['new_value']; // Modify as needed for your form fields

    // Prepare and execute the UPDATE query
    $sql = "UPDATE request_form SET some_field = ? WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $stmt->bind_param("si", $newValue, $requestId);
    $stmt->execute();
    $stmt->close();

    // Optionally redirect to the same page to reflect changes
    header("Location: search_form.php");
    exit();
}

// Fetch Records for Display
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchDate = $_POST['searchDate'];
    $searchLetterNo = $_POST['searchLetterNo'];

    $sql = "SELECT rf.*, fu.letterfile, fu.letterno 
            FROM request_form rf 
            LEFT JOIN file_upload fu ON rf.request_id = fu.request_id 
            WHERE rf.dated = ? AND fu.letterno = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    $stmt->bind_param("ss", $searchDate, $searchLetterNo);
    $stmt->execute();
    $result = $stmt->get_result();

    $searchResults = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <link rel="stylesheet" href="table.css">
    <!-- <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        table {
            width: 100%; /* Ensure table spans full width */
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .edit-form {
            display: none;
            background-color: #f9f9f9;
            padding: 10px;
            margin-top: 10px;
        }
        .edit-form input[type="text"], 
        .edit-form input[type="date"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        .edit-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .action-link {
            color: #008CBA;
            text-decoration: none;
            cursor: pointer;
            margin-right: 10px;
        }
        .delete-form {
            display: inline;
        }
        .file-link {
            color: #0000EE;
            text-decoration: none;
        }

        /* Ensure container displays children vertically */
        .container {
            padding-top: 0;
            margin-left: 60px;
            display: flex;
            flex-direction: column; /* Stack children vertically */
            align-items: center;
            overflow: hidden;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
            padding-top: 50px;
        }

        .form-container img {
            width: 200px;
            height: auto;
            margin-bottom: 10px;
            margin-top: -5px;
        }

        .form-container label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }

        .form-container select,
        .form-container input[type="text"],
        .form-container input[type="date"],
        .form-container input[type="file"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-container input[type="submit"] {
            width: calc(100% - 20px); /* Make the width consistent with other input fields */
            padding: 10px;
            background-color: rgb(62, 89, 207);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .form-container input[type="submit"]:hover {
            background-color: rgb(50, 70, 160);
        }

        .results-container {
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px; /* Optional: Limit max width if needed */
            margin-left: auto;
            margin-right: auto;
        }
    </style> -->
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img src="images/iesco_logo.png" alt="Logo">
            <form action="search_form.php" method="post">
                <h2>Search</h2>
                <label for="searchDate">Date:</label>
                <input type="date" id="searchDate" name="searchDate" required>

                <label for="searchLetterNo">Letter No.:</label>
                <input type="text" id="searchLetterNo" name="searchLetterNo" required>

                <input type="submit" value="Search">
            </form>
        </div>
        <?php if (isset($searchResults) && !empty($searchResults)) : ?>
            <div class="results-container">
                <table>
                    <thead>
                        <tr>
                            <th>Circle Name</th>
                            <th>Division</th>
                            <th>Sub Division</th>
                            <th>Date</th>
                            <th>Other Offices</th>
                            <th>Letter No.</th>
                            <th>File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $result) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['circlename']); ?></td>
                                <td><?php echo htmlspecialchars($result['division']); ?></td>
                                <td><?php echo htmlspecialchars($result['subdivision']); ?></td>
                                <td><?php echo htmlspecialchars($result['dated']); ?></td>
                                <td><?php echo htmlspecialchars($result['otheroffices']); ?></td>
                                <td><?php echo htmlspecialchars($result['letterno'] ?? 'N/A'); ?></td>
                                <td><a href="<?php echo htmlspecialchars($result['letterfile']); ?>" target="_blank">View File</a></td>
                                <td>
                                    <a href="javascript:void(0);" onclick="showEditForm('<?php echo htmlspecialchars($result['request_id']); ?>')" class="action-link">Edit</a>
                                    <form class="delete-form" method="post" onsubmit="return confirm('Are you sure you want to delete this record?');" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($result['request_id']); ?>">
                                        <input type="hidden" name="delete" value="1">
                                        <a href="javascript:void(0);" onclick="this.closest('form').submit();" class="action-link">Delete</a>
                                    </form>

                                    <!-- Example of an Edit Form -->
                                    <div id="edit-form-<?php echo htmlspecialchars($result['request_id']); ?>" class="edit-form">
                                        <form method="post" action="search_form.php">
                                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($result['request_id']); ?>">
                                            <label for="new_value">New Value:</label>
                                            <input type="text" id="new_value" name="new_value" required>
                                            <input type="hidden" name="edit" value="1">
                                            <input type="submit" value="Update">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function showEditForm(requestId) {
        // Locate the form associated with the requestId and make it visible
        var form = document.getElementById('edit-form-' + requestId);
        if (form) {
            form.style.display = 'block';
        }
    }
    </script>
</body>
</html>