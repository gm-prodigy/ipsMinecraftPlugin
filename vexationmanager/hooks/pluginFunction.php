//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class vexationmanager_hook_pluginFunction extends _HOOK_CLASS_
{
    public function unlinkUUID()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();
            $member = \IPS\Member::loggedIn();
//			sleep(10);
            try {
                $userInfo = \IPS\vexationmanager\Application::minecraftSQL()->select('*', 'minecraft_luckperms_players', array('member_id = ?', \IPS\Member::loggedIn()->member_id))->first();

            } catch (\UnderflowException $e) {
                $userInfo = array();
                $userInfo['uuid'];
            }

            $member->logHistory( 'vexationmanager', 'uuid_removed', $userInfo['uuid']);

			\IPS\vexationmanager\Application::minecraftSQL()->update( 'minecraft_luckperms_players', array( 'member_id' => null ), array( 'member_id=?', \IPS\Member::loggedIn()->member_id ) );



			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=auth', 'front', 'settings' ), 'Unlinked' )->csrf();
				
            \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'hooks', 'vexationmanager', 'front' )->requestUuidUnlink();
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

	public function changeAuthKey(){
		try{
			\IPS\Session::i()->csrfCheck();
//			sleep(10);
			$member_id = \IPS\Member::loggedIn()->member_id;
			\IPS\Db::i()->query("DELETE FROM vexationmanager_auths WHERE member_id=$member_id");

			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=auth', 'front', 'settings' ), 'Auth Key Updated' )->csrf();
		}catch ( \RuntimeException $e )
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
