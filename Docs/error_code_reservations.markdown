# Failnet -- PHP-based IRC Bot

## Exception code reservations

* 0 - **Failnet\FailnetException** - */Includes/Exception.php*

* 100xx - **Failnet\StartupException** - */Includes/Exception.php*
* 101xx - **Failnet\AutoloadException** - */Includes/Exception.php*
* 102xx - **Failnet\EnvironmentException** - */Includes/Exception.php*

* 202xx - **Failnet\Core\SocketException** - */Includes/Core/Socket.php*
* 203xx - **Failnet\Core\UIException** - */Includes/Core/UI.php*
* 204xx - **Failnet\Core\LanguageException** - */Includes/Core/Language.php*
* 205xx - **Failnet\Core\CronException** - */Includes/Core/Cron.php*
* 206xx - **Failnet\Core\AuthException** - */Includes/Core/Auth.php*

* 300xx - **Failnet\Lib\HostmaskException** - */Includes/Lib/Hostmask.php*
* 301xx - **Failnet\Lib\JSONException** - */Includes/Lib/JSON.php*

* 400xx - **Failnet\Cron\CronTaskException** - */Includes/Cron/CronBase.php*
