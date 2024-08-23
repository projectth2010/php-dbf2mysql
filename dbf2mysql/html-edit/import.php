<?php
// Increase memory limit
ini_set('memory_limit', '256M');

// Function to import data from .dbf file to MySQL table
function importDataFromDBF($conn, $dbfPath, $tableName) {
    // Open the .dbf file
    $dbf = dbase_open($dbfPath, 0);
    if (!$dbf) {
        die("Error: Failed to open .dbf file");
    }

    // Get number of records in the .dbf file
    $recordCount = dbase_numrecords($dbf);

    // Iterate through records and insert into MySQL table
    for ($i = 1; $i <= $recordCount; $i++) {
        $row = dbase_get_record_with_names($dbf, $i);

        // Build the SQL INSERT statement
        $sql = "INSERT INTO $tableName (";
        foreach ($row as $key => $value) {
            if ($key !== 'deleted') { // Skip the 'deleted' flag
                $sql .= "`$key`, ";
            }
        }
        $sql .= "version_timestamp, source_file_name) VALUES (";
        foreach ($row as $key => $value) {
            if ($key !== 'deleted') { // Skip the 'deleted' flag
                $sql .= "'" . addslashes($value) . "', ";
            }
        }
        $sql .= "NOW(), '" . basename($dbfPath) . "')";

        // Execute the SQL statement
        if (!$conn->query($sql)) {
            die("Error inserting data: " . $conn->error);
        }
    }

    echo "Data imported successfully";

    // Close the .dbf file
    dbase_close($dbf);
}

// Check if filename and table name are provided in the query string
if(isset($_GET["filename"]) && isset($_GET["tablename"])) {
    $fileName = $_GET["filename"];
    $tableName = $_GET["tablename"];

    // Define database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "password99";
    $dbname = "yasupada_mefixdb";

    // Create MySQL connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check MySQL connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Import data from .dbf file to MySQL table
    importDataFromDBF($conn, $fileName, $tableName);

    // Close MySQL connection
    $conn->close();
} else {
    echo "Error: Filename and tablename are required in the query string.";
}
?>
