<?php
/**
 * Created by PhpStorm.
 * User: Pashka
 * Date: 11/15/2016
 * Time: 2:05 PM
 */

$config = parse_ini_file(dirname(__FILE__)."/config.ini");
$connection = null;

$order = 'id';
if (isset($_REQUEST['order'])) {
    $order = $_REQUEST['order'];
    if (!in_array($order, ['id', 'name', 'k'])) $sort = 'id';
}
$sort = 'desc';
if (isset($_REQUEST['sort'])) {
    $sort = $_REQUEST['sort'];
    if (!in_array($sort, ['desc', 'asc'])) $sort = 'desc';
}
$sortInverted = ($sort == 'desc')?'asc':'desc';

function nodeHasChildren($id = 0) {
    global $connection;

    $result = $connection->query("SELECT * FROM `tree` WHERE pid=$id");
    return $result->rowCount() > 0;
}

function getSortedNodes($pid = 0, $level = 0) {
    global $connection, $order, $sort;
    $ret = [];

    $result = $connection->query("SELECT * FROM `tree` WHERE pid=$pid ORDER BY $order $sort");
    foreach ($result as $row) {
        $hasChildren = nodeHasChildren($row['id']);
        $ret[] = ['id' => $row['id'], 'name' => $row['name'], 'k' => $row['k'], 'url' => $row['url'], 'level' => $level, 'hasChildren' => $hasChildren];
        if ($hasChildren) {
            $ret = array_merge($ret, getSortedNodes($row['id'], $level + 1));
        }
    }
    return $ret;
}

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

?>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <script src="jquery-3.1.1.min.js"></script>
    <script>
        jQuery(document).ready(function () {
            $('.editable').dblclick(function (e) {
                target = $(e.target);
                oldValue = $(target).html();
                $(target).html("");
                xedit = $("<input type='text' value='" + oldValue + "' data-id='" + target.data('id') + "' data-field='" + target.data('field') + "'/>").appendTo(target);
                $(xedit).focus();
                $(xedit).focusout(function (e) {
                    $.ajax({
                        url: "api.php?m=update&id=" + $(e.target).data('id') + "&field=" + $(e.target).data('field') + "&value=" + e.target.value,
                        context: this
                    })
                    .done(function (data, text, xhr) {
                        console.log('ok!', data, text, xhr);
                        $(target).html(data.value);
                        if (data.url != null) $(target).parent().children().find('a').attr('href', data.url);
                    })
                    .fail(function (xhr, status) {
                        console.log('fail:', xhr, status);
                    });
                    $(this).remove();
                });
            });
        });
    </script>
</head>
<body>
<?php

try {
    $result = $connection->query("SELECT 1 FROM `tree` LIMIT 1");
} catch (Exception $e) {
    echo "Таблица БД не проинициализированна. <a href='db.php'>DB Tools</a>";
}

echo "<table>
<tr>
<th><a href='?order=id&sort=$sortInverted'>id</a>&nbsp;</th>
<th><a href='?order=name&sort=$sortInverted'>name</a>&nbsp;</th>
<th><a href='?order=k&sort=$sortInverted'>k</a>&nbsp;</th>
<th>url</th>
</tr>";

foreach (getSortedNodes() as $row) {
    $class = $row['hasChildren']?'node':'doc';
    echo "
<tr>
<td>{$row['id']}</td>
<td class='editable $class' style='padding-left: " . $row['level'] * 16 . "px' data-id='{$row['id']}' data-field='name'>{$row['name']}</td>
<td class='editable' data-id='{$row['id']}' data-field='k'>{$row['k']}</td>
<td><a href='{$row['url']}'>url</a></td>
</tr>";
}
echo "
</table>
";
?>
</body>
</html>

