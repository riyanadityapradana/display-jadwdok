<?php
// Test file untuk memeriksa koneksi database
require_once 'config/database.php';

try {
    echo "<h2>Test Koneksi Database</h2>";

    // Test koneksi
    echo "<p>✅ Koneksi database berhasil!</p>";

    // Test query sederhana
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p>✅ Query test berhasil: " . $result['test'] . "</p>";

    // Cek tabel yang ada
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Tabel yang ada di database:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Cek struktur tabel admin
    echo "<h3>Struktur tabel admin:</h3>";
    $stmt = $pdo->query("DESCRIBE admin");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch(PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>