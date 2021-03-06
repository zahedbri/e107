<?php

/**
 * @file
 * Functions for use with e107's Ajax API.
 */


/**
 * Class e_ajax_class.
 *
 * Functions for e107's Ajax API.
 *
 * E107's Ajax API is used to dynamically update parts of a page's HTML
 * based on data from the server. Upon a specified event, such as a button
 * click, a callback function is triggered which performs server-side logic and
 * may return updated markup, which is then replaced on-the-fly with no page
 * refresh necessary.
 *
 * This framework creates a PHP macro language that allows the server to
 * instruct JavaScript to perform actions on the client browser.
 *
 * When responding to Ajax requests, the server should do what it needs to do
 * for that request, then create a commands array. This commands array will
 * be converted to a JSON object and returned to the client, which will then
 * iterate over the array and process it like a macro language.
 *
 * Each command item is an associative array which will be converted to a
 * command object on the JavaScript side. $command_item['command'] is the type
 * of command, e.g. 'alert' or 'replace'. The command array may contain any other
 * data that the command needs to process, e.g. 'method', 'target', 'settings',
 * etc.
 *
 * Commands are usually created with a couple of helper functions, so they
 * look like this:
 * @code
 *   $ajax = e107::ajax();
 *
 *   $commands = array();
 *   // Merge array into e107.settings javascript object.
 *   $commands[] = $ajax->commandSettings(array('foo' => 'bar'));
 *   // Remove 'disabled' attribute from the '#object-1' element.
 *   $commands[] = $ajax->commandInvoke('#object-1', 'removeAttr', array('disabled'));
 *   // Insert HTML content into the '#object-1' element.
 *   $commands[] = $ajax->commandInsert('#object-1', 'html', 'some html content');
 *
 *   // This method returns with data in JSON format. It sets the header for
 *   // JavaScript output.
 *   $ajax->response($commands);
 * @endcode
 */
class e_ajax
{

	/**
	 * Constructor.
	 * Use {@link getInstance()}, direct instantiating is not possible for signleton
	 * objects.
	 */
	public function __construct()
	{
	}

	/**
	 * @return void
	 */
	protected function _init()
	{
	}

	/**
	 * Cloning is not allowed.
	 */
	private function __clone()
	{
	}

	/**
	 * Returns data in JSON format.
	 *
	 * This function should be used for JavaScript callback functions returning
	 * data in JSON format. It sets the header for JavaScript output.
	 *
	 * @param $var
	 *   (optional) If set, the variable will be converted to JSON and output.
	 */
	public function response($var = null)
	{
		// We are returning JSON, so tell the browser.
		header('Content-Type: application/json');

		if(isset($var))
		{
			echo $this->render($var);
		}
	}

	/**
	 * Renders a commands array into JSON.
	 *
	 * @param array $commands
	 *  A list of macro commands generated by the use of e107::ajax()->command*
	 *  methods.
	 *
	 * @return string
	 */
	public function render($commands = array())
	{
		$tp = e107::getParser();
		return $tp->toJSON($commands);
	}

	/**
	 * Creates an Ajax 'alert' command.
	 *
	 * The 'alert' command instructs the client to display a JavaScript alert
	 * dialog box.
	 *
	 * @param $text
	 *   The message string to display to the user.
	 *
	 * @return array
	 *   An array suitable for use with the e107::ajax()->render() function.
	 */
	public function commandAlert($text)
	{
		return array(
			'command' => 'alert',
			'text'    => $text,
		);
	}

	/**
	 * Creates an Ajax 'insert' command.
	 *
	 * This command instructs the client to insert the given HTML.
	 *
	 * @param $target
	 *   A jQuery target selector.
	 * @param $method
	 *   Selected method fo DOM manipulation:
	 *   'replaceWith', 'append', 'prepend', 'before', 'after', 'html'
	 * @param $html
	 *   The data to use with the jQuery method.
	 *
	 * @return array
	 *   An array suitable for use with the e107::ajax()->render() function.
	 */
	public function commandInsert($target, $method, $html)
	{
		return array(
			'command'  => 'insert',
			'method'   => $method,
			'target'   => $target,
			'data'     => $html,
		);
	}

	/**
	 * Creates an Ajax 'remove' command.
	 *
	 * The 'remove' command instructs the client to use jQuery's remove() method
	 * to remove each of elements matched by the given target, and everything
	 * within them.
	 *
	 * @param $target
	 *   A jQuery selector string.
	 *
	 * @return array
	 *   An array suitable for use with the e107::ajax()->render() function.
	 *
	 * @see http://docs.jquery.com/Manipulation/remove#expr
	 */
	public function commandRemove($target)
	{
		return array(
			'command' => 'remove',
			'target'  => $target,
		);
	}

	/**
	 * Creates an Ajax 'css' command.
	 *
	 * The 'css' command will instruct the client to use the jQuery css() method
	 * to apply the CSS arguments to elements matched by the given target.
	 *
	 * @param $target
	 *   A jQuery selector string.
	 * @param $argument
	 *   An array of key/value pairs to set in the CSS for the target.
	 *
	 * @return array
	 *   An array suitable for use with the e107::ajax()->render() function.
	 *
	 * @see http://docs.jquery.com/CSS/css#properties
	 */
	public function commandCSS($target, $argument)
	{
		return array(
			'command'  => 'css',
			'target'   => $target,
			'argument' => $argument,
		);
	}

	/**
	 * Creates an Ajax 'settings' command.
	 *
	 * The 'settings' command instructs the client to extend e107.settings with
	 * the given array.
	 *
	 * @param $settings
	 *   An array of key/value pairs to add to the settings. This will be utilized
	 *   for all commands after this if they do not include their own settings
	 *   array.
	 *
	 * @return array
	 *   An array suitable for use with the e107::ajax()->render() function.
	 */
	public function commandSettings($settings)
	{
		return array(
			'command'  => 'settings',
			'settings' => $settings,
		);
	}

	/**
	 * Creates an Ajax 'data' command.
	 *
	 * The 'data' command instructs the client to attach the name=value pair of
	 * data to the target via jQuery's data cache.
	 *
	 * @param $target
	 *   A jQuery selector string.
	 * @param $name
	 *   The name or key (in the key value pair) of the data attached to this
	 *   target.
	 * @param $value
	 *   The value of the data. Not just limited to strings can be any format.
	 *
	 * @return array
	 *   An array suitable for use with the e107::ajax()->render() function.
	 *
	 * @see http://docs.jquery.com/Core/data#namevalue
	 */
	public function commandData($target, $name, $value)
	{
		return array(
			'command' => 'data',
			'target'  => $target,
			'name'    => $name,
			'value'   => $value,
		);
	}

	/**
	 * Creates an Ajax 'invoke' command.
	 *
	 * The 'invoke' command will instruct the client to invoke the given jQuery
	 * method with the supplied arguments on the elements matched by the given
	 * target. Intended for simple jQuery commands, such as attr(), addClass(),
	 * removeClass(), toggleClass(), etc.
	 *
	 * @param $target
	 *   A jQuery selector string.
	 * @param $method
	 *   The jQuery method to invoke.
	 * @param $arguments
	 *   (optional) A list of arguments to the jQuery $method, if any.
	 *
	 * @return array
	 *   An array suitable for use with the e107::ajax()->render() function.
	 */
	public function commandInvoke($target, $method, array $arguments = array())
	{
		return array(
			'command'   => 'invoke',
			'target'    => $target,
			'method'    => $method,
			'arguments' => $arguments,
		);
	}

}