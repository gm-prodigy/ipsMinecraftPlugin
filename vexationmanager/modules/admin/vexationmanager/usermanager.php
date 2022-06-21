<?php


namespace IPS\vexationmanager\modules\admin\vexationmanager;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * usermanager
 */
class _usermanager extends \IPS\Dispatcher\Controller
{

	/**
	 * @brief	Has been CSRF-protected
	 */
	public static $csrfProtected = TRUE;

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'usermanager_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{

//		\IPS\Db::i();


		/* Create the table */

		$table = new \IPS\Helpers\Table\Db('minecraft_luckperms_players', \IPS\Http\Url::internal('app=vexationmanager&module=vexationmanager&controller=usermanager'));
		$table->langPrefix = 'vexation_usermanager_';
		$table->include = array('photo', 'member_id', 'username', 'primary_group');

		$table->noSort	= array('photo');
		// $table->noSort	= array('active');
		$table->mainColumn = 'member_id';
		$table->parsers = array(
			'photo' => function ($val, $row) {
				$member = \IPS\Member::load($row['member_id']);
				return \IPS\Theme::i()->getTemplate('global', 'core')->userPhoto($member, 'tiny');
			},
			'member_id' => function ($val, $row) {
				$member = \IPS\Member::load($val);
				return $member->link();
			},
			'uuid' => function ($val, $row) {
				return mb_substr($row['uuid'], 0, 10) . "...";
			},				
			// 'active' => function ($val, $row) {
			// 	$testuuid = $row['uuid'];
			// 	$info = \IPS\Db::i()->query("SELECT * FROM minecraft_litebansbans WHERE uuid='$testuuid'");
			// 	$get_data = mysqli_fetch_array($info);
			// $info1 = $get_data['active'];
			// 	return $info1;
			// },
		);
		$table->sortBy = $table->sortBy ?: 'member_id';
		$table->sortDirection = $table->sortDirection ?: 'desc';

		/* Search */
		$table->quickSearch = 'username';
		$table->advancedSearch	= array(
			'member_id'	=> \IPS\Helpers\Table\SEARCH_MEMBER,
			'uuid'	=> \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT,
			'primary_group'	=> \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT
		
		);

