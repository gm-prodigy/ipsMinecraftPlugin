//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class vexationmanager_hook_themeSystemSettings extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'settings' => 
  array (
    0 => 
    array (
      'selector' => '#elSettingsTabs > div.ipsColumns.ipsColumns_collapsePhone > div.ipsColumn.ipsColumn_wide > div.ipsBox.ipsPadding.sm:ipsPadding:half.ipsResponsive_pull.sm:ipsMargin_bottom > div.ipsSideMenu > ul.ipsSideMenu_list',
      'type' => 'add_inside_end',
      'content' => '{template="settings" group="hooks" app="vexationmanager" params="$tab"}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
