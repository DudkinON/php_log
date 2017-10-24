<?php
/**
 * Created by Oleg Dudkin.
 * Project: php_log
 * File name: index.php
 * Date: 10/20/2017
 * Time: 10:33 PM
 */


define('MAX_ROWS', 100);

// display errors
ini_set('display_errors', 0);
error_reporting(E_ALL);


// Define time zone
date_default_timezone_set('UTC');
$LOG_FILE = 'access_log';
$ERRORS = false; // Errors list


function get_connection()
{
    /**
     * TODO: Access to mysql database. Return PDO object or false
     */
    $host = '127.0.0.1';
    $db_name = 'access_log';
    $user = 'log_app';
    $password = 'Strong_password';
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $opt = [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true,
    ];
    try {
        $db = new PDO($dsn, $user, $password, $opt);
        $db->exec("set names utf8");
        return $db;
    } catch (Exception $e) {
        $ERRORS[] = 'Bad connection';
        return false;
    }
}


function save_log($data)
{
    /**
     *  TODO: Saving log into database
     */
    $db = get_connection();
    try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->beginTransaction();
        foreach ($data as $elem) {
            $uri = $elem['uri'];
            $method = $elem['method'];
            $date = $elem['date'];
            $user_agent = $elem['user_agent'];
            $status = $elem['status'];
            $ip = $elem['ip'];
            $db->exec("INSERT INTO `log` (`id`, `uri`, `method`, `user_agent`, `status`, `date`, `ip`) 
                                 VALUES (NULL, '{$uri}', '{$method}', '{$user_agent}', '{$status}', '{$date}', '{$ip}')");
        }
        $db->commit();
        return true;
    } catch (Exception $e) {
        $ERRORS[] = $e;
        $db->rollBack();
        return false;
    }
}

function convert_log_time($s)
{
    /**
     * TODO: convert the date format from Apache to MySQL
     */
    $s = preg_replace('#:#', ' ', $s, 1);
    $s = str_replace('/', ' ', $s);
    if (!$t = strtotime($s)) return FALSE;
    return date('Y-m-d', $t);
}

function get_log() {
    /**
     * TODO: Read data from Apache v2.4 log file
     */
    global $LOG_FILE, $ERRORS;
    $log = [];
    $handle = @fopen($LOG_FILE, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            preg_match('~(\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})\s.+?\s.+?\s\[(\d{1,2}\/\w{3}\/\d{4})[\d:]+\s[\d-]+\]\s\"([A-Z]+)\s([\S]+)\s(HTTP\/[\d\.]+)\"\s([\d]{3})\s[\S]+\s["-\s]{0,5}(.+)~', $buffer, $matches);
            $date = convert_log_time($matches[2]);
            if (isset($matches[0]) && $date) {
                $log[] = [
                    'ip' => $matches[1],
                    'date' => $date,
                    'method' => $matches[3],
                    'uri' => $matches[4],
                    'status' => $matches[6],
                    'user_agent' => $matches[7]];
            }
            if (count($log) >= MAX_ROWS) {
                save_log($log);
                $log = [];
            }
        }

        // Tests for end-of-file on a file pointer
        if (!feof($handle)) $ERRORS[] = "Error: unexpected fgets() fail\n";

        // Close an open file pointer
        fclose($handle);

        // if errors is none and save_log return true, clearing a log file
        if (save_log($log) && $ERRORS === false) file_put_contents($LOG_FILE, '');
        else echo '<pre>'; var_dump($ERRORS); echo '</pre>';
    }
}

function get_access_by_status_code() {
    /**
     * TODO: Return the amount of connections by status code
     */
    $db=get_connection();

    $result = $db->query('SELECT `status`, COUNT(*) AS `amount` FROM `log` GROUP BY `status`');

    return $result->fetchAll();
}

function get_access_by_uri() {
    /**
     * TODO: Return amount of the most popular uri
     */
    $db=get_connection();

    $result = $db->query('SELECT `uri`, ' .
        'COUNT(uri) / (SELECT COUNT(*) FROM `log`) * 100 AS `percent` ' .
        'FROM `log` ' .
        'GROUP BY `uri` ' .
        'ORDER BY `percent` DESC ' .
        'LIMIT 10');

    return $result->fetchAll();
}

function get_access_by_user_agent() {
    /**
     * TODO: Return amount of the most popular users agents
     */
    $db=get_connection();

    $result = $db->query('SELECT `user_agent`, ' .
        'COUNT(user_agent) / (SELECT COUNT(*) FROM `log`) * 100 AS `percent`' .
        'FROM `log` ' .
        'GROUP BY `user_agent`' .
        'ORDER BY `percent` DESC ' .
        'LIMIT 10');

    return $result->fetchAll();
}

if (isset($_GET['log']) && $_GET['log'] === 'update') get_log();

$access = get_access_by_status_code();
$total = 0;
$successes_access = 0;
$errors_access = 0;
echo '<pre>';
echo '<h3>Status Code Connections</h3>';

foreach ($access as $item) {
    if ($item['status'] === 200) $successes_access += $item['amount'];
    else $errors_access += $item['amount'];
    echo "<b>status code:</b> {$item['status']} - amount: {$item['amount']}\n";
    $total += $item['amount'];
}

echo "<br><b>Total:</b> {$total} <br>";
echo "<b>Successes:</b>  {$successes_access} <br>";
echo "<b>Errors:</b>  {$errors_access} <hr><br>";

$access = get_access_by_uri();

echo '<h3>URI connections</h3>';

foreach ($access as $item) {
    echo "<b>uri:</b> {$item['uri']} - {$item['percent']}%\n";
}

echo "<br><hr><br>";

$access = get_access_by_user_agent();

echo '<h3>User Agent Percents</h3>';

foreach ($access as $item) {
    echo "<b>user agent:</b> {$item['user_agent']} - {$item['percent']}%<br>";
}

echo "<br><hr><br><a href='/log.php?log=update'>update log</a>";
