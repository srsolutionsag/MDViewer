<?php

use ILIAS\HTTP\GlobalHttpState;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Class ilMDViewerConfigGUI
 * @author Fabian Schmid <fabian@sr.solutions>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilMDViewerConfigGUI: ilObjComponentSettingsGUI
 */
class ilMDViewerConfigGUI extends ilPluginConfigGUI
{
    public const CMD_CONFIGURE = 'configure';
    public const CMD_SAVE = 'save';
    public const CMD_CANCEL = 'cancel';

    protected GlobalHttpState $http;

    protected \ILIAS\DI\RBACServices $rbac;

    protected ?\ilMDViewerPlugin $plugin = null;

    protected \ILIAS\DI\UIServices $ui;

    protected ilCtrl $ctrl;

    private ilLanguage $lng;

    protected ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->rbac = $DIC->rbac();
        $this->plugin = $this->getPluginObject()?? $DIC["component.factory"]->getPlugin(
            ilMDViewerPlugin::PLUGIN_ID
        );
        $this->ui = $DIC->ui();
    }

    public function performCommand(string $cmd): void
    {
        switch ($cmd) {
            case self::CMD_CONFIGURE:
            case self::CMD_SAVE:
            case self::CMD_CANCEL:
                $this->{$cmd}();
                break;
        }
    }

    /**
     * Show configuration page
     */
    public function configure(): void
    {
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $this->initForm()
            )
        );
    }

    /**
     * Save configuration
     */
    public function save(): void
    {
        $form = $this->initForm();
        $form = $form->withRequest($this->http->request());
        $data = $form->getData();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                ilMDViewerConfig::setConfigValue(
                    $key,
                    $value
                );
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, self::CMD_CONFIGURE);
        }

        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $form
            )
        );
    }

    /**
     * Return to plugin list
     */
    public function cancel(): void
    {
        $this->ctrl->redirectByClass(ilObjComponentSettingsGUI::class);
    }

    /**
     * Initialize configuration-form
     */
    protected function initForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE),
            [
                // multi select input for roles that shall be allowed to use the plugin.
                ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES => $this->ui->factory()->input()->field()->multiSelect(
                    $this->plugin->txt('config_authorized_roles'),
                    $this->getConfigurableRoles()
                )->withByline(
                    $this->plugin->txt('config_info_authorized_roles')
                )->withValue(
                    ilMDViewerConfig::getConfigValue(ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES) ?? []
                ),

                // checkbox input for activating the blocks filter.
                ilMDViewerConfig::KEY_MD_BLOCKS_FILTER_ACTIVE => $this->ui->factory()->input()->field()->checkbox(
                    $this->plugin->txt('config_blocks_filter')
                )->withByline(
                    $this->plugin->txt('config_info_blocks_filter')
                )->withValue(
                    ilMDViewerConfig::getConfigValue(ilMDViewerConfig::KEY_MD_BLOCKS_FILTER_ACTIVE) ?? false
                ),
            ]
        );
    }

    /**
     * @return array<int, string>
     */
    protected function getConfigurableRoles(): array
    {
        $roles = [];
        foreach ($this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL) as $role_data) {
            $role_name = ilObjRole::_getTranslation($role_data['title']);
            $role_id = (int) $role_data['obj_id'];

            $roles[$role_id] = $role_name;
        }

        return $roles;
    }
}
