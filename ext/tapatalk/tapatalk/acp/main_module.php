<?php
/**
* @ignore
*/
namespace tapatalk\tapatalk\acp;

error_reporting(0);
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/



class main_module
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx, $phpbb_container;

		$action	= request_var('action', '');
		$submit = request_var('submit', '');
		$submit = (!empty($submit)) ? true : false;

		$form_key = 'acp_mobiquo';
		add_form_key($form_key);
		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'mobiquo':
				$display_vars = array(
					'title'	=> 'ACP_MOBIQUO_SETTINGS',
					'vars'	=> array(
					'legend'				    => 'GENERAL_OPTIONS',
					'tapatalk_push_key'		    => array('lang' => 'TAPATALK_PUSH_KEY', 'validate' => 'string','type' => 'text:40:60','explain' => true),
				    'tapatalk_sso_enabled'	    => array('lang' => 'TAPATALK_SSO', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
					'tapatalk_auto_approve'     => array('lang' => 'TAPATALK_AUTO_APPROVE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
					'tapatalk_register_group'	=> array('lang' => 'TAPATALK_REGISTER_GROUP', 'validate' => 'string',	'type' => 'custom',	'explain' => true,	'method' => 'select_register_group'),
					'tapatalk_ad_filter'        => array('lang' => 'TAPATALK_AD_FILTER', 'validate' => 'string',	'type' => 'custom',	'explain' => true,	'method' => 'select_mutil_group_box'),
					'tapatalkdir'			    => array('lang' => 'MOBIQUO_NAME', 'validate' => 'string', 'type' => 'text:10:12', 'explain' => true),
					'mobiquo_hide_forum_id'	    => array('lang' => 'MOBIQUO_HIDE_FORUM_ID', 'validate' => 'string',	'type' => 'custom',	'explain' => true,	'method' => 'select_box'),
				    'tapatalk_forum_read_only'	=> array('lang' => 'TAPATALK_FORUM_READ_ONLY', 'validate' => 'string',	'type' => 'custom',	'explain' => true,	'method' => 'select_box'),
					'tapatalk_custom_replace'   => array('lang' => 'TAPATALK_CUSTOM_REPLACE', 'validate' => 'string', 'type' => 'custom', 'explain' => true, 'method' => 'show_custom_replacement'),

						)
				);
				break;
            case 'mobiquo_deeplink':
                $display_vars = array(
                    'title'	=> 'ACP_TAPATALK_DEEPLINK',
                    'vars'	=> array(
                    'legend'				=> 'ACP_TAPATALK_DEEPLINK',
                    'tapatalk_app_banner_enable'=> array('lang' => 'TAPATALK_ALLOW_APP_BANNER', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
                    'tapatalk_google_indexing_enabled'     => array('lang' => 'TAPATALK_GOOGLE_INDEXING', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
                    'tapatalk_facebook_indexing_enabled'	=> array('lang' => 'TAPATALK_FACEBOOK_INDEXING', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
                    'tapatalk_twitter_indexing_enabled'   => array('lang' => 'TAPATALK_TWITTER_INDEXING', 'validate' => 'bool', 'type' => 'radio:yes_no','explain' => true),
                    )
                );
                break;
		}


		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = request_var('config',array('' => ''), true);
		$cfg_array = (!empty($cfg_array)) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if($mode == 'mobiquo')
			{
			    $lv2_settings = request_var('config',array('' => array('' => '')),true);
			    if(isset($lv2_settings['mobiquo_hide_forum_id']))
				{
					$hide_forum_id = implode(',',$lv2_settings['mobiquo_hide_forum_id']);
					$cfg_array['mobiquo_hide_forum_id'] = $hide_forum_id;
				}
				elseif ($submit && empty($lv2_settings['mobiquo_hide_forum_id']))
				{
					$cfg_array['mobiquo_hide_forum_id'] = '';
				}
				if(isset($lv2_settings['tapatalk_forum_read_only']))
				{
					$forum_read_only = implode(',',$lv2_settings['tapatalk_forum_read_only']);
					$cfg_array['tapatalk_forum_read_only'] = $forum_read_only;
				}
				elseif ($submit && empty($lv2_settings['tapatalk_forum_read_only']))
				{
					$cfg_array['tapatalk_forum_read_only'] = '';
				}
				if(isset($lv2_settings['tapatalk_ad_filter']))
				{
					$tapatalk_ad_filter = implode(',',$lv2_settings['tapatalk_ad_filter']);
					$cfg_array['tapatalk_ad_filter'] = $tapatalk_ad_filter;
				}
				elseif ($submit && empty($lv2_settings['tapatalk_ad_filter']))
				{
					$cfg_array['tapatalk_ad_filter'] = '';
				}
			}
			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			if ($submit)
			{
                $this->setTapatalkConfigValue($config_name, $config_value);
			}
		}

		if ($submit)
		{
			add_log('admin', 'LOG_CONFIG_' . strtoupper($mode));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_mobiquo';
		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],
			'L_ACP_MOBIQUO_MOD_VER'	=> $user->lang['ACP_MOBIQUO_MOD_VER'],
			'MOBIQUO_MOD_VERSION'	=> $config['mobiquo_version'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}
			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
	}

	function select_box($value, $key)
	{
		global $user, $config, $phpbb_root_path,$db,$strSelect;
		$strSelect = '<select id="' . $key . '" name="config[' . $key . '][]" multiple="multiple" size="8">';
		$forum_filter = '';
        $root_forum_id = 0;
		$sql = 'SELECT f.* '. ($user->data['is_registered'] ? ', fw.notify_status' : '') . '
            FROM ' . FORUMS_TABLE . ' f ' .
            ($user->data['is_registered'] ? ' LEFT JOIN ' . FORUMS_WATCH_TABLE . ' fw ON (fw.forum_id = f.forum_id AND fw.user_id = ' . $user->data['user_id'] . ')' : '') .
            $forum_filter . '
            ORDER BY f.left_id ASC';
	    $result = $db->sql_query($sql, 600);

	    $forum_rows = array();
	    while ($row = $db->sql_fetchrow($result))
	    {
	        $forum_id = $row['forum_id'];
	        $forum_rows[$forum_id] = $row;
	    }
	    $this->display_select_forum($forum_rows,0,$key);
	    $strSelect .= '</select>';
	    return $strSelect;
	}

	function display_select_forum($rows,$parent_id,$key)
	{
		global $user, $config,$db,$strSelect;
		$selected = explode(',', $this->getTapatalkConfigValue($key));
		$i = 0;
		static $i;
		$topArr = $this->getChild($rows,$parent_id);
		foreach ($topArr as $info)
		{
			$strTag = '';
			for($j = 0;$j < $i;$j++)
			{
				$strTag .= '--';
			}
			$info['forum_name'] = $strTag . $info['forum_name'];
			$strSelect .= '<option value="' . $info['forum_id'] . '"' . ((in_array($info['forum_id'], $selected)) ? ' selected="selected"' : '') . '>' . $info['forum_name'] . '</option>';
			$childArr = $this->getChild($rows,$info['forum_id']);
			if(!empty($childArr))
			{
				$i++;
				$this->display_select_forum($rows, $info['forum_id'],$key);
				$i--;
			}
			else
			{
				continue;
			}
		}
	}
	function show_custom_replacement($value, $key)
    {
        global $phpbb_container, $config;
        $customReplace = $this->getTapatalkConfigValue('tapatalk_custom_replace');
        return '<textarea id="' . $key . '" name="config[' . $key . ']" rows=4>' . $customReplace . '</textarea>';
    }
	function select_register_group($value,$key)
	{
		global $db, $user, $config;

		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE . "
            WHERE group_name <> 'ADMINISTRATORS' AND group_name <> 'BOTS'
			ORDER BY group_type DESC, group_name ASC";
		$result = $db->sql_query($sql);

		$s_group_options = '<select id="' . $key . '" name="config[' . $key . ']"  >';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($row['group_id'] == $value) ? ' selected="selected"' : '';
			$s_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '"' . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);

		return $s_group_options.'</select>';
	}

	function select_mutil_group_box($value,$key)
	{
		global $db, $user, $config;
		$selected_arr = explode(',', $config[$key]);

		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE . "
			ORDER BY group_type DESC, group_name ASC";
		$result = $db->sql_query($sql);

		$s_group_options = '<select id="' . $key . '" name="config[' . $key . '][]"  multiple="multiple" size="8">';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = in_array($row['group_id'], $selected_arr) ? ' selected="selected"' : '';
			$s_group_options .= '<option value="' . $row['group_id'] . '"' . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);

		return $s_group_options.'</select>';
	}

	function select_register_status($value,$key)
	{
		global $user;
		$s_group_options = '<select id="' . $key . '" name="config[' . $key . ']"  >';
		for($i = 2;$i >= 0;$i--)
		{
			$selected = ($i == $value) ? ' selected="selected"' : '';
			if($i == 2)
			{
				$name = $user->lang['TAPATALK_REGISTER_STATUS_SSO'];
			}
			elseif($i == 1)
			{
				$name = $user->lang['TAPATALK_REGISTER_STATUS_NATIVE'];
			}
			else
			{
				$name = $user->lang['TAPATALK_REGISTER_STATUS_URL'];
			}
			$s_group_options .= '<option value="' . $i . '"' . $selected . '>'.$name.'</option>';
		}
		return $s_group_options.'</select>';
	}

	function select_spam_status($value,$key)
	{
		global $user;
		$s_group_options = '<select id="' . $key . '" name="config[' . $key . ']"  >';
		for($i = 0;$i < 4; $i++)
		{
			$selected = ($i == $value) ? ' selected="selected"' : '';
			$name = $user->lang['TAPATALK_SPAM_STATUS_' . $i];
			$s_group_options .= '<option value="' . $i . '"' . $selected . '>'.$name.'</option>';
		}
		return $s_group_options.'</select>';
	}

	function getChild($row,$parent_id)
	{
		$temp = array();
		foreach ($row as $info)
		{
			if($parent_id == $info['parent_id'])
			{
				$temp[] = $info;
			}
		}
		return $temp;
	}
    function getTextConfigKeys()
    {
        return array('tapatalk_custom_replace','tapatalk_forum_read_only','mobiquo_hide_forum_id');
    }
    function getTapatalkConfigValue($key)
    {
        global $config, $phpbb_container;
        if(in_array($key, $this->getTextConfigKeys()))
        {
            $value = $config[$key];
            if(empty($value))
            {
                $value = $phpbb_container->get('config_text')->get($key);
            }
            else
            {
                $phpbb_container->get('config_text')->set($key, $value);
                set_config($key, '');
            }
        }
        else
        {
            $value = $config[$key];
        }
        return $value;
    }
    function setTapatalkConfigValue($key, $value)
    {
        global $config, $phpbb_container;
        if(in_array($key, $this->getTextConfigKeys()))
        {
            $phpbb_container->get('config_text')->set($key, $value);
            set_config($key, '');
        }
        else
        {
            set_config($key, $value);
        }
        return $value;
    }
}
