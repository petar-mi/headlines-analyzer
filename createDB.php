<?php

try {
    // Try Connect to the DB with new MySqli object - Params {hostname, userid, password, dbname}
    $link = new mysqli("localhost", "root", "novasifra", "demo");
} catch (mysqli_sql_exception $e) { // Failed to connect? Lets see the exception details..
    echo "MySQLi Error Code: " . $e->getCode() . "<br />";
    echo "Exception Msg: " . $e->getMessage();
    exit; // exit and close connection.
}

//No Exceptions were thrown, we connected successfully, yay!
echo "Success, we connected without failure! <br />";
echo "Connection Info: " . mysqli_get_host_info($link) . PHP_EOL;

// $sql = "CREATE DATABASE demo";
// if(mysqli_query($link, $sql)){
//     echo "Database created successfully";
// } else{
//     echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
// }

// ****************************************

// $sql = "DROP DATABASE demo";
// if(mysqli_query($link, $sql)){
//     echo "Database deleted successfully";
// } else{
//     echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
// }

// ****************************************

// $sql2 = "CREATE TABLE MyGuests (
//     id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     firstname VARCHAR(30) NOT NULL,
//     lastname VARCHAR(30) NOT NULL,
//     email VARCHAR(50),
//     reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
//     )";

//     if ($link->query($sql2) === TRUE) {
//         echo "Table MyGuests created successfully";
//     } else {
//         echo "Error creating table: " . $link->error;
//     };

// ****************************************

// $sql = "INSERT INTO MyGuests (firstname, lastname, email)
//     VALUES ('John', 'Doe', 'john@example.com')";

// if ($link->query($sql) === TRUE) {
//     echo "New record created successfully";
// } else {
//     echo "Error: " . $sql . "<br>" . $link->error;
// }

// ****************************************

// // prepare and bind
// $stmt = $link->prepare("INSERT INTO MyGuests (firstname, lastname, email) VALUES (?, ?, ?)");
// $stmt->bind_param("sss", $firstname, $lastname, $email);

// // set parameters and execute
// $firstname = "Delon";
// $lastname = "Dobri";
// $email = "dd@example.com";
// $stmt->execute();

// $firstname = "Mary";
// $lastname = "Moe";
// $email = "mary@example.com";
// $stmt->execute();

// $firstname = "Julie";
// $lastname = "Dooley";
// $email = "julie@example.com";
// $stmt->execute();

// echo "New records created successfully";

// $stmt->close();

// ****************************************

echo "<br />";
// $sql = "SELECT id, firstname, lastname FROM MyGuests WHERE lastname='Doe'";
// $sql = "DELETE FROM MyGuests WHERE id=3";
// $sql = "UPDATE MyGuests SET lastname='Doe' WHERE id=2";
// $sql = "SELECT * FROM Orders LIMIT 10 OFFSET 15"; // return only 10 records, start on record 16 (OFFSET 15)
$sql = "SELECT id, firstname, lastname FROM MyGuests WHERE id>4 ORDER BY lastname";


$result = $link->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
    }
} else {
    echo "0 results";
}
/* free result set */
$result->free();

mysqli_close($link); // finally, close the connection
