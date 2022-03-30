<?php include 'importer.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Importer</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
</head>

<body>

    <div id="wrap">
        <div class="container">
            <div class="row">
                <form class="form-horizontal" action="importer.php" method="post" name="upload_excel" enctype="multipart/form-data">
                    <fieldset>

                        <legend>Download the template</legend>
                        <p>
                        <h4>Download here: </h4><a href="test.csv"><button>Download</button></a>
                        </p>
                        <!-- Import Form -->
                        <legend>Upload Price Changes Sheet </legend>
                        <div class="form-group">
                            <label class="control-label" for="filebutton">Choose File</label>
                            <div class="">
                                <input type="file" name="file" id="file" class="input-large">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="singlebutton">Upload: </label>
                            <div class="">
                                <button type="submit" id="submit" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Import</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="row">
                <form class="form-horizontal" action="importer.php" method="post" name="upload_excel" enctype="multipart/form-data">
                    <fieldset>
                        <!-- Form Name -->
                        <legend>Download Current Price Changes</legend>
                        <!-- Button -->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="singlebutton">Download</label>
                            <div class="col-md-4">
                                <button type="submit" id="submit" name="Export" class="btn btn-primary button-loading">Export CSV</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
</body>

</html>