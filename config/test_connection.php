<?php
/**
 * Quick connection test — open in browser after importing the schema:
 * http://localhost/pos_system/config/test_connection.php
 *
 * Delete this file after confirming the connection works.
 */

require_once __DIR__ . '/db.php';

$result = $conn->query('SELECT DATABASE() AS db_name, NOW() AS server_time');

if ($result) {
    $row = $result->fetch_assoc();
    echo '<h2>Database connection successful</h2>';
    echo '<p>Connected to: <strong>' . htmlspecialchars($row['db_name']) . '</strong></p>';
    echo '<p>Server time: ' . htmlspecialchars($row['server_time']) . '</p>';

    $tables = $conn->query("SHOW TABLES");
    echo '<h3>Tables in pos_db:</h3><ul>';
    while ($table = $tables->fetch_row()) {
        echo '<li>' . htmlspecialchars($table[0]) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<h2>Connection OK but query failed</h2>';
    echo '<p>' . htmlspecialchars($conn->error) . '</p>';
}

$conn->close();
