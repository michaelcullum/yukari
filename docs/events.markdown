# Yukari

## Yukari Environment - Event types

The following is a list of the various event types that can and will be dispatched in the Yukari core.

* *runtime.connect* - Empty event sent upon establishing a connection
* *runtime.tick* - Event sent with tick timestamp for each iteration of the Yukari runtime loop

* *ui.startup* - Empty event fired upon startup, for displaying the startup text in the terminal (and perhaps logging something on startup)
* *ui.message.warning* - A warning UI message (PHP warning, runtime logic problem such as an unexpected setting, etc.) to be displayed or logged.
* *ui.message.system* - A system notice UI message (mainly just backend status change notifications, like loading a core library) that is to be displayed or logged.

* *system.shutdown* - Triggers Yukari's shutdown procedure, which will process all remaining events, dispatch any responses it must, then begin closing down safely.