		/* Controls */
		$table->rowButtons = function ($row) {
			$buttons = [];
			$testid = $row['member_id'];
			$testuuid = $row['uuid'];
			$info = \IPS\vexationmanager\Application::minecraftSQL()->query("SELECT * FROM minecraft_litebansbans WHERE uuid='$testuuid' ORDER BY active DESC");
			$get_data = mysqli_fetch_array($info);
			$info1 = $get_data['active'];
			

				if($row['member_id'] == null){

				}
				else{
					if ($row['member_id'] != \IPS\Member::loggedIn()->member_id) {
					$buttons['ban_member'] = [
						'icon'		=> 'gavel',
						'title'		=> 'Ban Member',
						'link'	=> \IPS\Http\Url::internal('app=core&module=members&controller=members&do=ban&id=') . $row['member_id'],
						'data'	=> array('ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('ban_member'))
					];
					}
					$buttons['transactions'] = [
						'icon'		=> 'credit-card',
						'title'		=> 'View History',
						'link'	=> \IPS\Http\Url::internal('app=core&module=members&controller=members&do=history&id=') . $row['member_id'],
						'data'	=> array('ipsDialog' => '', 'ipsDialog-size' => 'narrow', 'ipsDialog-size' => 'medium', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('user_history'))
					];
				}
				
				$buttons['edit'] = [
					'icon'		=> 'edit',
					'title'		=> 'Edit',
					'link'	=> \IPS\Http\Url::internal('app=vexationmanager&module=vexationmanager&controller=usermanager&do=edit&id=') . $row['uuid'],
					'data'	=> array('ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('edit_member'))
				];

				if ($info1 == 1){
					$buttons['unban'] = [
						'icon'		=> 'unlock',
						'title'		=> 'Un Ban uuid',
						'id'		=> "$testuuid-ban",
						'link'	=> \IPS\Http\Url::internal('app=vexationmanager&module=vexationmanager&controller=usermanager&do=unban&id=') . $row['uuid'],
						'data'	=> array('ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('ban test'))
					];
					
				} elseif($info1 == 0) {
					$buttons['ban'] = [
						'icon'		=> 'lock',
						'title'		=> 'Ban uuid',
						'id'		=> "$testuuid-ban",
						'link'	=> \IPS\Http\Url::internal('app=vexationmanager&module=vexationmanager&controller=usermanager&do=ban&id=') . $row['uuid'],
						'data'	=> array('ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('ban test'))
					];
					
				}
			return $buttons;
		};

		/* Display */

		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('title__vexationmanager_vexationmanager_usermanager');
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate('global', 'core')->block('title', (string) $table);
	}


    protected function edit()
    {
        /* Decode json fields */
        $permsSelect = \IPS\vexationmanager\Application::minecraftSQL()->select('*', 'minecraft_luckperms_groups', array());
        $permsArray = array();

        foreach ($permsSelect as $perms) {
            $permsArray["{$perms['name']}"] = "{$perms['name']}";
        }


        $id = \IPS\Request::i()->id;
        $member = \IPS\Member::load($id);
        try {
            $userInfo = \IPS\vexationmanager\Application::minecraftSQL()->select('*', 'minecraft_luckperms_players', array('uuid = ?', $id))->first();

        } catch (\UnderflowException $e) {
            $userInfo = array();
            $userInfo['uuid'] = $userInfo['primary_group'] = $userInfo['username'] = $userInfo['member_id'];
        }

        /* This action access the console php file*/
        try {
            require_once(\IPS\ROOT_PATH . '/applications/vexationmanager/interface/vexAPI/WebsenderAPI.php');
        } catch (\RuntimeException $e) {
            throw $e;
        }


        $host = \IPS\Settings::i()->vexationmanager_host;
        $password = \IPS\Settings::i()->vexationmanager_password;
        $port = \IPS\Settings::i()->vexationmanager_port;

        $form = new \IPS\Helpers\Form($id);
        $form->addTab('user_settings');
        $form->addHeader('uuid_info');
        $form->add(new \IPS\Helpers\Form\Text('uuid', $userInfo['uuid'], FALSE, array(
            'maxLength' => 36,
            'minLength' => 32,
            'disabled' => TRUE)));
        $form->addHeader('primary_group');

        $form->add(new \IPS\Helpers\Form\Select('primary_group', explode(',', $userInfo['primary_group']), FALSE, array(
            'options' => $permsArray,
            'multiple' => FALSE,
        )));



        $form->addHeader('username');
        $form->add(new \IPS\Helpers\Form\Text('username', $userInfo['username'], FALSE, array('disabled' => TRUE)));
        $form->addTab('user_permissions');
        $form->addHeader('placeholder');
//        $form->add(new \IPS\Helpers\Form\YesNo('can_access_vex_userpanel', $member->can_access_vex_userpanel, FALSE, array(), NULL, NULL, NULL, 'can_access_vex_userpanel'));


        if ($values = $form->values()) {
            $profileFields = array();
            $profileFields['uuid'] = $values['uuid'];
            // $profileFields['member_id'] = $values['member_id'];
            $profileFields['username'] = $values['username'];
            // $profileFields['primary_group'] = $values['primary_group'];
            \IPS\Db::i()->replace('minecraft_luckperms_players', array_merge(array('member_id' => $userInfo['member_id']), $profileFields));

            $selectedGroup = $values['primary_group'];

            $wsr = new \Websender\WebsenderAPI($host,$password,$port); // HOST , PASSWORD , PORT
            if($wsr->connect()){ //Open Connect
                $wsr->sendCommand("lp user $id parent set $selectedGroup");
            }else{
                throw new \DomainException('Connection error! Check ip, pass and port.');
            }

            $wsr->disconnect(); //Close connection.

            // $member->can_access_vex_userpanel = $values['can_access_vex_userpanel'];
            // $member->save();

            \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=vexationmanager&module=vexationmanager&controller=usermanager'), 'saved');
        }

        \IPS\Output::i()->output = $form;
    }


    protected function ban()
	{
		/* This action access the console php file*/
		try {
			require_once(\IPS\ROOT_PATH . '/applications/vexationmanager/interface/vexAPI/WebsenderAPI.php');
		} catch (\RuntimeException $e) {
			throw $e;
		}
		$forumsName = \IPS\Member::loggedIn()->name;
		$id = \IPS\Request::i()->id;
		$now = time();


		$form = new \IPS\Helpers\Form($id);	
		$form->addTab('Ban User');
		$form->addHtml('<div class="ipsMessage ipsMessage_warning ipsJS_show">Do you really want to ban user?</div>');
		$form->add(new \IPS\Helpers\Form\Text('reason'));
		$form->add(new \IPS\Helpers\Form\Date('until'));
		$form->add(new \IPS\Helpers\Form\YesNo('silent'));



        $host = \IPS\Settings::i()->vexationmanager_host;
        $password = \IPS\Settings::i()->vexationmanager_password;
        $port = \IPS\Settings::i()->vexationmanager_port;

		if ($values = $form->values()) {

			$reason = $values['reason'];
			

			if ($values['silent'] == 1){
				$silent = "-s";
			} else {
				$silent = "";
			}

			if($values['until'] == null){
				$time = -1;
			}else {
				$now = time();
				$count = strtotime($values['until']);
				$day_diff = $count - $now;
				$time = floor($day_diff/(60*60*24))."d";
			}
			

			$wsr = new \Websender\WebsenderAPI($host,$password,$port); // HOST , PASSWORD , PORT
			if($wsr->connect()){ //Open Connect          
				$wsr->sendCommand("ban $silent $id $time $reason "); 
			}else{
				throw new \DomainException('Connection error! Check ip, pass and port.');
			}
			
			$wsr->disconnect(); //Close connection.

			\IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=vexationmanager&module=vexationmanager&controller=usermanager'), 'banned');
		}
		\IPS\Output::i()->output = $form;
	}

	protected function unban()
    {
		$id = \IPS\Request::i()->id;
				/* This action access the console php file*/
		try {
			require_once(\IPS\ROOT_PATH . '/applications/vexationmanager/interface/vexAPI/WebsenderAPI.php');
		} catch (\RuntimeException $e) {
			throw $e;
		}


        $host = \IPS\Settings::i()->vexationmanager_host;
        $password = \IPS\Settings::i()->vexationmanager_password;
        $port = \IPS\Settings::i()->vexationmanager_port;


		$wsr = new \Websender\WebsenderAPI($host,$password,$port); // HOST , PASSWORD , PORT
		if($wsr->connect()){ //Open Connect          
			$wsr->sendCommand("unban $id"); 
		}else{
			throw new \DomainException('Connection error! Check ip, pass and port.');
		}
		
		$wsr->disconnect(); //Close connection.

		\IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=vexationmanager&module=vexationmanager&controller=usermanager'), 'unbanned');
		
	}
}