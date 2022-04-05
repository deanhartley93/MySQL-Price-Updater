<!DOCTYPE html>
<html lang="en">

<head>
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
    if (isset($_POST["Import"])) {
        $filename = $_FILES["file"]["tmp_name"];
        if ($_FILES["file"]["size"] > 0) {

            //convert uploaded csv file to associative array and loop through each line
            $csvArr = csv_file_to_array($filename);

            foreach ($csvArr as $line) {

                //check current line for required fields, and format date if needed to y-m-d
                validateLine($line);

                //insert row into DB table
                $db->query("INSERT into test (itemId,price,startDate,endDate,memo,priceLevel,categoryManager)
                    values ('" . $db->real_escape_string($line['itemId']) . "','" . $db->real_escape_string($line['price']) . "','" . $db->real_escape_string($line['startDate']) . "','" . $db->real_escape_string($line['endDate']) . "','" . $db->real_escape_string($line['memo']) . "', '" . $db->real_escape_string($line['priceLevel']) . "','" . $db->real_escape_string($line['categoryManager']) . "')
                    ON DUPLICATE KEY UPDATE
                    price = '" . $db->real_escape_string($line['price']) . "',
                    startDate = '" . $db->real_escape_string($line['startDate']) . "',
                    endDate = '" . $db->real_escape_string($line['endDate']) . "',
                    memo = '" . $db->real_escape_string($line['memo']) . "',
                    priceLevel = '" . $db->real_escape_string($line['priceLevel']) . "',
                    categoryManager = '" . $db->real_escape_string($line['categoryManager']) . "'");
            }
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
        $query = $db->query("SELECT * FROM test ORDER BY itemId ASC");

        if ($query->num_rows > 0) {
            $delimiter = ",";
            $filename = "pricing_export" . "_" . date('Y-m-d') . ".csv";

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
            exit();
        }
    }

    //checks if itemID,price,start Date, and pricelevel are included
    //Also fomats date if needed to y-m-d
    function validateLine($line)
    {

        if (!isset($line['itemId']) and !is_int($line['itemId'])) {
            echo "<script>
            alert('File is missing required item id!');
            window.location.href='app.php';
            </script>";
        }
        if (!isset($line['price'])) {
            echo "<script>
            alert('File is missing required price!');
            window.location.href='app.php';
            </script>";
        }
        if (!isset($line['startDate'])) {
            echo "<script>
            alert('File is missing required start date!');
            window.location.href='app.php';
            </script>";
        }
        if (!isset($line['priceLevel'])) {
            echo "<script>
            alert('File is missing required price level!');
            window.location.href='app.php';
            </script>";
        }

        //format the date
        date_format($line['startDate'], "Y-m-d");
        date_format($line['endDate'], "Y-m-d");

        return $line;
    }

    //display current price changes in database as a table on page
    function get_records($db)
    {
        $result = $db->query("SELECT * FROM test ORDER BY itemId ASC");
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-responsive'><table id='myTable' class='table table-striped table-bordered'>
             <thead><tr><th>Item ID</th>
                          <th>Price</th>
                          <th>Start Date</th>
                          <th>End Date</th>
                          <th>Memo</th>
                          <th>Price Level</th>
                          <th>Category Manager</th>
                        </tr></thead><tbody>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr><td>" . $row['itemId'] . "</td>
                   <td>" . $row['price'] . "</td>
                   <td>" . $row['startDate'] . "</td>
                   <td>" . $row['endDate'] . "</td>
                   <td>" . $row['memo'] . "</td>
                   <td>" . $row['priceLevel'] . "</td>
                   <td>" . $row['categoryManager'] . "</td></tr>";
            }

            echo "</tbody></table></div>";
        } else {
            echo "There are no current price changes in the database";
        }
    }
    ?>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Importer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
</head>

<body>

    <div class="container p-4">

        <div class="mx-auto w-50 p-3 bg-light text-body text-center rounded mb-5 border">
            <h3>Download the template</h3>
            <a href="Price Changes.xlsx"><button>Download</button></a>
        </div>


        <!-- Import Form -->

        <div class="border mx-auto w-50 p-3 bg-light text-center text-body rounded mb-5">
            <form action="app.php" method="post" name="upload_excel" enctype="multipart/form-data">
                <p>
                <h3>Upload Price Changes Sheet</h3>
                (You MUST upload as .csv)
                </p>
                <fieldset>
                    <p>
                        <input type="hidden" name="uploaded" value="true" />
                        <input type="file" name="file" id="file" />
                        <button type="submit" id="submit" name="Import" class="btn btn-primary">Upload</button>
                    </p>
                </fieldset>
            </form>
        </div>

        <div class="mx-auto w-50 text-center">
            <a href="http://www.quadratec.net">Return to Quadranet</a>
        </div>

        <div class="mx-auto w-50 p-3 bg-light text-center text-body rounded mb-5 border">
            <form action="app.php" method="post" name="upload_excel" enctype="multipart/form-data">
                <fieldset>
                    <div class="">
                        <button type="submit" id="submit" name="Export" class="btn btn-primary">Export</button><br />

                    </div>
                    <?php get_records($db); ?>
                </fieldset>
            </form>
        </div>



</body>

</html>



<?php

function csv_file_to_array($filepath)
{
    // This function returns an array of new orders from the local truecommerce.order database

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
                $array[$i][$fields[$k]] = $value;
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
