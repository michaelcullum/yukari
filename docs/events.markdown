# Yukari

## Yukari Environment - Event types

The following is a list of the various event types that can and will be dispatched in the Yukari core.

* *runtime.connect* - Empty event sent upon establishing a connection
* *runtime.tick* - Event sent with tick timestamp for each iteration of the Yukari runtime loop

* *ui.startup* - Empty event fired upon startup, for displaying the startup text in the terminal (and perhaps logging something on startup)
* *ui.message.warning* - A warning UI message (PHP warning, runtime logic problem such as an unexpected setting, etc.) to be displayed or logged.
* *ui.message.system* - A system notice UI message (mainly just backend status change notifications, like loading a core library) that is to be displayed or logged.

* *system.shutdown* - Triggers Yukari's shutdown procedure, which will process all remaining events, dispatch any responses it must, then begin closing down safely.

* *irc.input.privmsg* - The event received when we get a PRIVMSG from the server.
* *irc.input.command.{$command}* - The event dispatched when a privmsg event is found to be a bot command. (e.g. "!somecommand", "Yukari: somecommand", or in a direct PRIVMSG - "somecommand")
* *irc.input.directcommand.{$command}* - The event dispatched when an in-channel privmsg command is directed at us (e.g. "Yukari: somecommand")
* *irc.input.privatecommand.{$command}* - The event dispatched when a command is sent directly to us via privmsg  (e.g. direct PRIVMSG containing "somecommand")
