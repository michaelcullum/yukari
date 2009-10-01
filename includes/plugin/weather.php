<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 * 
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 */
 

/**
 * Failnet - Weather plugin,
 * 		This allows a user to check the weather for a specified location.
 * 		Based off of the googleWeather PHP class by Ashwin Surajbali of Redink Design
 * 
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_weather extends failnet_plugin_common
{
	/**
	 * Last time a user asked for a forecast
	 * @var integer
	 */
	private $last_forecast = 0;

	/**
	 * Number of seconds that someone must wait from the last forecast in order to get another one
	 * @var integer
	 */
	private $forecast_floodcheck = 15;

	/**
	 * Location to get weather info for.
	 * @var int
	 */
	public $location;

	/**
	 * Disable or enable caching
	 * @var boolean
	 */
	public $enable_cache = false;

	/**
	 * Path to your cache directory
	 * eg. /www/website.com/cache
	 * @var string
	 */
	public $cache_path = 'data/weather';

	/**
	 * Cache expiration time in seconds
	 * Default: 3600 = 1 Hour
	 * If the cached file is older than 1 hour, new data is fetched
	 * @var int
	 */
	public $cache_time = 3600;

	/**
	 * Full location of the cache file
	 * @var string
	 */
	private $cache_file;

	/**
	 * Location of the google weather api
	 * @var string
	 */
	private $gweather_api_url = 'http://www.google.com/ig/api?weather=';

	/**
	 * Storage var for data returned from curl request to the google api
	 * @var string
	 */
	private $raw_data;

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$sender = $this->event->nick;
		$hostmask = $this->event->gethostmask();
		switch ($cmd)
		{
			case 'weather':
				$weather_data = $this->weather($text);
				if($weather_data && $weather_data['forecast_info']['zip'][0] != '')
				{
					$current_weather = array(
						$weather_data['forecast_info']['city'][0],
						$weather_data['current_conditions']['condition'][0],
						$weather_data['current_conditions']['temp_f'][0] . 'F ' . $weather_data['current_conditions']['temp_c'][0] . 'C',
						$weather_data['current_conditions']['humidity'][0],
						$weather_data['current_conditions']['wind'][0]
					);
					$current_weather = implode(' / ', $current_weather);
					$this->call_privmsg($this->event->source(), $current_weather);
				}
				else
				{
					$this->call_privmsg($this->event->source(), $this->event->nick . ': Sorry, but I wasn\'t able to retrieve the current weather conditions for the area you specified.');
				}
			break;

			case 'forecast':
				if((time() - $this->forecast_floodcheck) >= $this->last_forecast)
				{
					$weather_data = $this->weather($text);
					if($weather_data && $weather_data['forecast_info']['zip'][0] != '')
					{
						$this->call_privmsg($this->event->source(), $this->event->nick . ': Here\'s the current forecast for ' . $weather_data['forecast_info']['city'] . ':');
						foreach($weather_data['forecast'] as $forecast)
						{
							$data = $forecast['day_of_week'] . ' / High ' . $forecast['high'] . 'F ' . $this->temp_conv('F-C', $forecast['high']) . 'C / Low ' . $forecast['low'] . 'F ' . $this->temp_conv('F-C', $forecast['low']) . 'C / Condition: ' . $forecast['condition'];
							$this->call_privmsg($this->event->source(), $data);
							usleep(500);
						}
						$this->last_forecast = time();
					}
					else
					{
						$this->call_privmsg($this->event->source(), $this->event->nick . ': Sorry, but I wasn\'t able to retrieve a forecast for the area you specified.');
					}
					
				}
				else
				{
					$this->call_privmsg($this->event->source(), 'I just gave out a forecast already.');
				}
			break;
		}
	}

	/**
	 * Pull weather information for 'Zipcode' passed in
	 * If enable_cache = true, data is cached and refreshed every hour
	 * Weather data is returned in an associative array
	 *
	 * @param int $zip
	 * @return array
	 */
	public function weather($zip = 0)
	{
		$this->zip = $zip;

		if($this->enable_cache && !empty($this->cache_path))
		{
			$this->cache_file = FAILNET_ROOT . $this->cache_path . "/{$this->zip}.inc";
			$return = $this->load_cache();
			if($return !== false)
				return $return;
		}

		if($this->make_request())
		{
			$xml = new SimpleXMLElement($this->raw_data);

			$return = array(
				'forecast_info'			=> array(
					'city'					=> $xml->weather->forecast_information->city['data'],
					'zip'					=> $xml->weather->forecast_information->postal_code['data'],
					'date'					=> $xml->weather->forecast_information->forecast_date['data'],
					'date_time'				=> $xml->weather->forecast_information->current_date_time['data'],
				),
				'current_conditions'	=> array(
					'condition' 			=> $xml->weather->current_conditions->condition['data'],
					'temp_f' 				=> $xml->weather->current_conditions->temp_f['data'],
					'temp_c' 				=> $xml->weather->current_conditions->temp_c['data'],
					'humidity' 				=> $xml->weather->current_conditions->humidity['data'],
					'icon' 					=> 'http://www.google.com' . $xml->weather->current_conditions->icon['data'],
					'wind' 					=> $xml->weather->current_conditions->wind_condition['data'],
				),
			);
			for($i = 0; $i < count($xml->weather->forecast_conditions); $i++)
			{
				$data = &$xml->weather->forecast_conditions[$i];
				$forecast[$i] = array(
					'day_of_week'	=> (string) $data->day_of_week['data'],
					'low'			=> (string) $data->low['data'],
					'high'			=> (string) $data->high['data'],
					'icon'			=> (string) 'http://img0.gmodules.com/' . $data->icon['data'],
					'condition'		=> (string) $data->condition['data'],
				);
			}

			$return['forecast'] = $forecast;
		}
		if ($this->enable_cache && !empty($this->cache_path))
			$this->write_cache($return);

		return $return;
	}

	private function load_cache()
	{
		if (file_exists($this->cache_file) && (time() - filemtime($this->cache_file)) <= $this->cache_time)
		{
			return unserialize(file_get_contents($this->cache_file));
		}
		else
		{
			return false;
		}
	}

	private function write_cache($data)
	{
		if (!file_put_contents($this->cache_file, serialize($data)))
			trigger_error('Could not save data to cache. Please make sure the cache directory exists and is writable.', E_USER_WARNING);
	}

	private function make_request()
	{
		$this->raw_data = file_get_contents($this->gweather_api_url . urlencode($this->zip));
		return !empty($this->raw_data) ? true : false;
	}

	/**
	 * Converts temperatures between one scale to another
	 * @param string $type - The type of conversion to do (F to C, C to F)
	 *@param integer $temp - The temperature to convert
	 */
	private function temp_conv($type, $temp)
	{
		switch($type)
		{
			case 'F-C':
				return round((5/9) * ($temp - 32), 1);
			break;
		
			case 'C-F':
				return round((9/5) * $temp + 32, 1);
			break;
		}
	}
}
?>