<?php include 'importer.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Importer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
</head>

<body>

    <div class="container-fluid p-4">

        <div class="mx-auto w-50 p-3 bg-light text-body text-center rounded-pill mb-5 border">
            <h3>Download the template</h3>
            <a href=" test.csv"><button>Download</button></a>
        </div>


        <!-- Import Form -->

        <div class="border mx-auto w-50 p-3 bg-light text-center text-body rounded-pill mb-5">
            <form action="importer.php" method="post" name="upload_excel" enctype="multipart/form-data">
                <p>
                <h3>Upload Price Changes Sheet</h3>
                (You MUST upload as .csv)
                </p>
                <fieldset>
                    <p>
                        <label class="control-label" for="filebutton"></label>
                        <input type="file" name="file" id="file" class="input-large">
                        <button type="submit" id="submit" name="Import" class="btn btn-primary">Upload</button>
                    </p>

                </fieldset>
            </form>
        </div>


        <div class="mx-auto w-50 p-3 bg-light text-center text-body rounded-pill mb-5 border">
            <form action=" importer.php" method="post" name="upload_excel" enctype="multipart/form-data">
                <fieldset>
                    <!-- Form Name -->
                    <h3>Download Current Price Changes</h3>
                    <!-- Button -->

                    <label class="control-label" for="singlebutton">Download</label>
                    <div class="">
                        <button type="submit" id="submit" name="Export" class="btn btn-primary">Export CSV</button>
                    </div>
                </fieldset>
            </form>
        </div>

        <div class="mx-auto w-50 text-center">
            <a href="http://www.quadratec.net">Return to Quadranet</a>

        </div>

</body>

</html>