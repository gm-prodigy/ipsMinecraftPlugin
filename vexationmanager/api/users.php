<?php

/**
 * @brief		Vexation User Info
 * @author		Gm Prodigy
 * @since		24 May 2020
 */

namespace IPS\vexationmanager\api;

/* To prevent PHP errors (extending class does not exist) revealing path */

if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

class _users extends \IPS\Api\Controller
{
	/**
	 * GET /vexationmanager/users/{uuid}
	 * Get infomation about a specific uuid
	 *
	 * @return	array
	 * @apiresponse	string	uuid	Minecraft user ID
     * @throws      1VEX56/4    Invalid_UUID    The uuid is invald
	 */
	public function GETitem($uuid)
	{
        
        try{
            $results = \IPS\vexationmanager\Application::minecraftSQL()->select('*', 'minecraft_luckperms_players', array('uuid=?', $uuid));
            $info = array();
            // $info['member_id'];
            // $info['username'];
            // $info['uuid'];
            // $info['primary_group'];
    
            foreach ($results as $k => $v) {
                $info[$k] = $v;
            }
            if (empty($info)) {
                throw new \IPS\Api\Exception('Invalid uuid', '1VEX292/2', 404);
            }
            return new \IPS\Api\Response(200, $info[0]);
        } catch (\OutOfRaangeException $e) {
            throw new \IPS\Api\Exception('INVALID_UUID', '1VEX344/3', 404);
        }
	}
}

