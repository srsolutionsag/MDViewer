<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Class ilMDViewerConfigGUI
 * @author studer + raimann ag - Team Core 1 <support-core1@studer-raimann.ch>
 */
class ilMDViewerConfigGUI extends ilPluginConfigGUI
{

    const CMD_CONFIGURE = 'configure';
    const CMD_SAVE = 'save';
    const CMD_CANCEL = 'cancel';

    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;
    /**
     * @var \ILIAS\DI\RBACServices
     */
    protected $rbac;
    /**
     * @var ilMDViewerPlugin
     */
    protected $plugin;
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    private $lng;
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * ilMDViewerConfigGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->rbac = $DIC->rbac();
        $this->plugin = $this->getPluginObject() ?? (new ilMDViewerPlugin());
        $this->ui = $DIC->ui();
    }

    /**
     * @param string $cmd
     */
    public function performCommand($cmd)
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
    public function configure()
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
    public function save()
    {
        $form = $this->initForm();
        $form = $form->withRequest($this->http->request());
        $data = $form->getData();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                ilMDViewerConfig::set(
                    $key,
                    $value
                );
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
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
    public function cancel()
    {
        $this->ctrl->redirectByClass(ilObjComponentSettingsGUI::class);
    }

    /**
     * Initialize configuration-form
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    protected function initForm()
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
                    ilMDViewerConfig::get(ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES) ?? ''
                ),

                // checkbox input for activating the blocks filter.
                ilMDViewerConfig::KEY_MD_BLOCKS_FILTER_ACTIVE => $this->ui->factory()->input()->field()->checkbox(
                    $this->plugin->txt('config_blocks_filter')
                )->withByline(
                    $this->plugin->txt('config_info_blocks_filter')
                )->withValue(
                    ilMDViewerConfig::get(ilMDViewerConfig::KEY_MD_BLOCKS_FILTER_ACTIVE) ?? false
                ),
            ]
        );
    }

    /**
     * @return array<int, string>
     */
    protected function getConfigurableRoles()
    {
        $roles = [];
        foreach ($this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL) as $role_data) {
            $role_name = ilObjRole::_getTranslation($role_data['title']);
            $role_id = (int) $role_data['obj_id'];

            $roles[$role_id] = $role_name;
        }

        return $roles;
    }

    /**
     * @deprecated
     * @param int  $filter
     * @param bool $with_text
     * @return array
     */
    public static function getRoles($filter, $with_text = true)
    {
        global $DIC;
        $opt = array(0 => 'Login');
        $role_ids = array(0);
        foreach ($DIC->rbac()->review()->getRolesByFilter($filter) as $role) {
            $opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
            $role_ids[] = $role['obj_id'];
        }
        if ($with_text) {
            return $opt;
        } else {
            return $role_ids;
        }
    }
}
