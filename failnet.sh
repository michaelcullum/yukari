#!/bin/bash

#
#===================================================================
#
#  Failnet -- PHP-based IRC Bot
#-------------------------------------------------------------------
# Version:      3.0.0 DEV
# Copyright:    (c) 2009 - 2010 -- Damian Bushong
# License:      MIT License
#
#===================================================================
#
# Thanks to Techie-Micheal for writing the fancy bash script for Failnet!
#

#
# This source file is subject to the MIT license that is bundled
# with this package in the file LICENSE.
#

# This is what server configuration file you want Failnet to load.
SERVER="Config"

# Run Failnet!
while php Failnet.php $SERVER;
	do true
done

# Uncomment this (remove the #) to have the command prompt window pause after Failnet's termination.
# Useful for trapping errors.
read -s -n 1 -p "Press any key to continue ...\n"
