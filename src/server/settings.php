<?php

/**
 * Elang module admin settings and defaults
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package     mod
 * @subpackage  elang
 * @copyright   2013-2015 University of La Rochelle, France
 * @license     http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html CeCILL-B license
 *
 * @since       0.0.1
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree)
{
	require_once dirname(__FILE__) . '/lib.php';
	require_once dirname(__FILE__) . '/locallib.php';
	require_once $CFG->libdir . '/resourcelib.php';
	$languages = Elang\getLanguages();

	// General settings
	if (version_compare(moodle_major_version(true), '2.8', '<'))
	{
		// This should be present only until Moodle 2.7.x
		$settings->add(
			new admin_setting_configcheckbox(
				'elang/requiremodintro',
				get_string('requiremodintro', 'elang'),
				get_string('configrequiremodintro', 'elang'),
				1
			)
		);
	}

	$settings->add(
		new admin_setting_configtext(
			'elang/videomaxsize',
			get_string('videomaxsize', 'elang'),
			get_string('videomaxsize_config', 'elang'),
			10000000,
			PARAM_INT
		)
	);

	$settings->add(
		new admin_setting_configtext(
			'elang/postermaxsize',
			get_string('postermaxsize', 'elang'),
			get_string('postermaxsize_config', 'elang'),
			1000000,
			PARAM_INT
		)
	);

	$settings->add(
		new admin_setting_configtext(
			'elang/subtitlemaxsize',
			get_string('subtitlemaxsize', 'elang'),
			get_string('subtitlemaxsize_config', 'elang'),
			100000,
			PARAM_INT
		)
	);
	$settings->add(
		new admin_setting_configtext(
			'elang/repeatedunderscore',
			get_string('repeatedunderscore', 'elang'),
			get_string('repeatedunderscore_config', 'elang'),
			10,
			PARAM_INT
		)
	);
	$settings->add(
		new admin_setting_configtext(
			'elang/titlelength',
			get_string('titlelength', 'elang'),
			get_string('titlelength_config', 'elang'),
			100,
			PARAM_INT
		)
	);
	$settings->add(
		new admin_setting_configselect(
			'elang/limit',
			get_string('limit', 'elang'),
			get_string('limit_config', 'elang'),
			10,
			array(5 => 5, 10 => 10, 15 => 15, 20 => 20, 25 => 25)
		)
	);
	$settings->add(
		new admin_setting_configtext(
			'elang/timeout',
			get_string('timeout', 'elang'),
			get_string('timeout_config', 'elang'),
			3000,
			PARAM_INT
		)
	);

	$settings->add(
		new admin_setting_configmultiselect(
			'elang/language',
			get_string('language', 'elang'),
			get_string('language_config', 'elang'),
			array_keys($languages),
			$languages
		)
	);
	$settings->add(
		new admin_setting_configcheckbox(
			'elang/showlanguage',
			get_string('showlanguage', 'elang'),
			get_string('showlanguage_config', 'elang'),
			1
		)
	);

	$settings->add(
		new admin_setting_configtext(
			'elang/left',
			get_string('left', 'elang'),
			get_string('left_config', 'elang'),
			20,
			PARAM_INT
		)
	);
	$settings->add(
		new admin_setting_configtext(
			'elang/top',
			get_string('top', 'elang'),
			get_string('top_config', 'elang'),
			20,
			PARAM_INT
		)
	);
	$settings->add(
		new admin_setting_configtext(
			'elang/size',
			get_string('size', 'elang'),
			get_string('size_config', 'elang'),
			16,
			PARAM_INT
		)
	);
}
