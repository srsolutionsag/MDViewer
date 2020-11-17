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
        global $rbacreview, $ilUser;

        $authorized_roles = ilMDViewerConfig::get(ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES);
        if (!empty($authorized_roles)) {
            foreach ($authorized_roles as $authorized_role) {
                /**
                 * @var $rbacreview ilRbacReview
                 */
                if ($rbacreview->isAssigned($ilUser->getId(), $authorized_role)) {
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

}

