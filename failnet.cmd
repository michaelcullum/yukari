@ECHO OFF

::
::===================================================================
::
::  Failnet -- PHP-based IRC Bot
::-------------------------------------------------------------------
:: Version:     3.0.0 DEV
:: Copyright:   (c) 2009 - 2010 -- Damian Bushong
:: License:     MIT License
::
::===================================================================
::

::
:: This source file is subject to the MIT license that is bundled
:: with this package in the file LICENSE.
::

:: Set our title...
TITLE Failnet PHP IRC Bot

:: Where is the PHP executable located?
SET PHP=

:: Where is the bot located?
SET BOT=

:: This is what server configuration file you want Failnet to load.
SET SERVER=Config

:: Ignore this.  It's just for the bot to find its termination indicator file. ;)
SET CHECKFILE="%BOT%\Data\Restart.inc"

:: Loop to here if we're just restarting Failnet.
:RUNBOT

:: Run Failnet!
"%PHP%\php\php.exe" "%BOT%\Failnet.php" %SERVER% %2 %3 %4

:: Is our termination indicator file there?
IF EXIST %CHECKFILE% GOTO RUNBOT
IF NOT EXIST %CHECKFILE% GOTO EOF

:: Time to go bye-bye.
:EOF

:: Uncomment this (remove the ::) to have the command prompt window pause after Failnet's termination.
:: Useful for trapping errors.
::PAUSE

EXIT
