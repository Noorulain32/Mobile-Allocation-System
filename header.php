<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<ul>
    <li><a href="dashboard.php"><img src="images/iesco_logo.png" alt="Logo"></a></li>

    <li class="dropdown">    
    <a href="#" class="dropbtn">Request</a>
        <div class="dropdown-content">
            <a href="request_form.php">Request Form</a>
            <a href="noting_form.php">Noting</a>
            <a href="search_form.php">Search</a>
        </div>

        <li class="dropdown">
        <a href="#" class="dropbtn">Allocation</a>
        <div class="dropdown-content">
            <a href="new_allocation.php">New Allocation</a>
            <a href="against_damage.php">Against Damage</a>
            <a href="view_allocation.php">View Allocation</a>
        </div>


    <li class="dropdown">
        <a href="#" class="dropbtn">Stock</a>
        <div class="dropdown-content">
            <a href="stock.php">Add Stock</a>
            <a href="view_stock.php">View Stock</a>
        </div>
    </li>
    <li class="dropdown">
        <a href="#" class="dropbtn">Record</a>
        <div class="dropdown-content">
            <a href="record.php">Add Record</a>
            <a href="view_record.php">View Record</a>
        </div>
    <li><a href="reporting.php">Report</a></li>
    <li><a href="login.php">Log out</a></li>
</ul>
</body>
</html>