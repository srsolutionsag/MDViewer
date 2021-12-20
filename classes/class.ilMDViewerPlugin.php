<?php
include_once("./Services/COPage/classes/class.ilPageComponentPlugin.php");
require_once('./Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/classes/class.ilMDViewerPluginGUI.php');

/**
 * Class ilMDViewerPlugin
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMDViewerPlugin extends ilPageComponentPlugin
{
    const PLUGIN_NAME = "MDViewer";

    /**
     * Get plugin name
     * @return string
     */
    public function getPluginName()
    {
        return self::PLUGIN_NAME;
    }

    /**
     * @param string $a_parent_type
     * @return bool
     */
    public function isValidParentType($a_parent_type)
    {
        /** @var $ilUser ilObjUser */
        global $ilUser;

        return $this->isUserAuthorized($ilUser->getId());
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public function isUserAuthorized($user_id)
    {
        /** @var $rbacreview ilRbacReview */
        global $rbacreview;

        $authorized_roles = ilMDViewerConfig::get(ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES);
        if (!empty($authorized_roles)) {
            foreach ($authorized_roles as $authorized_role) {
                if ($rbacreview->isAssigned($user_id, $authorized_role)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $a_mode
     * @return array
     */
    public function getJavascriptFiles($a_mode)
    {
        return [];
    }

    /**
     * @param $a_mode
     * @return array
     */
    public function getCssFiles($a_mode)
    {
        return [
            'templates/external-md.css',
        ];
    }

    protected function afterUninstall()
    {
        global $DIC;

        $DIC->database()->dropTable((new ilMDViewerConfig)->getConnectorContainerName());
    }

}

