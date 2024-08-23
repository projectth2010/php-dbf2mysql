<?php
// Increase memory limit
ini_set('memory_limit', '256M');

// Check if form is submitted
if(isset($_POST["submit"])) {
    // Check if file was uploaded without errors
    if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $fileName = $_FILES["file"]["name"];
        $fileTmpName = $_FILES["file"]["tmp_name"];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check if the uploaded file is a .dbf file
        if($fileExt == "dbf") {
            // Define database connection parameters
            $servername = "localhost";
            $username = "yasupada_mefixdb";
            $password = "mefixdb123456!";
            $dbname = "yasupada_mefixdb";

            // Create MySQL connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check MySQL connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Read file structure and create MySQL table
            $dbfPath = $fileTmpName;
            $tableName = pathinfo($fileName, PATHINFO_FILENAME);
            createTableFromDBF($conn, $dbfPath, $tableName);

            // Close MySQL connection
            $conn->close();
        } else {
            echo "Error: Please upload a .dbf file.";
        }
    } else {
        echo "Error: No file uploaded or an error occurred while uploading the file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload .dbf File</title>
</head>
<body>
    <h2>Upload .dbf File</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".dbf">
        <button type="submit" name="submit">Upload File</button>
    </form>
    

    <?php if(isset($_POST["submit"])): ?>
    <!-- Display next step button to import data -->
    <a href="import.php?filename=<?php echo $fileName; ?>&tablename=<?php echo $tableName; ?>">Next Step - Import Data</a>
    <?php endif; ?>

</body>
</html>

<?php
// Function to create MySQL table from .dbf file
function createTableFromDBF($conn, $dbfPath, $tableName) {
    // Open the .dbf file
    $dbf = dbase_open($dbfPath, 0);
    if (!$dbf) {
        die("Error: Failed to open .dbf file");
    }

    // Get header information
    $header = dbase_get_header_info($dbf);
    if ($header === false) {
        die("Error: Failed to get header information from .dbf file");
    }

    // Construct SQL query to create table
    $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
    foreach ($header as $field) {
        $mysqlType = '';
        switch ($field['type']) {
            case 'character':
                $mysqlType = 'VARCHAR(255)';
                break;
            case 'number':
                $mysqlType = 'INT';
                break;
            case 'date':
                $mysqlType = 'DATE';
                break;
            default:
                $mysqlType = 'VARCHAR(255)';
        }
        $sql .= "`{$field['name']}` $mysqlType, ";
    }
    $sql .= "version_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
    $sql .= "source_file_name VARCHAR(255))";

    // Execute SQL query
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }

    echo "Table $tableName created successfully";

    // Close the .dbf file
    dbase_close($dbf);
}
?>
