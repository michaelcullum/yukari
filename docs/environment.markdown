# Yukari

## Yukari Environment - Object storage

The following is a list of the slots that are in use within the Yukari kernel.

* **core.timezone** - object(DateTimeZone) - The timezone to use across Yukari by default.
* **core.start** - object(DateTime) - The DateTime object representing when Yukari was started.
* **core.autoload** - object(\Yukari\Autoload) - Yukari's autoloader object, used for "just-in-time" class loading when a class is not yet defined.
* **core.cli** - object(\Yukari\CLI\CLIArgs) - Yukari's CLI Arg parser, which handles $argv input.
* **core.ui** - object(\Yukari\CLI\UI) - Yukari's "user interface", used to handle output to the terminal.
* **core.language** - object(\Yukari\Language\Manager) - The language manager, handles localization for IRC interactions.
* **core.hash** - object(\Yukari\Lib\Hash) - Yukari's adaptation of the phpass password hashing system.
* **core.dispatcher** - object(\Yukari\Event\Dispatcher) - Yukari's event dispatcher.
* **core.session** - object(\Yukari\Session\Manager) - Yukari's session handler.
* **core.socket** - object(\Yukari\Connection\Socket) - Socket connection handler, dispatches and recieves data directly from the socket.
