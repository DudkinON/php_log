<?php
/**
 * Created by Oleg Dudkin.
 * Project: php_log
 * File name: index.php
 * Date: 10/20/2017
 * Time: 10:33 PM
 */


// simple saving data

function getConnection()
{
    $host = '127.0.0.1';
    $db_name = 'access_log';
    $user = 'log_app';
    $password = 'Strong_password';

    $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $password);
    $db->exec("set names utf8");
    return $db;
}
function save_connection()
{
    $db = getConnection();
    $sql = 'INSERT ' .
        'INTO `log` (`id`, `uri`, `method`, `user_agent`, `status`, `date`, `ip`)' .
        ' VALUES (NULL, :uri, :method, :user_agent, :status, :date, :ip)';

    $data = $_SERVER;
    $date = date('Y-m-d H:i:s');
    $result = $db->prepare($sql);
    $result->bindParam(':uri', $data['REQUEST_URI'], PDO::PARAM_STR);
    $result->bindParam(':method', $data['REQUEST_METHOD'], PDO::PARAM_STR);
    $result->bindParam(':user_agent', $data['HTTP_USER_AGENT'], PDO::PARAM_STR);
    $result->bindParam(':status', $data['REDIRECT_STATUS'], PDO::PARAM_INT);
    $result->bindParam(':date', $date, PDO::PARAM_STR);
    $result->bindParam(':ip', $data['REMOTE_ADDR'], PDO::PARAM_STR);
}
save_connection();
