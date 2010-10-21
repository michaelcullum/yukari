# Failnet -- PHP-based IRC Bot

## Exception code reservations

* 0 - **Failnet\FailnetException** - */src/Exception.php*
* 100xx - **Failnet\StartupException** - */src/Exception.php*
* 101xx - **Failnet\AutoloadException** - */src/Exception.php*
* 102xx - **Failnet\EnvironmentException** - */src/Exception.php*
* 202xx - **Failnet\Connection\SocketException** - */src/Connection/SocketException.php*
* 203xx - **Failnet\CLI\UIException** - */src/CLI/UIException.php*
* 204xx - **Failnet\Language\ManagerException** - */src/Language/ManagerException.php*
* 205xx - **Failnet\Language\CompilerException** - */src/Language/CompilerException.php*
* 206xx - **Failnet\Cron\ManagerException** - */src/Cron/ManagerException.php*
* 207xx - **Failnet\Session\ManagerException** - */src/Session/ManagerException.php*
* 208xx - **Failnet\Addon\LoaderException** - */src/Addon/LoaderException.php*
* 300xx - **Failnet\Lib\HostmaskException** - */src/Lib/HostmaskException.php*
* 301xx - **Failnet\Lib\JSONException** - */src/Lib/JSONException.php*
* 400xx - **Failnet\Cron\Task\TaskException** - */src/Cron/Task/TaskException.php*
