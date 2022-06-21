<?php

namespace IPS\vexationmanager;

/**
 * Auth Node
 */
class _Auth extends \IPS\Content\Item
{
    /**
     * @brief	Application
     */
    public static $application = 'vexationmanager';

    /**
     * @brief	Module
     */
    public static $module = 'modcp';

    /**
     * @brief	[ActiveRecord] Multiton Store
     */
    protected static $multitons;

    /**
     * @brief	[Node] Node Title
     */
    public static $nodeTitle = 'auths';

    /**
     * @brief	[ActiveRecord] Database Table
     */
    public static $databaseTable = 'vexationmanager_auths';


    /**
     * @brief	SEO Title Column
     */
    public static $seoTitleColumn = 'code';

    /**
     * @brief	Database Column Map
     */
    public static $databaseColumnMap = array(
        'author' => 'member_id',
        'date' => 'updated_at',
        'title' => 'code',
    );


    /**
     * @brief	Title
     */
    public static $title = 'auth';

    /**
     * Delete Auth
     *
     * @return	void
     */
    public function delete()
    {
        // Delete auth
        parent::delete();

        // Delete the photo
        $this->deletePhoto();
    }

    /**
     * Verify auth.
     */
    public function verify()
    {

        $now = time();
        $member = \IPS\Member::load($this->member_id);

        // Update auth
        $this->updated_at = $now;
        $this->save();

        // Add user to groups
        $primaryGroup   = (int)\IPS\Settings::i()->vexationmanager_group;
        $secondaryGroup = (int)\IPS\Settings::i()->vexationmanager_group_secondary;

        if ($primaryGroup !== -1) {
            try {
                $group = \IPS\Member\Group::load($primaryGroup);
                $member->member_group_id = $primaryGroup;
            } catch(\Exception $e) {}
        }

        if ($secondaryGroup !== -1) {
            try {
                $group = \IPS\Member\Group::load($secondaryGroup);

                $secondaryGroups = $member->mgroup_others ? explode( ',', $member->mgroup_others ) : array();

                if (!in_array($secondaryGroup, $secondaryGroups)) {
                    $secondaryGroups[] = $secondaryGroup;
                }

                $member->mgroup_others = implode( ',', array_filter( $secondaryGroups ) );
            } catch(\Exception $e) {}
        }

        $member->save();

        // Notify user
        $notification = new \IPS\Notification( \IPS\Application::load('vexationmanager'), 'verified', $this, array($this) );
        $notification->recipients->attach( \IPS\Member::load($this->member_id) );
        $notification->send();
    }
}
