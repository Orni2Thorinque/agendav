<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/*
 * Copyright 2011 Jorge López Pérez <jorge@adobo.org>
 *
 *  This file is part of AgenDAV.
 *
 *  AgenDAV is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  any later version.
 *
 *  AgenDAV is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with AgenDAV.  If not, see <http://www.gnu.org/licenses/>.
 */

class Dates {

	// Possible time formats
	static $timeformats = array(
			'24' => array(
				'strftime' => '%H:%M',
				'date' => 'H:i',
				'fullcalendar' => 'HH:mm',
				),
			'12' => array(
				'strftime' => '%l:%M%P',
				'date' => 'h:i A', // timepicker format
				'fullcalendar' => 'h(:mm)tt',
				));

	// Possible date formats (not needed for strftime)
	static $dateformats = array(
			'ymd' => array(
				'date' => 'Y-m-d',
				'datepicker' => 'yy-mm-dd',
				),
			'dmy' => array(
				'date' => 'd/m/Y',
				'datepicker' => 'dd/mm/yy',
				),
			'mdy' => array(
				'date' => 'm/d/Y',
				'datepicker' => 'mm/dd/yy',
				),
			);

	private $CI;

	function __construct() {
		$this->CI =& get_instance();
	}

	/**
	 * Returns a DatTime object with date approximated by factor seconds.
	 * Defaults to 30 minutes (60*30 = 1800)
	 */

	function approx_by_factor($time = null, $factor = 1800, $tz = null) {
		if (is_null($time)) {
			$time = time();
		}

		$rounded = (round($time/$factor))*$factor;

		return $this->ts2datetime($rounded);
	}

	/**
	 * Creates a DateTime object from an UNIX timestamp using the specified
	 * Timezone. If not TZ is specified then default one is used
	 */
	function ts2datetime($ts, $tz = null) {
		if (is_null($tz)) {
			$tz = $this->CI->config->item('default_timezone');
		}

		$obj = new DateTime('@' . $ts);
		// When creating by timestamp, DateTime ignores current timezone
		$obj->setTimeZone(new DateTimeZone($tz));

		return $obj;
	}

	/**
	 * Creates a DateTime object from a date formatted by frontend (such as
	 * m/d/Y H:i).
	 *
	 * Returns FALSE on date parsing error
	 */
	function frontend2datetime($str, $tz = null) {
		if (is_null($tz)) {
			$tz = $this->CI->config->item('default_timezone');
		}

		$format = $this->date_format_string('date') . ' '. $this->time_format_string('date');
		$obj = DateTime::createFromFormat($format, $str, new
				DateTimeZone($tz));
		$err = DateTime::getLastErrors();
		if (FALSE === $obj || $err['warning_count']>0) {
			return FALSE;
		}

		return $obj;
	}

	/**
	 * Converts a DateTime to DATE-TIME format 
	 * in UTC time by default
	 *
	 * If no object is passed, current time is used
	 */
	function datetime2idt($dt = null, $tz = 'UTC', $format = '') {

		if (is_null($dt)) {
			$dt = new DateTime('now', new DateTimeZone($tz));
		} else {
			$dt->setTimeZone(new DateTimeZone($tz));
		}

		if (empty($format)) {
			$format = $this->format_for('DATE-TIME', $tz);
		}

		$str = $dt->format($format);

		return $str;
	}

	/**
	 * Converts a DATE-TIME/DATE formatted string to DateTime
	 * in UTC time by default.
	 *
	 * Default timezone is used if not specified
	 */
	function idt2datetime($id_arr, $tz = null) {
		if ($tz == null) {
			// Suppose current timezone
			$tz = $this->CI->config->item('default_timezone');
		}

		$format = 'YmdHis';

		// $tz should be enough
		unset($id_arr['tz']);

		$str = '';
		foreach ($id_arr as $k => $v) {
			$str .= $v;
		}

		// VALUE=DATE
		if (!isset($id_arr['hour'])) {
			$str .= '000000';
		}

		$obj = DateTime::createFromFormat($format,
				$str, new DateTimeZone($tz));

		return $obj;
	}

	/**
	 * Returns a suitable date() format string according to specified
	 * timezone to parse a DATE-TIME/DATE iCalendar string.
	 *
	 * Defaults to UTC
	 */

	function format_for($type = 'DATE-TIME', $tz = 'UTC') {
		$format = '';

		if ($type == 'DATE') {
			$format = 'Ymd';
		} else {
			$format = 'Ymd\THis';
		}

		if ($tz == 'UTC' && $type != 'DATE') {
			$format .= '\Z';
		}

		return $format;
	}

