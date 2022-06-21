<?php


namespace IPS\vexationmanager\modules\admin\vexationmanager;

/* To prevent PHP errors (extending class does not exist) revealing path */

use DomainException;
use IPS\Data\Cache;
use IPS\Db;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Helpers\Form;
use IPS\Helpers\Form\Select;
use IPS\Helpers\Form\Text;
use IPS\Helpers\Form\TextArea;
use IPS\Helpers\Wizard;
use IPS\Http\Url;
use IPS\Member;
use IPS\Member\Group;
use IPS\Output;
use IPS\Request;
use IPS\Session;
use IPS\Settings;
use IPS\Theme;
use IPS\vexationmanager\Application;
use RuntimeException;
use UnderflowException;
use Websender\WebsenderAPI;
use function is_array;
use const IPS\ROOT_PATH;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * settings
 */
class _settings extends Controller
{

    /**
	 * @brief	Has been CSRF-protected
	 */
	// public static $csrfProtected = TRUE;

    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');
        // \IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js('admin_settings.js', 'vexationmanager', 'admin' ) );

        parent::execute();
    }

    /**
     * Manage Settings
     *
     * @return    void
     */
    protected function manage()
    {
        // \IPS\Session::i()->csrfCheck();

        // Sidebar buttons
        Output::i()->sidebar['actions']['reconfigure'] = [
            'primary' => false,
            'icon' => 'cogs',
            'title' => 'vexationmanager_configuration_configure',
            'link' => \IPS\Http\Url::internal("app=vexationmanager&module=vexationmanager&controller=settings&do=advancedSetup")->csrf(),
        ];

        $groups_no_guests = array('-1' => 'vexationmanager_select_group');
        $groups_no_empty = [];
        foreach (Group::groups(TRUE, FALSE) as $k => $v) {
            $groups_no_guests[$k] = $v->name;
            $groups_no_empty[$k] = $v->name;
        }

        // Set page title
        Output::i()->title = Member::loggedIn()->language()->addToStack('settings');


        $form = new Form;
        $form_1 = new Form('form_1');
        $packages = Db::i()->select('*', 'nexus_packages', array());
        foreach ($packages as $pack) {
            $selectPackage["nexus_package_{$pack['p_id']}"] = "nexus_package_{$pack['p_id']}";
        }

        $form_1->add(new Select('vexation_sub_packages', explode(',', Settings::i()->vexation_sub_packages), FALSE, array(
            'options' => $selectPackage,
            'multiple' => TRUE,
        )));

        $form_1->add(new Text('vexationmanager_package_command', Settings::i()->vexationmanager_package_command, FALSE, array()));

        if ($values = $form_1->values(TRUE)) {
            $form_1->saveAsSettings($values);
            // Session::i()->log('acplogs__change_vexsettings');
            Output::i()->redirect(Url::internal('app=vexationmanager&module=vexationmanager&controller=settings')->csrf(), 'saved');
        }

        // $form = new \IPS\Helpers\Form;

        // $packages = \IPS\Db::i()->select('*', 'nexus_packages', array());
        // $selectPackage = array();

        // foreach ($packages as $pack) {
        //     $selectPackage["nexus_package_{$pack['p_id']}"] = "nexus_package_{$pack['p_id']}";
        // }

        // $form->add(new \IPS\Helpers\Form\Select('vexation_sub_packages', explode(',', \IPS\Settings::i()->vexation_sub_packages), FALSE, array(
        //     'options' => $selectPackage,
        //     'multiple' => true,)));

        $form->addButton('console', 'link', Url::internal("app=vexationmanager&module=vexationmanager&controller=settings&do=console")->csrf(), 'ipsButton ipsButton_positive', array('data-ipsDialog-title' => "Console"));

        $permission_groups = array_keys(Application::get_permission_groups());
        $form->add(new Select('vexationmanager_permitted_groups', empty($permission_groups) ? -1 : $permission_groups, TRUE, array(
            'options' => $groups_no_empty,
            'multiple' => TRUE,
            'unlimited' => -1,
        )));

        // Save values
        if ($values = $form->values()) {


            if (is_array($values['vexationmanager_permitted_groups'])) {
                $values['vexationmanager_permitted_groups'] = implode(',', $values['vexationmanager_permitted_groups']);
            }

            Cache::i()->clearAll();
            // Session::i()->log('acplogs__vexationmanager_settings');
            Output::i()->redirect(Url::internal('app=vexationmanager&module=vexationmanager&controller=settings')->csrf(), 'saved');
        }

        // Output the form
        Output::i()->output = $form_1 . '<br>' . $form;

    }

    protected function advancedSetup()
    {
        \IPS\Session::i()->csrfCheck();
        
        $form = new \IPS\Helpers\Form('form');
        $form->addHeader('console_client_connect');
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_host',\IPS\Settings::i()->vexationmanager_host, false));
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_password',\IPS\Settings::i()->vexationmanager_password, false, array( 'protect' => TRUE)));
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_port',\IPS\Settings::i()->vexationmanager_port, false));

        $form->addHeader('minecraft_database');
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_sql_host',\IPS\Settings::i()->vexationmanager_sql_host, false));
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_sql_user',\IPS\Settings::i()->vexationmanager_sql_user, false));
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_sql_password',\IPS\Settings::i()->vexationmanager_sql_password, false, array( 'protect' => TRUE)));
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_sql_database',\IPS\Settings::i()->vexationmanager_sql_database, false));
        $form->add(new \IPS\Helpers\Form\Text('vexationmanager_sql_port',\IPS\Settings::i()->vexationmanager_sql_port, false));

        if ($values = $form->values()) {
           \IPS\Settings::i()->changeValues($values);
           Output::i()->redirect(Url::internal('app=vexationmanager&module=vexationmanager&controller=settings')->csrf(), 'saved');   
        }

        \IPS\Output::i()->output = $form;
       
    }


    protected function console()
    {
        \IPS\Session::i()->csrfCheck();

        //        Output::i()->jsFiles = array_merge(Output::i()->jsFiles, Output::i()->js('admin_config.js', 'vexationmanager', 'admin'));

        \IPS\Output::i()->jsFiles = array_merge(
            \IPS\Output::i()->jsFiles,
            \IPS\Output::i()->js('admin_config.js', 'vexationmanager' )
        );

        /* This action access the console php file*/
        try {
            require_once(ROOT_PATH . '/applications/vexationmanager/interface/vexAPI/WebsenderAPI.php');
        } catch (RuntimeException $e) {
            throw $e;
        }


        $ctx = stream_context_create(array('http' =>
            array(
                'timeout' => 3,  //1200 Seconds is 20 Minutes
            )
        ));

        // $hostIP = \IPS\Settings::i()->vexationmanager_host;
        // $getContent = file_get_contents("http://$hostIP:5723/hub.php", false, $ctx);
        $getContent = false;

        if($getContent === FALSE) {
            $getContent = "failed to open stream: A connection attempt failed because the connected party did not properly respond after a period of time, or established connection failed because connected host has failed to respond.";
        }



        // $consoleLogs = "<textarea id='console1' style='margin-top: 0px; margin-bottom: 0px; width: 2000px; height: 900px; cursor: auto;'>$getContent</textarea>";


        $form = new Form('form', 'Send');


        $form->addHtml('<div class="ipsMessage ipsMessage_warning ipsJS_show">This message will be sent directly to Server Console.</div>');


        $form->add(new TextArea('console_content', NULL, TRUE, array('ipsDialog' => ''), function ($val) {
                $messageContent = $val;
                $host = \IPS\Settings::i()->vexationmanager_host;
                $password = \IPS\Settings::i()->vexationmanager_password;
                $port = \IPS\Settings::i()->vexationmanager_port;

                $wsr = new WebsenderAPI($host, $password, $port); // HOST , PASSWORD , PORT
                if ($wsr->connect()) { //Open Connect
                    $wsr->sendCommand($messageContent);
                } else {
                    throw new DomainException('Connection error! Check ip, pass and port.');
                }

                $wsr->disconnect(); //Close connection.

                Output::i()->redirect(Url::internal('app=vexationmanager&module=vexationmanager&controller=settings&do=console')->csrf(), 'sent');
            }));


        // \IPS\Output::i()->output= $consoleLogs . '<br>' . $form;

        // return \IPS\Theme::i()->getTemplate('configuration', 'vexationmanager' )->console($form);
        //         \IPS\Output::i()->jsFiles = array_merge(
        //             \IPS\Output::i()->jsFiles,
        //             \IPS\Output::i()->js('controllers/vexationmanager/ips.vexationmanager.console.js', 'vexationmanager' )
        //         );
        // \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'configuration', 'vexationmanager')->console($form, $getContent);
        return Output::i()->output = Theme::i()->getTemplate('configuration','vexationmanager')->console($form, $getContent);

    }
}
