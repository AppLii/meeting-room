<?php

$pdo = new PDO('sqlite:/var/db/rsv_app.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "<h1>--- テーブル: $table ---</h1>";
    $sql = "SELECT * FROM $table";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echoDataAsTable($result);
}


function echoDataAsTable(array $pdo_assoc): void {
	if (!$pdo_assoc) {
		echo 'No Entry';
		return;
	}

	echo '<table>' . PHP_EOL;
	echo '<thead>' . PHP_EOL;
	echo '<tr>' . PHP_EOL;

	foreach ($pdo_assoc[0] as $key => $value) {
		echo "\t<th>" . htmlspecialchars($key) . "</th>" . PHP_EOL;
	}

	echo '</tr>' . PHP_EOL;
	echo '</thead>' . PHP_EOL;
	echo '<tbody>' . PHP_EOL;

	foreach ($pdo_assoc as $row) {
		echo '<tr>' . PHP_EOL;
		foreach ($row as $value) {
			echo "\t<td>" . htmlspecialchars($value) . "</td>" . PHP_EOL;
		}
		echo '</tr>' . PHP_EOL;
	}

	echo '</tbody>' . PHP_EOL;
	echo '</table>' . PHP_EOL;
}
