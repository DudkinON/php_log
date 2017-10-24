# HTTP simple statistic

This script provides fast processing Apache HTTP combined log with saving to MySQL database. 

And display: 

1. Amount of successful connections, amount errors connections, and their totals. 
2. The top ten most popular links with their percents. 
3. The top ten most popular user agents with their percents.

[![demo](https://github.com/DudkinON/php_log/php_log_preview.png?raw=true)](php_log_preview.png)

## Installation

Clone this script with GIT:

```
git clone https://github.com/DudkinON/php_log
```

in your project folder edit Apache config file **httpd.conf** or
 **httpd-vhosts.conf** (if you use virtual hosts) add following code in 
 **VirtualHost**:
```
 LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-agent}i\"" combined
 CustomLog "/path/to/php_log/access_log" combined
```
> Note: edit **$LOG_FILE** if you changed name of Apache log file to something different from
**access_log**.

> Note: If you already have Apache log file with same **combined** **LogFormat**
You can also copy the log to script folder and rename same name with **$LOG_FILE**

> Note: After processing, the log file **will be rewrite** with null value. If you want, 
you can save copy of log file into different folder.

## Usage

In the script file, change the following variables: $host, $db_name, $user, $password with your
database connection data.

For using this script, provide access to folder php log. This script have to be 
available on url: www.example.com/php_log if you did not change the folder name. Click on 
update log link, after that you will see the views data

> Note: if you do not see the reports after clicking on the update log link, change
 in the script file the following line:

```
ini_set('display_errors', 0);
```
on 
```$xslt
ini_set('display_errors', 1);
```
for display errors.

## License

[MIT](LICENSE)