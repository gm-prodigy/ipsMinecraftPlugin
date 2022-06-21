//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class vexationmanager_hook_updateRank extends _HOOK_CLASS_
{
    /**
     * On Purchase Generated
     *
     * @param \IPS\nexus\Purchase $purchase The purchase
     * @param \IPS\nexus\Invoice $invoice The invoice
     * @return    void
     */
    public function onPurchaseGenerated(\IPS\nexus\Purchase $purchase, \IPS\nexus\Invoice $invoice)
    {

        try {

            
            // $member = \IPS\Member::loggedIn();
            // $getpackage = \IPS\Settings::i()->vexation_sub_packages;
            // $stripPackageName = array("nexus_package_");
            // $stripedPackageName = \str_replace($stripPackageName, "", "$getpackage");

//            if ( \in_array( $this->id,  array($stripedPackageName)) ) {
//            if ( \in_array( $this->id,  array($stripedPackageName)) ) {
           // if ( \in_array( $this->id,  array(1,2)) ) {

                /* Checks if logged in user has a uuid before the transaction is complete*/
                // try {
                //     $userInfo = \IPS\vexationmanager\Application::minecraftSQL()->select('*', 'minecraft_luckperms_players',
                //         array('member_id = ?',
                //             \IPS\Member::loggedIn()->member_id))->first();
                // } catch (\UnderflowException $e) {
                //     $userInfo = array();
                //     if (empty($userInfo['uuid'])) {
                //         \IPS\Output::i()->error('no_uuid_store', '2B302/1', 403, '');
                //     }
                // }
                /* This is dirty, but left it because I was lazy*/


//                try {
//                    $userLogs = \IPS\Db::i()->select('*', 'core_member_history',
//                        array('log_type=? AND log_member=?', 'uuid_used_onPurchase', \IPS\Member::loggedIn()->member_id))->first();

//                    $userLogs = \IPS\vexationmanager\Application::minecraftSQL()->query("select * from core_member_history where json_contains(`data`, '{UUID : 2016-04-26}')");
//                } catch (\UnderflowException $e) {
//                    $userLogs = array();
//                }

                // $member_uuid = \strval($userInfo['uuid']);
                // $getCurrentpackageName = $invoice->title;





                /* Grab required file to create connection to game server*/
                // try {
                //     require_once(\IPS\ROOT_PATH . '/applications/vexationmanager/interface/vexAPI/WebsenderAPI.php');
                // } catch (\RuntimeException $e) {
                //     throw $e;
                // }

                /* Garbs console login details from Application*/
                // $host = \IPS\Settings::i()->vexationmanager_host;
                // $password = \IPS\Settings::i()->vexationmanager_password;
                // $port = \IPS\Settings::i()->vexationmanager_port;
                // $commandMsg = \IPS\Settings::i()->vexationmanager_package_command;

                /* Echoing random data in server console*/
                // $testing = $this->id;

                /* Should be a better way of doing this right?*/


                // $wsr = new \Websender\WebsenderAPI($host,$password,$port); // HOST , PASSWORD , PORT
                // if($wsr->connect()){ //Open Connect
                    /* Sends server commands*/
                  //$wsr->sendCommand(sprintf("$commandMsg", $member_uuid, $getCurrentpackageName));
//                  $wsr->sendCommand(sprintf("$commandMsg",
//                      $member_uuid,$getCurrentpackageName));
                  /* Sends Test server commands*/
                    // $wsr->sendCommand("say Package ID: $userLogs[log_data]");
//                  $wsr->sendCommand("say Package Title: $getCurrentpackageName");
//                  $wsr->sendCommand("say All Package ID: $stripedPackageName");
//                  $wsr->sendCommand("say All Package without being stripped: $getpackage");
                // }else{
                    /* Throws output before the transaction is complete*/
                    // \IPS\Output::i()->error('Something went wrong while updating your role, You have not been charged', '3C108/1', 403, '')	;
                // }
                // $wsr->disconnect(); //Close connection.

            // $member->logHistory( 'vexationmanager', 'uuid_used_onPurchase', array(
            //     'Package' => $invoice->title,
            //     'UUID'	=> $member_uuid
            // ) );

//            $member->logHistory( 'vexationmanager', 'uuid_add', $new_uuid );


//            }
            return parent::onPurchaseGenerated($purchase, $invoice);
        }
        catch ( \RuntimeException $e )
        {
            if ( method_exists( get_parent_class(), __FUNCTION__ ) )
            {
                return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
            }
            else
            {
                throw $e;
            }
        }

    }

    /**
     * @param \IPS\nexus\Purchase $purchase
     * @param \IPS\nexus\Package $newPackage
     * @param null $chosenRenewalOption
     * @return mixed
     */
    public function onChange(\IPS\nexus\Purchase $purchase, \IPS\nexus\Package $newPackage, $chosenRenewalOption = NULL)
    {
        try
        {
            try {
                $userLogs = \IPS\Db::i()->select('*', 'core_member_history',
                    array('log_type=? AND log_member=?', 'uuid_used_onPurchase', \IPS\Member::loggedIn()->member_id))->first();
            } catch (\UnderflowException $e) {
                $userLogs = array();
            }


            return parent::onPurchaseGenerated($purchase, $newPackage, $chosenRenewalOption);
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
