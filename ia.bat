@ECHO OFF

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

:: Where is the PHP executable located?
SET PHP=

:: Where is the bot located?
SET BOT=

:RUN

:: Run
"%PHP%\php\php.exe" "%BOT%\ia.php" %1 %2 %3 %4

:: Time to go bye-bye.
:EOF
EXIT