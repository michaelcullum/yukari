# Failnet -- PHP-based IRC Bot

## Failnet Coding Guidelines

### Editor settings

* You must use tab indentation for within each block
* Tabs should be set to equal 4 (four) spaces for Failnet.
* All files should be saved with UNIX (also known as LF or "\n") linefeeds, and not using the Windows linefeeds (also known as CRLF or "\r\n") or Classic Mac (also known as CR or "\r") linefeeds.

### General coding style

* Use braces and not keywords (such as endif; and endwhile;)
* Braces indicating the start or end of code blocks are to be placed on their own line at all times.
* Only have one statement in a line!  Do not stack multiple statements separated by the end-of-statement character on one line!
* Long lines of code should not be wrapped onto the next line.
* Order of operations should be made explicitly clear; use parenthesis as necessary.
* Put a space between operators and values.
* Use symbolic operators instead of keyword operators.  This isn't Visual Basic, this is PHP.
* Ternary statements are allowed for assigning values to a variable or property, or specifying parameters for a method/function.  Ternary statements are NOT mini-if statements for deciding on what methods/functions to execute!

Examples:
	/* example 1 - braces vs keywords */
	// this is bad
	if($something == true)
		echo 'hi';
	endif;
	// this is good
	if($something == true)
	{
		echo 'hi';
	}

	/* example 2 - brace location */
	// this is bad
	if($something == true){
		echo 'hi';
	}
	// this is good
	if($something == true)
	{
		echo 'hi';
	}

	/* example 3 - statement stacking */
	// this is bad
	echo 'hi'; $this->doSomething(); exit();
	// this is good
	echo 'hi';
	$this->doSomething();
	exit();

	/* example 4 - statement wrapping */
	// this is bad
	$this->doSomething('somereallylongstringthatiswaytoolongthatyoushouldneverseeinyourcode',
		array('something' => 'foobar'));
	// this is good
	$this->doSomething('somereallylongstringthatiswaytoolongthatyoushouldneverseeinyourcode', array('something' => 'foobar'));
	// this is much better
	$long_string = 'somereallylongstringthatiswaytoolongthatyoushouldneverseeinyourcode';
	$this->doSomething($long_string, array('something' => 'foobar'));

	/* example 5 - order of operations, operator precedence */
	// this is bad
	$bool = ($big_var < 7 && $super_var > 8 || $some_var == 4);
	// this is good
	$bool = ($big_var < 7 && ($super_var > 8 || $some_var == 4));

	/* example 6 - spacing between operators and values */
	// this is bad
	$value=3+$another_value;
	// this is good
	$value = 3 + $another_value;

	/* example 7 - symbolic operators versus keyword operators */
	// this is bad
	if($something AND $something_else)
	// this is good
	if($something && $something_else)

	/* example 8 - ternary statements */
	// this is bad
	($some_value) ? $this->doSomething : $this->doSomethingElse;
	// this is good
	$some_value = ($value !== false) ? $this->doSomething($value) : $some_other_value;

### Naming style
* All names (classes, files, methods, properties, etc.) should have short yet descriptive names.
* Class names, file names, directory names, and method names should be in camelCase.
* Filenames and class names should start with the first letter capitalized
* Method names should start with the first letter NOT capitalized.
* Variable names and property names are to be written in lowercase with underscores replacing spaces.
* Boolean values (true and false) are to be written in lowercase, while the "NULL" keyword is to be written in uppercase.
* Underscores in method names are reserved for hookable methods and built-in magic methods.  If the method is hookable, it should only have one underscore at the beginning of the method name, and nowhere else.

Examples:
	/* example 1 - class naming, method naming, property naming */
	class SomeClass
	{
		public $some_var = '';
		public function doSomething()
		{
			echo 'did something';
		}
	}

	/* example 2 - directory/file naming */
	/Includes/Core/Language.php

	/* example 3 - variable naming */
	$some_variable = 3;
	$another_variable = 'string';

	/* example 4 - boolean/null use */
	$null_variable = NULL;
	$true_variable = true;
	$false_variable = false;

	/* example 5 - naming things clearly */
	// this is bad
	$cost_to_construct_giant_robot = 300;
	// this is better
	$giant_robot_cost = 300;

	// this is very bad
	$bc = 'red';
	// this is better
	$background_color = 'red';


### "IF" conditionals

* Do not include a space between "if" and the opening parenthesis.
* When no "else" or "elseif" extension is present and when there is only one line being executed, does not have to use braces.

Example:
	if($some_var)
		$this->doSomething();

### Loops

* For and foreach loops, as long as they only execute one line of code in the loop, are not required to use braces
* While and do-while loops are required to use braces.

Examples:
	/* example 1 - single-line for loops */
	for($i = 1;  $i <= 10; $i++)
		echo $i;

	/* example 2 - single line foreach loops */
	foreach($array as $key => $value)
		$another_array[$key] = $value;

	/* example 3 - while loops */
	while(true)
	{
		$this->doSomething();
	}

	/* example 4 - do-while loops */
	do
	{
		$this->doSomething();
	}
	while(true);

### Code documentation

* All files, classes, methods, and properties should be properly documented as per PHPDoc guidelines.
* Any methods that throw an exception must also state such in their method documentation using the @throws comment.  See the provided example on how to write the @throws comment.

Examples:
	/* example 1 - file header documentation */
	// @note file's location is at Includes/Core/Core.php
	// @note take notice! @author should be your name, @copyright changed to your own name, @link should be changed to a link to your own project
	// @note the @package comment should change to the correct package this is in as well
	<?php
	/**
	 *
	 *===================================================================
	 *
	 *  Failnet -- PHP-based IRC Bot
	 *-------------------------------------------------------------------
	 * @version     3.0.0 DEV
	 * @category    Failnet
	 * @package     core
	 * @author      Damian Bushong
	 * @copyright   (c) 2010 -- Damian Bushong
	 * @license     MIT License
	 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
	 *
	 *===================================================================
	 *
	 * This source file is subject to the MIT license that is bundled
	 * with this package in the file LICENSE.
	 *
	 */

	/* example 2 - class documentation */
	// @note this should say what the class is, describe it, and also contain the correct @author, @package, and @link comments
	/**
	 * Failnet - Core class,
	 *      Failnet in a nutshell.  Faster, smarter, better, and with a sexier voice.
	 *
	 *
	 * @category    Failnet
	 * @package     core
	 * @author      Damian Bushong
	 * @license     MIT License
	 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
	 */
	class Core extends Base
	{

	/* example 3, property documentation
	// @note this should be @var (variable type) - (property description)
	/**
	 * @var array - Automagical property for use with magic methods
	 */
	private $virtual_storage = array();

	/* example 4 - method documentation, no parameters */
	// @note even if the method does not return a value, it should have an @return comment! just say @return void
	/**
	 * Instantiates Failnet and sets everything up.
	 * @return void
	 */
	public function __construct()

	/* example 5 - method documentation with parameters, @throws comment */
	// @note the @param comment should be like follows: @param (variable type) $(parameter name) - (parameter description)
	// @note also, take note: this shows that the method throws an exception of type Failnet\Exception, and any calls to this method should be prepared if it does throw an exception of this type
	/**
	 * Failnet configuration file settings load method
	 * @param string $file - The configuration file to load
	 * @return void
	 * @throws Failnet\Exception
	 */
	private function load($file)
