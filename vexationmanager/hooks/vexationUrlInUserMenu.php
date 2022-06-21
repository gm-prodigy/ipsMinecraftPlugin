//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class vexationmanager_hook_vexationUrlInUserMenu extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'userBar' => 
  array (
    0 => 
    array (
      'selector' => '#elUserLink_menu > li.ipsMenu_item[data-menuitem=\'profile\']',
      'type' => 'add_after',
      'content' => '<li class=\'ipsMenu_item\' data-menuItem=\'auth\'><a href=\'{url="app=core&module=system&controller=settings&area=auth" seoTemplate="settings_auth"}\'><span class="fa fa-user"></span> Vex Auth</a></li>

        
',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */
}
