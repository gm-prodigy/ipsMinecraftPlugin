<?php

/**
 * @brief		Vexation User Link API
 * @author		Gm Prodigy
 * @since		24 May 2020
 */

namespace IPS\vexationmanager\api;

/* To prevent PHP errors (extending class does not exist) revealing path */

if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _link extends \IPS\Api\Controller
{
    /**
     * POST /vexationmanager/link/{uuid}
     * Link Minecraft account to Forums
     *
	 * @return	array
	 * @apiparam	string	uuid	Minecraft user ID
	 * @apiparam	string	authID	User's authID found on forums account
     * @throws      1VEX56/4    Invalid_UUID    User ID does not exist in database
     */
    public function POSTitem($uuid)
    {
        function bad_inputs($object) {
            return filter_var($object, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        }
        $badUUID = bad_inputs($uuid);

        try{
            $checkUUID = \IPS\vexationmanager\Application::minecraftSQL()->select('*', 'minecraft_luckperms_players', array('uuid=?', $badUUID));
            $info = array();

            foreach ($checkUUID as $k => $v) {
                $info[$k] = $v;
            }
            if (empty($info)) {
                return new \IPS\Api\Response( 404, array('Status'	=> "Invalid uuid") );
            }
        } catch (\OutOfRangeException $e) {
            return new \IPS\Api\Response( 404, array('Status'	=> "Invalid uuid") );
        }
        try {
            try {
                $userId = \IPS\Db::i()->select('*', 'vexationmanager_auths', array('code = ?', bad_inputs($_GET['authID'])))->first();

            } catch (\UnderflowException $e) {
                $userId = array();
                if (empty($userId)) {
                    return new \IPS\Api\Response( 404, array('Status'	=> "Invalid authID") );
                }
            }
            /* Load a specific member by ID */
            $member = \IPS\Member::load( $userId['member_id'] );

            $query = \IPS\vexationmanager\Application::minecraftSQL()->update( 'minecraft_luckperms_players', array( 'member_id' => $userId['member_id'] ), array( 'uuid=?', $badUUID ) );

            if ($query) {
//					return new \IPS\Api\Response(200, "Successful");
                $member->logHistory( 'vexationmanager', 'uuid_add', $badUUID );
                return new \IPS\Api\Response( 200, array('Status'	=> "Success") );

            } else{
                return new \IPS\Api\Response( 409, array('Status'	=> "You're Already Linked") );
            }
        } catch (\InvalidArgumentException $e) {
            return new \IPS\Api\Response( 400, array('Status'	=> "Bad Request") );
        }
    }
}