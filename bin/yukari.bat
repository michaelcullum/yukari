@ECHO OFF
::
::===================================================================
::
::  Yukari
::-------------------------------------------------------------------
:: Copyright:   (c) 2009 - 2011 -- Damian Bushong
:: License:     MIT License
::
::===================================================================
::

::
:: This source file is subject to the MIT license that is bundled
:: with this package in the file LICENSE.
::

TITLE Yukari - IRC Bot

if "%PHPBIN%" == "" set PHPBIN=%PHP_PEAR_PHP_BIN%
%PHPBIN% "%CD%\yukari.php" %*
:: Uncomment this (remove the ::) to have the command prompt window pause after termination
PAUSE
