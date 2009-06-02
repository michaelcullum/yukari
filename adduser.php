#!/usr/bin/php
<?php
file_put_contents("data/users", file_get_contents("data/users")."\n".$argv[1]."::".$argv[2]."::".sha1($argv[3]));
?>