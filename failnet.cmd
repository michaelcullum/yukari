@ECHO OFF

::
::===================================================================
::
::  Failnet -- PHP-based IRC Bot
::-------------------------------------------------------------------
::	Script info:
:: Version:		2.0.0
:: SVN ID:		$Id$
:: Copyright:	(c) 2009 - Failnet Project
:: License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
::
::===================================================================
::

::
:: This program is free software; you can redistribute it and/or modify
:: it under the terms of the GNU General Public License as published by
:: the Free Software Foundation; either version 2 of the License,
:: or (at your option) any later version.
::
:: This program is distributed in the hope that it will be useful,
:: but WITHOUT ANY WARRANTY; without even the implied warranty of
:: MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
:: See the GNU General Public License for more details.
::
:: You should have received a copy of the GNU General Public License
:: along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
::

:: Set our title...
TITLE Failnet PHP IRC Bot

:: Where is the PHP executable located?
SET PHP=

:: Where is the bot located?
SET BOT=

:: This is what server configuration file you want Failnet to load.
SET SERVER=config

:: Ignore this.  It's just for the bot to find its termination indicator file. ;)
SET CHECKFILE="%BOT%\data\restart.inc"

:: Loop to here if we're just restarting Failnet.
:RUNBOT

:: Run Failnet!
"%PHP%\php\php.exe" "%BOT%\failnet.php" %SERVER% %2 %3 %4

:: Is our termination indicator file there?
IF EXIST %CHECKFILE% GOTO RUNBOT
IF NOT EXIST %CHECKFILE% GOTO EOF

:: Time to go bye-bye.
:EOF

:: Uncomment this (remove the ::) to have the command prompt window pause after Failnet's termination.  
:: Useful for trapping errors.
::PAUSE

EXIT