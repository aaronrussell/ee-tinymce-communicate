<?php
if (!defined('EXT')) exit('Invalid file request');

class TinyMCE_Communicate
{
	var $name			= 'TinyMCE Communicate';
	var $description	= 'Adds TinyMCE WYSIWYG editing to ExpressionEngine\'s \'Communicate\' tab. (Requires LG TinyMce)';
	var $version 		= '0.1';
	var $docs_url		= 'http://github.com/aaronrussell/ee-tinymce-communicate';
	
	function __construct()
	{
		$this->settings = $this->_get_lg_settings();
	}
	
	function _get_lg_settings( $force_refresh = FALSE, $return_all = FALSE )
	{
		global $DB, $REGX, $PREFS;
		$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = 'Lg_tinymce' LIMIT 1");
		$settings = $REGX->array_stripslashes(unserialize($query->row['settings']));
		return $settings[$PREFS->ini('site_id')];
	}
	
	function activate_extension()
	{
		global $DB;

		$settings =	array();
		$hooks[] = array(			
			'extension_id'	=> '',
			'class'			=> __CLASS__,
			'method'		=> 'show_full_control_panel_end',
			'hook'			=> 'show_full_control_panel_end',
			'settings'		=> serialize($settings),
			'priority'		=> 10,
			'version'		=> $this->version,
			'enabled'		=> 'y'
		);
		
		foreach($hooks as $hook)
		{
			 $DB->query($DB->insert_string('exp_extensions', $hook));
		}
	}
	
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM exp_extensions WHERE class = '" . __CLASS__ . "'");
	}
	
	function show_full_control_panel_end($html)
	{
		global $EXT;
		
		$html = ($EXT->last_call !== FALSE) ? $EXT->last_call : $html;
		
		if(isset($_GET['C']) && $_GET['C'] == 'communicate')
		{
			$replace = '<script type="text/javascript" src="' . trim($this->settings['script_path']) . '"></script>' . NL;
			$settings_parts = implode("\n\t\t", preg_split("/(\r\n|\n|\r)/", trim($this->settings['script_config'])));
			$replace .= '
				<script type="text/javascript">
				//<![CDATA[
					tinyMCE.init({'.$settings_parts.'});
				//]]>
				</script>';
			$html = str_replace('</head>', $replace.'</head>', $html);
			$html = $this->str_replace_once("class='textarea'", "class='textarea lg_mceEditor'", $html);
		}
		return $html;
	}
	
	function str_replace_once($needle , $replace , $haystack)
	{
		$pos = strpos($haystack, $needle);
		if ($pos === false) {return $haystack;}
		return substr_replace($haystack, $replace, $pos, strlen($needle));
	}
}
?>