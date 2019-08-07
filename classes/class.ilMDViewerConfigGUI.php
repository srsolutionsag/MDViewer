<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Class ilMDViewerConfigGUI
 *
 * @author studer + raimann ag - Team Core 1 <support-core1@studer-raimann.ch>
 */
class ilMDViewerConfigGUI extends ilPluginConfigGUI
{

    const CMD_CONFIGURE = 'configure';
    const CMD_SAVE = 'save';
    const CMD_CANCEL = 'cancel';
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
     * @var ilPropertyFormGUI
     */
    private $config_form;


    /**
     * ilMDViewerConfigGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
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
        $this->initForm();
        $this->fillForm();
        $this->tpl->setContent($this->config_form->getHTML());
    }


    /**
     * Save configuration
     */
    public function save()
    {
        ilMDViewerConfig::set(
            ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES,
            $_POST[ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES]
        );
        ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, self::CMD_CONFIGURE);
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
     */
    protected function initForm()
    {
        // form
        $this->config_form = new ilPropertyFormGUI();
        $this->config_form->setFormAction($this->ctrl->getFormAction($this));
        $this->config_form->setTitle($this->getPluginObject()->txt('config_configuration'));

        // multi select input for roles that shall be allowed to use the plugin
        $authorized_roles = new ilMultiSelectInputGUI(
            $this->getPluginObject()->txt("config_authorized_roles"),
            ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES
        );
        $authorized_roles->setOptions(self::getRoles(ilRbacReview::FILTER_ALL_GLOBAL));
        $authorized_roles->setInfo($this->getPluginObject()->txt("config_info_authorized_roles"));
        $authorized_roles->setRequired(true);
        $this->config_form->addItem($authorized_roles);

        // save and cancel buttons
        $this->config_form->addCommandButton(self::CMD_SAVE, $this->lng->txt('save'));
        $this->config_form->addCommandButton(self::CMD_CANCEL, $this->lng->txt('cancel'));
    }


    public function fillForm()
    {
        $array = array(
            ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES => ilMDViewerConfig::get(
                ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES
            ),
        );
        $this->config_form->setValuesByArray($array);
    }


    /**
     * @param int  $filter
     * @param bool $with_text
     *
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