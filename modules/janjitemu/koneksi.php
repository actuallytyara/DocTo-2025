<?php
$host = 'localhost';       
$username = 'root';       
$password = '';          
$database = 'login';     

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

function closeConnection($connection) {
    if ($connection) {
        mysqli_close($connection);
    }
}

function escapeString($connection, $string) {
    return mysqli_real_escape_string($connection, $string);
}

function executeQuery($connection, $query) {
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        error_log("Database Error: " . mysqli_error($connection));
        return false;
    }
    
    return $result;
}

function fetchSingleRow($connection, $query) {
    $result = executeQuery($connection, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

function fetchAllRows($connection, $query) {
    $result = executeQuery($connection, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    return [];
}

?>