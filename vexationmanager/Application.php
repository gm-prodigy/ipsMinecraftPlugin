<?php
/**
 * @brief		Vexation Manager Application Class
 * @author		<a href=''>Gm Prodigy</a>
 * @copyright	(c) 2020 Gm Prodigy
 * @package		Invision Community
 * @subpackage	Vexation Manager
 * @since		24 May 2020
 * @version		
 */
 
namespace IPS\vexationmanager;

/**
 * Vexationmanager Application Class
 */
class _Application extends \IPS\Application
{
    /**
     * [Node] Get Icon for tree
     *
     * @note	Return the class for the icon (e.g. 'globe')
     * @return	string|null
     */
    protected function get__icon()
    {
        return 'vimeo';
    }

    
    /**
     * Create a random alphanumeric auth code.
     *
     * @return string
     */
    public static function createAuthCode()
    {


		function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
			$str = '';
			$count = mb_strlen($charset);
			while ($length--) {$str .= $charset[mt_rand(0, $count-1)];}
			return $str;
				}

        return randString(4) . "-" . randString(4) . "-" . randString(4);
    }

    /**
     * Get permission groups
     */
    public static function get_permission_groups()
    {
        
        $return = array();

        if (\IPS\Settings::i()->vexationmanager_permitted_groups &&
            \IPS\Settings::i()->vexationmanager_permitted_groups != -1) {
            $groups = explode( ',', \IPS\Settings::i()->vexationmanager_permitted_groups );

            foreach($groups as $group_id) {
                try
                {
                    $group = \IPS\Member\Group::load( $group_id );
                    $return[$group_id] = $group;
                }
                catch ( \OutOfRangeException $e )
                {
                }
            }
        }

        return $return;
    }

    /**
     * Connects to Minecraft Database.
     *
     * @return string
     */
    public static function minecraftSQL()
    {

       return \IPS\Db::i( 'external', array(
            'sql_host'        => \IPS\Settings::i()->vexationmanager_sql_host,
            'sql_user'        => \IPS\Settings::i()->vexationmanager_sql_user,
            'sql_pass'        => \IPS\Settings::i()->vexationmanager_sql_password,
            'sql_database'    => \IPS\Settings::i()->vexationmanager_sql_database,
            'sql_port'        => \IPS\Settings::i()->vexationmanager_sql_port,
            'sql_socket'    => '/var/lib/mysql.sock',
            'sql_utf8mb4'    => true,
        ) );



    }
    
}
