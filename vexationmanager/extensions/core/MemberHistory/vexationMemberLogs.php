<?php
/**
 * @brief		MemberHistory: vexationMemberLogs
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Vexation Manager
 * @since		16 Jul 2020
 */

namespace IPS\vexationmanager\extensions\core\MemberHistory;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Member History: vexationMemberLogs
 */
class _vexationMemberLogs
{
	/**
	 * Return the valid member history log types
	 *
	 * @return array
	 */
	public function getTypes()
	{
        return [
            'uuid_change',
            'uuid_add',
            'uuid_removed',
            'uuid_used_onPurchase'
        ];
	}

	/**
	 * Parse LogType column
	 *
	 * @param	string		$value		column value
	 * @param	array		$row		entire log row
	 * @return	string
	 */
	public function parseLogType( $value, $row )
	{
        return \IPS\Theme::i()->getTemplate( 'members', 'core' )->logType( $value );
	}

	/**
	 * Parse LogData column
	 *
	 * @param	string		$value		column value
	 * @param	array		$row		entire log row
	 * @return	string
	 */
	public function parseLogData( $value, $row )
	{
        {
            $jsonValue = json_decode( $value, TRUE );

            switch( $row['log_type'] )
            {
                case 'uuid_change':
                    return \IPS\Member::loggedIn()->language()->addToStack( "Changed UUID To: $value", FALSE, array());
                case 'uuid_add':
                    return \IPS\Member::loggedIn()->language()->addToStack( "Added UUID: $value", FALSE, array());
                case 'uuid_removed':
                    return \IPS\Member::loggedIn()->language()->addToStack( "Removed UUID: $value", FALSE, array());
                case 'uuid_used_onPurchase':
                    return \IPS\Member::loggedIn()->language()->addToStack( "Member bought $jsonValue[Package] with UUID: $jsonValue[UUID]", FALSE, array());
            }

            return $value;
        }
	}
}