	/**
	 * Converts a DateInterval to a DURATION string
	 *
	 * Parameter has to be the result of add() or diff() to an existing
	 * DateTime object
	 */
	function di2duration($di) {
		if ($obj->days === FALSE) {
			// We have a problem
			return FALSE;
		}

		$days = $obj->days;
		$seconds = $obj->s + $obj->i*60 + $obj->h*3600;
		$str = '';

		// Simplest case
		if ($days%7 == 0 && $seconds == 0) {
			$str = ($days/7) . 'W';
		} else {
			$time_units = array(
					'3600' => 'H',
					'60' => 'M',
					'1' => 'S',
					);
			$str_time = '';
			foreach ($time_units as $k => $v) {
				if ($seconds >= $k) {
					$str_time .= floor($seconds/$k) . $v;
					$seconds %= $k;
				}
			}

			// No days
			if ($days == 0) {
				$str = 'T' . $str_time;
			} else {
				$str = $days . 'D' . (empty($str_time) ? '' : 'T' . $str_time);
			}
		}

		return ($obj->invert == '1' ? '-' : '') . 'P' . $str;
	}

	/**
	 * Convertes a DURATION string to a DateInterval
	 * Allows the use of '-' in front of the string
	 */
	function duration2di($str) {
		$minus;
		$new_str = preg_replace('/^(-)/', '', $str, -1, $minus);

		$interval = new DateInterval($new_str);
		if ($minus == 1) {
			$interval->invert = 1;
		}

		return $interval;
	}

	/**
	 * Converts a X-CURRENT-DTSTART/X-CURRENT-DTEND string to a DateTime
	 * object
	 */
	function x_current2datetime($str, $tz) {
		$matches = array();
		$res = preg_match('/^(\d+)-(\d+)-(\d+)( (\d+):(\d+):(\d+)( (\S+))?)?$/', $str, $matches);

		if ($res === FALSE || $res != 1) {
			log_message('ERROR',
					'Error procesando [' . $str . '] como cadena'
					.' para X-CURRENT-*');
			return new DateTime();
		}

		$y = $matches[1];
		$m = $matches[2];
		$d = $matches[3];
		$h = isset($matches[5]) ? $matches[5] : '00';
		$i = isset($matches[6]) ? $matches[6] : '00';
		$s = isset($matches[7]) ? $matches[7] : '00';
		// Timezone is ignored, we already have $tz
		//$e = isset($matches[9]) ? $matches[9] : $tz;
	
		$format = 'dmY His';
		$new_str = $d.$m.$y.' '.$h.$i.$s;

		$dt = DateTime::createFromFormat($format, $new_str, 
				new DateTimeZone($tz));

		if ($dt === FALSE) {
			$this->CI->extended_logs->message('ERROR',
					'Error processing ' . $new_str . ' (post) as a string'
					.' for X-CURRENT-*');
			return new DateTime();
		}

		return $dt;
	}

	/**
	 * Returns a time format string for the current user
	 *
	 * @param	string	Type of format (fullcalendar, date, strftime)
	 * @return	string	Format string. Default formats on invalid params
	 */
	function time_format_string($type) {
		// TODO prefs
		$cfg_time = $this->CI->config->item('default_time_format');
		if ($cfg_time === FALSE 
				|| ($cfg_time != '12' && $cfg_time != '24')) {
			$this->CI->extended_logs->message('ERROR', 
					'Invalid default_time_format configuration value');
			$cfg_time = '24';
		} 
		switch($type) {
			case 'fullcalendar':
			case 'date':
			case 'strftime':
				return Dates::$timeformats[$cfg_time][$type];
				break;
			default:
				$this->CI->extended_logs->message('ERROR', 
						'Invalid type for time_format_string() passed'
						.' ('.$type.')');
				break;
		}
	}

	/**
	 * Returns a date format string for the current user
	 *
	 * @param	string	Type of format (date, datepicker)
	 * @return	string	Format string. Default formats on invalid params
	 */
	function date_format_string($type) {
		// TODO prefs
		$cfg_date = $this->CI->config->item('default_date_format');
		if ($cfg_date === FALSE 
				|| ($cfg_date != 'ymd' && $cfg_date != 'dmy'
					&& $cfg_date != 'mdy')) {
			$this->CI->extended_logs->message('ERROR', 
					'Invalid default_date_format configuration value');
			$cfg_date = 'ymd';
		} 

		switch($type) {
			case 'date':
			case 'datepicker':
				return Dates::$dateformats[$cfg_date][$type];
				break;
			default:
				$this->CI->extended_logs->message('ERROR', 
						'Invalid type for date_format_string() passed'
						.' ('.$type.')');
				break;
		}

	}

}
