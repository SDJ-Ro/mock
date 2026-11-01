<?php

// Applies the database updates that haven't run yet: every database/updates/NNN_name.sql, in number order.
//     php database/update.php        (from the project folder; DB_NAME=… points it at another database)
// An update is one small .sql file per change, e.g. 006_notices.sql — never edit one that has already run;
// add a new numbered file instead.

declare(strict_types=1);

require dirname(__DIR__) . '/core/bootstrap.php';

$db = Core\Database::getConnection();

// The database remembers which files have already run
$db->exec('CREATE TABLE IF NOT EXISTS database_updates (name VARCHAR(191) PRIMARY KEY, ran_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');

$alreadyRan = $db->query('SELECT name FROM database_updates')->fetchAll(PDO::FETCH_COLUMN);
$files      = glob(__DIR__ . '/updates/*.sql');
$ranNow     = 0;

sort($files);   // 001, 002, 003...

foreach ($files as $file)
{
    $name = basename($file);

    if (in_array($name, $alreadyRan, true))
    {
        continue;
    }

    $db->exec(file_get_contents($file));
    $db->prepare('INSERT INTO database_updates (name) VALUES (:name)')->execute(['name' => $name]);

    echo "Ran {$name}\n";
    $ranNow++;
}

echo $ranNow === 0 ? "Database is already up to date.\n" : "Done: {$ranNow} change(s) applied.\n";
