<?php

/**
 * Class ilMDViewerPlugin
 * @author Fabian Schmid <fabian@sr.solutions>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilMDViewerPlugin extends ilPageComponentPlugin
{
    public const PLUGIN_NAME = "MDViewer";
    public const PLUGIN_ID = "md_tme";
    private ilObjUser $user;
    private ilRbacReview $rbacreview;

    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    public function __construct(ilDBInterface $db, ilComponentRepositoryWrite $component_repository, string $id)
    {
        global $DIC;
        parent::__construct($db, $component_repository, $id);

        if ($DIC->offsetExists('ilUser') && $DIC->offsetExists('rbacreview')) {
            $this->user = $DIC->user();
            $this->rbacreview = $DIC->rbac()->review();
        }
    }

    public function isValidParentType(string $a_type): bool
    {
        return $this->isUserAuthorized($this->user->getId());
    }

    public function isUserAuthorized(int $user_id): bool
    {
        $authorized_roles = ilMDViewerConfig::getConfigValue(ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES);
        if (!empty($authorized_roles)) {
            foreach ($authorized_roles as $authorized_role) {
                if ($this->rbacreview->isAssigned($user_id, $authorized_role)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getJavascriptFiles(string $a_mode): array
    {
        return [];
    }

    public function getCssFiles(string $a_mode): array
    {
        return [
            'templates/external-md.css',
        ];
    }

    protected function afterUninstall(): void
    {
        $this->db->dropTable((new ilMDViewerConfig())->getConnectorContainerName());
    }
}
