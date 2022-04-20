<?php
//Create connection

define('SKYSQL_HOST', '');
define('SKYSQL_USER', '');
define('SKYSQL_PASS', ',');
define('SKYSQL_DB', '');
define('SKYSQL_PORT', );
define('SKYSQL_SSL', '');

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
if (isset($_POST["Import"])) {
    $filename = $_FILES["file"]["tmp_name"];
    if ($_FILES["file"]["size"] > 0) {

        //convert uploaded csv file to associative array and loop through each line
        $csvArr = csv_file_to_array($filename);
        //purchase price query
        lineQuery($db, $csvArr, 'Purchase Price');
        //selling price query
        lineQuery($db, $csvArr, 'Selling Price');
        //club price query
        lineQuery($db, $csvArr, 'Club Price');
        //dealer price query
        lineQuery($db, $csvArr, 'Dealer Price');
        //jobber price query
        lineQuery($db, $csvArr, 'Jobber Price');
        //retail map query
        lineQuery($db, $csvArr, 'Retail Map');
        //MSRP query
        lineQuery($db, $csvArr, 'MSRP');
        //wholesale map query
        lineQuery($db, $csvArr, 'Wholesale Map');
        //if uploaded successfully
        success();
    } else {
        echo "<script>
            alert('Please select a file!');
            window.location.href='app.php';
            </script>";
    }
}

// Create a csv from database
if (isset($_POST['Export'])) {

    $query = $db->query("SELECT * FROM priceChanges GROUP BY itemId ASC");

    if ($query->num_rows > 0) {
        $delimiter = ",";
        $filename = "pricing_export" . "_" . date('Y-m-d') . ".csv";

        // Create a file pointer
        $f = fopen('php://memory', 'w');

        // Set column headers
        $fields = array('Internal ID', 'Purchase Price', 'Selling Price', 'Club Price', 'Dealer Price', 'Jobber Price', 'Retail Map', 'MSRP', 'Wholesale Map', 'Start Date', 'End Date', 'Notes');
        fputcsv($f, $fields, $delimiter);

        // Output each row of the data, format line as csv and write to file pointer
        while ($row = $query->fetch_assoc()) {

            //Price Level queries
            $purchaseQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'Purchase Price' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());
            $sellingQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'Selling Price' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());
            $clubQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'Club Price' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());
            $dealerQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'Dealer Price' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());
            $jobberQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'Jobber Price' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());
            $mapQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'Retail Map' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());
            $msrpQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'MSRP' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());
            $wholesaleQuery = ($db->query("SELECT price FROM priceChanges WHERE priceLevel = 'Wholesale Map' and itemId = '" . $row['itemId'] . "' ")->fetch_assoc());

            //compile row of data in csv
            $lineData = array($row['itemId'], $purchaseQuery['price'], $sellingQuery['price'], $clubQuery['price'], $dealerQuery['price'], $jobberQuery['price'], $mapQuery['price'], $msrpQuery['price'], $wholesaleQuery['price'], $row['startDate'], $row['endDate'], $row['memo']);

            fputcsv($f, $lineData, $delimiter);
        }

        // Move back to beginning of filef
        fseek($f, 0);
        // Set headers to download file rather than displayed
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        //output all remaining data on a file pointer
        fpassthru($f);
        exit();
    }
}

