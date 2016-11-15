<?php
/**
 * Created by PhpStorm.
 * User: Pashka
 * Date: 11/15/2016
 * Time: 5:18 PM
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
if (isset($_REQUEST['m'])) { $method = $_REQUEST['m']; }
$id = $_REQUEST['id'];
$field = $_REQUEST['field'];
$value = $_REQUEST['value'];


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

header('Content-Type: application/json');

switch($method) {
    case 'update':
        if (!$id || !$field || !$value) {
            echo json_encode(['status' => 'error', 'message' => 'variables not defined']);
        } else {
            if ($field = 'name') $query = "UPDATE `tree` SET $field='$value', url='" . rus2translit($value). "' WHERE id=$id";
            else $query = "UPDATE `tree` SET $field='$value' WHERE id=$id";

            $result = $connection->query($query);
            if ($result) {
                echo json_encode(['status' => 'Ok', 'field' => $field, 'value' => $value]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $result->errorInfo()]);
            }
        }
        break;
    default:
        echo json_encode(['status' => 'error']);
        break;
}

