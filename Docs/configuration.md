# Failnet -- PHP-based IRC Bot

## Available Failnet Configurations

### Language

* *language.file_dir* - string - The directory to load language files from, no trailing slash.

### UI

* *ui.output_level* - string - How much information should Failnet output?
* *ui.enable_colors* - boolean - Enable use of text colors in UI output, if possible?

### Socket

* *socket.use_ssl* - boolean - Do we want to connect via SSL, or just plain old TCP?
* *server.server_pass* - string - A password to connect to the specified server with, if we need one.
* *server.server_uri* - string - The server we are connecting to.
* *server.port* - integer - The port on the remote server to connect to.
* *server.username* - string - IRC username to use (note: NOT nickname, username).
* *server.realname* - string - IRC realname to use.
* *server.nickname* - string - IRC nickname to use.
