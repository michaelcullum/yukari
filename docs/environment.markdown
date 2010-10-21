# Failnet -- PHP-based IRC Bot

## Failnet Environment - Object storage

The following is a list of the slots that Failnet currently uses within the Failnet\Environment object.

* **core.timezone** - object(DateTimeZone) - The timezone to use across Failnet by default.
* **core.start** - object(DateTime) - The DateTime object representing when Failnet was started.
* **core.autoload** - object(Failnet\Autoload) - Failnet's autoloader object, used for "just-in-time" class loading when a class is not available.
* **core.cli** - object(Failnet\CLI\CLIArgs) - Failnet's CLI Arg parser, which handles $argv input.
* **core.ui** - object(Failnet\CLI\UI) - Failnet's user interface, used to handle output to the terminal.
* **core.core** - object(Failnet\Install\Core) - The Failnet installer core. (Deprecated, will change later)
* **core.language** - object(Failnet\Language\Manager) - The language manager, handles localization for IRC interactions.
* **core.hash** - object(Failnet\Lib\Hash) - Failnet's adaptation of the phpass password hashing system.
* **core.dispatcher** - object(Failnet\Event\Dispatcher) - Failnet's event dispatcher.
* **core.session** - object(Failnet\Session\Manager) - Failnet's session handler.
* **core.socket** - object(Failnet\Connection\Socket) - Socket connection handler, dispatches and recieves data directly from the socket.
* **mailer.transport** - object(Swift_SmtpTransport) - The Swiftmailer transport object.
* **mailer.mailer** - object(Swift_Mailer) - The Swiftmailer message handler object.
* **mailer.replacements** - object(Failnet\Mailer\Replacements) - The Failnet replacement lookup object that is used in conjunction with the Swiftmailer Decorator Plugin.