//inserts or updates DB
function lineQuery($db, $csvArr, $pricingLevel)
{
    //remove dollar sign and commas from prices
    $replace = ['$', ','];

    //This loop traverses each line of the uploaded csv file
    foreach ($csvArr as $line) {
        //check current line for required fields, and format date if needed to y-m-d
        validateLine($line);

        //format date to y-m-d
        $line['Start Date'] = date('Y-m-d', strtotime($line['Start Date']));
        $line['End Date'] = date('Y-m-d', strtotime($line['Start Date']));

        //insert or update data to SkySQL
        $sql = "INSERT into priceChanges (itemId,price,priceLevel,startDate,endDate,memo)
                    values ('" . $db->real_escape_string($line['Internal ID']) . "','" . $db->real_escape_string(str_replace($replace, '', $line[$pricingLevel])) . "', '" . $db->real_escape_string($pricingLevel) . "' ,'" . $db->real_escape_string($line['Start Date']) . "',";

        // Used to correctly pass NULL to SQL if no end date, memo, or category manager set
        $sql .= (empty($line['End Date'])) ? " NULL, " : "'" . $db->real_escape_string($line['End Date']) . "',";
        $sql .= "'" . $db->real_escape_string($line['Notes']) . "')
                    ON DUPLICATE KEY UPDATE
                    itemId = '" . $db->real_escape_string($line['Internal ID']) . "',
                    price = '" . $db->real_escape_string(str_replace($replace, '', $line[$pricingLevel])) . "',
                    priceLevel = '" . $db->real_escape_string($pricingLevel) . "',
                    startDate = '" . $db->real_escape_string($line['Start Date']) . "',";
        $sql .= (empty($line['End Date'])) ?  " endDate = NULL," : " endDate = '" . $db->real_escape_string($line['End Date']) . "',";
        $sql .= "memo = '" . $db->real_escape_string($line['Notes']) . "'";
        $db->query($sql);
    }
}

//checks if itemID,price,start Date, and pricelevels are included
function validateLine($line)
{
    //check for internal ID
    if (empty($line['Internal ID'])) {
        echo "<script>
            alert('File is missing required internal id!');
            window.location.href='app.php';
            </script>";
        die();
    }

    //check for start date
    if (empty($line['Start Date'])) {
        echo "<script>
            alert('File is missing required start date!');
            window.location.href='app.php';
            </script>";
        die();
    }

    //array of all price levels to check for
    $priceLevelCheck = [$line['Purchase Price'], $line['Selling Price'], $line['Club Price'], $line['Dealer Price'], $line['Jobber Price'], $line['Retail Map'], $line['MSRP'], $line['Wholesale Map']];

    //counter for how many levels are null. Count has to be >0 to be valid (meaning at least one price field is filled in)
    $count = 0;
    //Check that at least 1 price level is included in csv
    foreach ($priceLevelCheck as $price) {
        if (!is_null($price)) {
            $count++;
        }
    }

    if ($count < 1) {
        echo "<script>
            alert('File is missing some required prices!');
            window.location.href='app.php';
            </script>";
        die();
    }
}

//This function takes in a csv file and generates an associative array
function csv_file_to_array($filepath)
{

    $array = $fields = array();
    $i = 0;
    $bom = "\xef\xbb\xbf";
    $handle = @fopen($filepath, "r");


    // We need to check for the BOM at the beginning of the file. If it doesn't exist, rewind
    if (fgets($handle, 4) !== $bom) {
        // BOM not found - rewind pointer to start of file.
        rewind($handle);
    }

    if ($handle) {
        while (($row = fgetcsv($handle, 4096)) !== false) {

            if (empty($fields)) {
                $fields = $row;
                continue;
            }
            foreach ($row as $k => $value) {
                //checks to see if csv uploaded has empty cells that are present
                if (!empty($value)) {
                    $array[$i][trim($fields[$k])] = $value;
                }
            }
            $i++;
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }

    return $array;
}

function success()
{
    echo "<script>
            alert('File uploaded successfully!');
            window.location.href='app.php';
            </script>";
}

?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadratec Price Importer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
</head>

<body style="background: #d7d7d7">
    <div class=" container p-4">

        <!-- Import Form -->

        <div class="border border-success mx-auto w-50 p-3 bg-light text-center text-body rounded mb-5">
            <form action="app.php" method="post" name="upload_excel" enctype="multipart/form-data">
                <p>
                <h4>Upload Price Changes</h4>
                <p>Download the template file here: <a href="Price Update Template.xlsb">Download</a></p>

                <hr>
                </p>
                <fieldset>
                    <h6>(You MUST upload as .csv)</h6>
                    <p>
                        <input type="hidden" name="uploaded" value="true" />
                        <input type="file" name="file" id="file" />
                        <button type="submit" id="submit" name="Import" class="btn btn-success">Upload</button>
                    </p>
                </fieldset>
            </form>
        </div>

        <!-- Export Form -->
        <div class="mx-auto w-50 p-3 bg-light text-center text-body rounded mb-5 border border-success">
            <form action="app.php" method="post" name="upload_excel" enctype="multipart/form-data">
                <fieldset>
                    <div>
                        <h5>Export Current Price Change Database</h5>
                        <button type="submit" id="export" name="Export" class="btn btn-success">Export</button>
                    </div>
                </fieldset>
            </form>
        </div>

        <div class="mx-auto w-50 text-center">
            <a href="http://www.quadratec.net" target="_blank" rel="noopener noreferrer">Return to Quadranet</a>
        </div>
</body>

</html>
