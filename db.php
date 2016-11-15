<?php
/**
 * Created by PhpStorm.
 * User: Pashka
 * Date: 11/15/2016
 * Time: 12:17 PM
 */

$config = parse_ini_file(dirname(__FILE__)."/config.ini");

$connection = null;

try {
    $connection = @new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",
        $config['dbuser'],
        $config['dbpass'],
        array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
    );
}
catch (PDOException $e) {
    die ("An error occurred while connecting to the database. The error reported by the server was: " . $e->getMessage());
}
if (isset($_REQUEST['method'])) { $method = $_REQUEST['method']; }

$tableExist = false;
$tableEmpty = true;

try {
    $result = $connection->query("SELECT 1 FROM `tree` LIMIT 1");
    $tableExist = true;
    if ($result->rowCount() > 0) $tableEmpty = false;
} catch (Exception $e) {
}

function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}

function addMokupRecord ($name, $pid = 0) {
    global $connection;
    $status = rand(0,4);
    $url = rus2translit($name);
    $k = rand(1,20);
    $result = $connection->query("INSERT INTO `tree` (`pid`, `name`, `status`, `url`, `k`)
    VALUES ($pid, '$name', $status, '$url', $k)");
    if ($result) {
        return $connection->lastInsertId();
    } else die($result->errorInfo());
}

switch ($method) {
        case 'setup':
            $result = $connection->query("CREATE TABLE `tree` ( 
`id` int(11) NOT NULL AUTO_INCREMENT,
`pid` int(11) NOT NULL DEFAULT 0,
`name` varchar(255) NOT NULL,
`status` int(1),
`url` varchar(253) NOT NULL,
`k` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`)
)
");
            if ($result) {
                header("Location: ?");
            } else {
                echo $result->errorInfo();
            }
            break;
    case 'data-add':
        $rootID = addMokupRecord('Главная');

        $nodeID = addMokupRecord('JavaScript', $rootID);
        addMokupRecord('Sting', $nodeID);
        addMokupRecord('Array', $nodeID);
        addMokupRecord('Object', $nodeID);
        addMokupRecord('Math', $nodeID);

        $nodeID = addMokupRecord('CSS', $rootID);
        $subNodeID = addMokupRecord('Селекторы', $nodeID);
        addMokupRecord('ID', $subNodeID);
        addMokupRecord('CLASS', $subNodeID);
        addMokupRecord(':HOVER', $subNodeID);
        addMokupRecord('Media queries', $nodeID);

        $nodeID = addMokupRecord('PHP', $rootID);
        addMokupRecord('PDO', $nodeID);
        addMokupRecord('Mail', $nodeID);

        $nodeID = addMokupRecord('Mysql', $rootID);
        addMokupRecord('SELECT', $nodeID);
        addMokupRecord('UPDATE', $nodeID);
        addMokupRecord('INSERT', $nodeID);

        header("Location: ?");

        break;
    case 'data-del':
        $result = $connection->query("DELETE FROM `tree`");
        if ($result) {
            header("Location: ?");
        } else {
            echo $result->errorInfo();
        }
        break;
    case 'clean':
        $result = $connection->query("DROP TABLE `tree`");
        if ($result) {
            header("Location: ?");
        } else {
        echo $result->errorInfo();
        }
        break;
        default:
            echo '<h1>DB tools</h1>';
            if (!$tableExist) echo '<a href="?method=setup">Setup table</a><br />';
            if ($tableExist && $tableEmpty) echo '<a href="?method=data-add">Fill with mokup data</a><br />';
            if ($tableExist && !$tableEmpty) echo '<a href="?method=data-del">Clean mokup data</a><br />';
            if ($tableExist) echo '<a href="?method=clean">Remove table</a><br />';
            break;
}
