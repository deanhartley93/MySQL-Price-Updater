<?php

//Create connection
define('SKYSQL_HOST', 'master-data-store.mdb0002405.db.skysql.net');
define('SKYSQL_USER', 'DB00005284');
define('SKYSQL_PASS', ',1ltCD4rMh41C/LB4,aCGsWN5Wf');
define('SKYSQL_DB', 'workshop_dean_h');
define('SKYSQL_PORT', 5001);
define('SKYSQL_SSL', 'skysql_chain.pem');

// START SKYSQL CONNECTION
$db = mysqli_init();
if (!$db) {
    die("mysqli_init failed");
}

$db->ssl_set('', '', SKYSQL_SSL, NULL, NULL);
$db->real_connect(SKYSQL_HOST, SKYSQL_USER, SKYSQL_PASS, SKYSQL_DB, SKYSQL_PORT);


// Check connection
if ($db->connect_errno) {
    echo "Failed to connect to SkySQL: " . $db->connect_errno . " \n";
    exit();
}



//uploading file
if (isset($_POST['Import'])) {

    // Allowed mime types
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

    // Validate whether selected file is a CSV file
    if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)) {

        // If the file is uploaded
        if (is_uploaded_file($_FILES['file']['tmp_name'])) {

            // Open uploaded CSV file
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

            // Skip the first line
            fgetcsv($csvFile);

            // Parse data from CSV file line by line
            while (($line = fgetcsv($csvFile)) !== FALSE) {

                //Check if any of these are missing- itemId, price, startDate, priceLevel
                if (!$line[0] == NULL or !$line[1] == NULL or !$line[2] == NULL or !$line[3] == NULL) {
                    // Get row data
                    $itemId   = $line[0];
                    $price  = $line[1];
                    $startDate  = $line[2];
                    $endDate = $line[3];
                    $memo = $line[4];
                    $priceLevel = $line[5];
                    $categoryManager = $line[6];

                    //Check if itemID exists in the table
                    $sql = "SELECT itemId FROM `test`";
                    $results = $db->query($sql);
                    if ($row != $itemId) {
                        // Insert item data in the database
                        $db->query("INSERT INTO test (itemId, price, startDate, endDate, memo, priceLevel, categoryManager) VALUES ('" . $itemId . "', '" . $price . "', '" . $startDate . "', '" . $endDate . "', '" . $memo . "', '" . $priceLevel . "','" . $categoryManager . "')");
                    } else {
                        // Update member data in the database
                        $db->query("UPDATE test SET itemId = '" . $itemId . "', price = '" . $price . "', startDate = '" . $startDate . "', endDate = '" . $endDate . "', memo = '" . $memo . "', priceLevel = '" . $priceLevel . "', categoryManager = '" . $categoryManager . "'");
                    }
                } else {
                    echo '<script>alert("Required Fields Missing from File!")</script>';
                }
            }


            // Close opened CSV file
            fclose($csvFile);
        } else {
            $qstring = '?status=err';
        }
    } else {
        $qstring = '?status=invalid_file';
    }
}


// Export from database 
if (isset($_POST['Export'])) {
    $query = $db->query("SELECT * FROM test ORDER BY itemId ASC");

    if ($query->num_rows > 0) {
        $delimiter = ",";
        $filename = "pricing_export" . date('Y-m-d') . ".csv";

        // Create a file pointer 
        $f = fopen('php://memory', 'w');

        // Set column headers 
        $fields = array('itemId', 'price', 'startDate', 'endDate', 'memo', 'priceLevel', 'categoryManager');
        fputcsv($f, $fields, $delimiter);

        // Output each row of the data, format line as csv and write to file pointer 
        while ($row = $query->fetch_assoc()) {
            $status = ($row['status'] == 1) ? 'Active' : 'Inactive';
            $lineData = array($row['itemId'], $row['price'], $row['startDate'], $row['endDate'], $row['memo'], $row['priceLevel'], $row['categoryManager']);
            fputcsv($f, $lineData, $delimiter);
        }

        // Move back to beginning of file 
        fseek($f, 0);

        // Set headers to download file rather than displayed 
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        //output all remaining data on a file pointer 
        fpassthru($f);
    }
}
