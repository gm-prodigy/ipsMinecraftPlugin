//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class vexationmanager_hook_codeCoreSystemSettings extends _HOOK_CLASS_
{
    /**
     * Auth
     *
     * @return	string
     */
    protected function _auth()
    {
	try
	{
        \IPS\Output::i()->jsFiles = array_merge(
            \IPS\Output::i()->jsFiles,
            \IPS\Output::i()->js('front_config.js', 'vexationmanager' )
        );
			

	        // if (!$member->canBecomeVerified()) {
	        //     \IPS\Output::i()->error( 'node_error', '2L182/2', 404, '' );
	        // }
	
	        $now = time();
	
        // Try to load the Auth code of the current user.
        // If no Auth code could be found we create a new
        // Auth code for the user and display it. Otherwise
        // we will take the existing code.
	        try {
	            $databasePrefix = \IPS\vexationmanager\Auth::$databasePrefix;
	            $where = array($databasePrefix . 'vexationmanager_auths.member_id = ?', \IPS\Member::loggedIn()->member_id);
	
	            $auth = \IPS\vexationmanager\Auth::getItemsWithPermission($where, $order = 'id', $limit = 1)->first();
	            $auth = \IPS\vexationmanager\Auth::constructFromData($auth);
	        } catch(\UnderflowException $e) {
	            $auth = new \IPS\vexationmanager\Auth();
	            $auth->member_id = \IPS\Member::loggedIn()->member_id;
	            $auth->code = \IPS\vexationmanager\Application::createAuthCode();
	            $auth->created_at = $now;
	            $auth->updated_at = $now;
	
            // Handle users who have already the configured group
            // and prevent empty rows.
	            if(!\IPS\Member::loggedIn()->isVerified()) {
	                $auth->save();
	            }
			}
			
			try {
				$userInfo = \IPS\vexationmanager\Application::minecraftSQL()->select('*', 'minecraft_luckperms_players', array('member_id = ?', \IPS\Member::loggedIn()->member_id))->first();
	
			} catch (\UnderflowException $e) {
				$userInfo = array();
				$userInfo['uuid'] = $userInfo['primary_group'] = $userInfo['username'] = $userInfo['member_id'] = null;
			}


//			$form = new \IPS\Helpers\Form;
//			$form->add(new \IPS\Helpers\Form\Text('username', null, null, array(), function($val) use ($userInfo) {
//			    $member_id = \IPS\Member::loggedIn()->member_id;
//			    $Info1 = \IPS\Db::i()->select('*', 'minecraft_luckperms_players', array('member_id = ?', \IPS\Member::loggedIn()->member_id))->first();
//
//				if($Info1 == false){
//                    $info2 = \IPS\Db::i()->query("SELECT member_id FROM minecraft_luckperms_players WHERE username='$val'");
//					if($info2 == false){
//						\IPS\Db::i()->update( 'minecraft_luckperms_players', array( 'member_id' => \IPS\Member::loggedIn()->member_id ), array( 'username=?', $val ) );
//
//						\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=auth', 'front', 'settings' ), "done" );
//					}else{
//                        \IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=auth', 'front', 'settings' ), "They already have a linked account!" );
//                    }
//				}else{
//                    \IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=auth', 'front', 'settings' ), "You already have a linked account!" );
//                }
//			}));



			// $form->add( new \IPS\Helpers\Form\Text( 'username', NULL, TRUE, array(), function( $val ){
			// 	try{
			// 		\IPS\Db::i()->update( 'minecraft_luckperms_players', array( 'member_id' => \IPS\Member::loggedIn()->member_id ), array( 'username=?', $val ) );
			// 		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=auth', 'front', 'settings' ), "done" );
			// 	}catch(\RuntimeException $e){
			// 	}
			// }));

			// if(mb_strpos($getUsername, '*') !== false){
			// 	// echo "<i class='fa fa-vimeo'></i>" and $getusername;
				
			// 	$getusernamereplace = substr_replace("*",$getUsername,0);
			
			// 	// return "<i class='fab fa-xbox'></i>";
			// 	$username = "<i class='fab fa-xbox'></i>" and $getusernamereplace;
			// }


			$username = $userInfo['username'];
			$primary_group = $userInfo['primary_group'];
			$uuid = $userInfo['uuid'];
			
			

			\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'settings.js', 'vexationmanager', 'front' ) );
			\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'settings.css', 'vexationmanager', 'general' ) );
			// \IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'general/settings.css', 'vexationmanager' ) );
	        return \IPS\Theme::i()->getTemplate( 'hooks', 'vexationmanager' )->settingsAuth($auth, $username, $primary_group, $uuid);
	}
	catch ( \RuntimeException $e )
	{
		if ( method_exists( get_parent_class(), __FUNCTION__ ) )
		{
			return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
		}
		else
		{
			throw $e;
		}
	}
    }
}
