//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class vexationmanager_hook_codeMember extends _HOOK_CLASS_
{
    /**
     * @brief	Auth count
     */
    protected $authCount = NULL;

    /**
     * Validated whether the member can become
     * verified or not.
     *
     * @return bool
     */
    public function canBecomeVerified()
    {
	try
	{
	        $permissionGroups = \IPS\vexationmanager\Application::get_permission_groups();
	
        // Group list is empty: everyone can become
        // a verified member.
	        if (empty($permissionGroups)) {
	            return TRUE;
	        }
	
	        return $this->inGroup($permissionGroups);
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

    /**
     * Checks whether the user is verified or not.
     */
    public function isVerified()
    {
	try
	{
	        $primaryGroup   = (int)\IPS\Settings::i()->vexationmanager_group;
	        $secondaryGroup = (int)\IPS\Settings::i()->vexationmanager_group_secondary;
	
	        $inPrimaryGroup     = $primaryGroup !== -1 ? $this->inGroup($primaryGroup) : FALSE;
	        $inSecondaryGroup   = $secondaryGroup !== -1 ? $this->inGroup($secondaryGroup) : FALSE;
	
	        if ($primaryGroup !== -1 && $secondaryGroup !== -1) {
	            return $inPrimaryGroup && $inSecondaryGroup;
	        } else if ($primaryGroup !== -1) {
	            return $inPrimaryGroup;
	        }
	
	        return $inSecondaryGroup;
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
