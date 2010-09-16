# Failnet -- PHP-based IRC Bot

## Available Failnet Configurations

### Language

* *language.file_dir* - string - The directory to load language files from, no trailing slash.

### UI

* *ui.output_level* - string - How much information should Failnet output?
* *ui.enable_colors* - boolean - Enable use of text colors in UI output, if possible?

### Socket

* *server.server_uri* **REQUIRED** - string - The server we are connecting to.
* *server.nickname* **REQUIRED** - string - IRC nickname to use.
* *socket.use_ssl* - boolean - Do we want to connect via SSL, or just plain old TCP?
* *server.server_pass* - string - A password to connect to the specified server with, if we need one.
* *server.port* - integer - The port on the remote server to connect to.
* *server.username* - string - IRC username to use (note: NOT nickname, username).
* *server.realname* - string - IRC realname to use.

### Dispatcher

* *dispatcher.listeners* - array - Array of listener callables to register with the dispatcher on runtime startup.  Each listener entry should be an array containing the event type to register to and the listener callback information (and optionally, any additional parameters to supply the listener with)
