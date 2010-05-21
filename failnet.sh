#!/bin/bash

#
#===================================================================
#
#  Failnet -- PHP-based IRC Bot
#-------------------------------------------------------------------
# Version:		3.0.0 DEV
# Copyright:	(c) 2009 - 2010 -- Failnet Project
# License:		GNU General Public License, Version 3
#
#===================================================================
#
# Thanks to Techie-Micheal for writing the fancy bash script for Failnet 2.
#

#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
#

# This is what server configuration file you want Failnet to load.
SERVER="config"

# Run Failnet!
while php failnet.php $SERVER;
	do true
done

# Uncomment this (remove the #) to have the command prompt window pause after Failnet's termination.  
# Useful for trapping errors.
read -s -n 1 -p "Press any key to continue ...\n"