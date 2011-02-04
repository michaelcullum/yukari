# Yukari

## Available Yukari Configurations

### Language

* *language.file_dir* - string - The directory to load language files from, no trailing slash.
* *language.default_locale* - string - The locale to use by default, for pulling language entries from. (e.g. en-US, fr-FR, en)

### UI

* *ui.output_level* - string - How much information should Yukari output?
* *ui.enable_colors* - boolean - Enable use of text colors in UI output, if possible?

### Socket

* *socket.use_ssl* - boolean - Do we want to connect via SSL, or just plain old TCP?
* *irc.url* - string - The server we are connecting to.
* *irc.nickname* - string - IRC nickname to use.
* *irc.password* - string - A password to connect to the specified server with, if we need one.
* *irc.port* - integer - The port on the remote server to connect to.
* *irc.username* - string - IRC username to use (note: NOT nickname, username).
* *irc.realname* - string - IRC realname to use.

### Dispatcher

* *dispatcher.listeners* - array - Array of listener callables to register with the dispatcher on runtime startup.  Each listener entry should be an array containing the event type to register to and the listener callback information (and optionally, any additional parameters to supply the listener with)
