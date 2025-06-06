<?php

session_start();

echo "<h1>Debug Autentikasi Admin DocTo</h1>";
echo "<p>File ini membantu mendiagnosis masalah pada autentikasi admin.</p>";

function debug_var($var_name, $var_value) {
    echo "<strong>$var_name:</strong> ";
    
    if (!isset($var_value)) {
        echo "<span style='color:red'>tidak terdefinisi</span>";
    } else if ($var_value === "") {
        echo "<span style='color:orange'>string kosong</span>";
    } else if ($var_value === true) {
        echo "<span style='color:green'>true (boolean)</span>";
    } else if ($var_value === false) {
        echo "<span style='color:red'>false (boolean)</span>";
    } else if ($var_value === "1") {
        echo "<span style='color:blue'>\"1\" (string)</span>";
    } else if ($var_value === 1) {
        echo "<span style='color:purple'>1 (integer)</span>";
    } else if (is_array($var_value)) {
        echo "<span style='color:blue'>Array: " . print_r($var_value, true) . "</span>";
    } else {
        echo "<span style='color:blue'>" . htmlspecialchars($var_value) . " (" . gettype($var_value) . ")</span>";
    }
    
    echo "<br>";
}

echo "<h2>Status Session:</h2>";
debug_var('session_id', session_id());
debug_var('session_status', session_status());

echo "<h2>Variabel Session:</h2>";
if (empty($_SESSION)) {
    echo "<p style='color:red'>Tidak ada variabel session yang aktif.</p>";
} else {
    echo "<ul>";
    foreach ($_SESSION as $key => $value) {
        echo "<li>";
        debug_var($key, $value);
        echo "</li>";
    }
    echo "</ul>";
}

echo "<h2>Status Admin:</h2>";
debug_var('$_SESSION[\'username\']', isset($_SESSION['username']) ? $_SESSION['username'] : null);
debug_var('$_SESSION[\'is_admin\']', isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : null);

$admin_check = (!isset($_SESSION['username']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']);
echo "<p>Hasil pengecekan admin: " . ($admin_check ? "<span style='color:red'>GAGAL</span>" : "<span style='color:green'>SUKSES</span>") . "</p>";

echo "<h2>Form Test Login Admin:</h2>";
echo "<form method='post' action=''>";
echo "<input type='hidden' name='set_admin' value='true'>";
echo "<input type='submit' value='Set Session Admin'>";
echo "</form>";

if (isset($_POST['set_admin'])) {
    $_SESSION['username'] = 'admin_test';
    $_SESSION['is_admin'] = true;
    echo "<p style='color:green'>Session admin telah diatur. Silakan refresh halaman untuk melihat perubahan.</p>";
    echo "<script>window.location.reload();</script>";
}

echo "<h2>Informasi PHP:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";

echo "<h2>Reset Session:</h2>";
echo "<form method='post' action=''>";
echo "<input type='hidden' name='reset_session' value='true'>";
echo "<input type='submit' value='Reset Session' style='background-color:#ff0000; color:white;'>";
echo "</form>";

if (isset($_POST['reset_session'])) {
    session_unset();
    session_destroy();
    echo "<p style='color:orange'>Session telah dihapus. Silakan refresh halaman.</p>";
    echo "<script>window.location.reload();</script>";
}
?>