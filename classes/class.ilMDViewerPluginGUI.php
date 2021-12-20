<?php
include_once("./Services/COPage/classes/class.ilPageComponentPluginGUI.php");
require_once('./Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/vendor/autoload.php');

use Michelf\MarkdownExtra;

/**
 * Class ilMDViewerPluginGUI
 * @author Fabian Schmid <fabian@sr.solution>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @ilCtrl_isCalledBy ilMDViewerPluginGUI: ilPCPluggedGUI
 */
class ilMDViewerPluginGUI extends ilPageComponentPluginGUI
{

    const F_LINK_PREFIX = 'link_prefix_url';
    const F_EXTERNAL_MD = 'external_md';
    const F_BLOCKS_FILTER = 'filtered_blocks';
    const MODE_EDIT = 'edit';
    const MODE_PRESENTATION = 'presentation';
    const MODE_CREATE = "create";
    const MODE_UPDATE = 'update';
    const CMD_CANCEL = 'cancel';

    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery;
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        if ($this->isVersionAboveSix()) {
            $this->refinery = $DIC->refinery();
        }

        parent::__construct();
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::MODE_PRESENTATION:
            case self::CMD_CANCEL:
                $this->{$cmd}();
                break;

            case self::MODE_EDIT:
            case self::MODE_CREATE:
            case self::MODE_UPDATE:
                if ($this->getPlugin()->isUserAuthorized($this->user->getId())) {
                    $this->{$cmd}();
                } else {
                    $this->cancel();
                }
                break;

            default:
                throw new LogicException("'$cmd' not found in " . self::class);
        }
    }

    public function insert()
    {
        $this->showForm();
    }

    public function edit()
    {
        $this->showForm();
    }

    public function create()
    {
        $this->processForm();
    }

    public function update()
    {
        $this->processForm();
    }

    public function cancel()
    {
        $this->returnToParent();
    }

    /**
     * @param string $a_mode
     * @param array $a_properties
     * @param string $a_plugin_version
     * @return string
     */
    public function getElementHTML($a_mode, array $a_properties, $a_plugin_version)
    {
        if (self::MODE_PRESENTATION !== $a_mode) {
            return $a_properties[self::F_EXTERNAL_MD];
        }

        /** @var $template ilTemplate */
        $template = $this->getPlugin()->getTemplate('tpl.output.html');
        $external_file = $a_properties[self::F_EXTERNAL_MD];
        $link_prefix = $a_properties[self::F_LINK_PREFIX];
        $link_prefix = ('' === $link_prefix) ?
            rtrim(dirname($external_file), "/") . "/" :
            $link_prefix
        ;

        $external_content_raw = @file_get_contents($external_file);
        if ($this->areFilterBlocksEnabled() && '' !== $this->stripWhitespaces($a_properties[self::F_BLOCKS_FILTER])) {
            $external_content_raw = $this->filterRawContentString(
                $external_content_raw,
                explode(',', $a_properties[self::F_BLOCKS_FILTER])
            );
        }

        $parser = new MarkdownExtra();
        $parser->url_filter_func = static function ($url) use ($link_prefix) {
            switch (true) {
                case (strpos($url, '.') === 0):
                case (strpos($url, '..') === 0):
                    return $link_prefix . $url;
                case (strpos($url, '#') === 0):
                default:
                    return $url;
            }
        };

        $template->setVariable('MD_CONTENT', $parser->transform($external_content_raw));
        $template->setVariable('TEXT_INTRO', $this->getPlugin()->txt('box_intro_text'));
        $template->setVariable('TEXT_OUTRO', $this->getPlugin()->txt('box_outro_text'));
        $template->setVariable('HREF_ORIGINAL', $external_file);
        $template->setVariable('TEXT_ORIGINAL', $this->getPlugin()->txt('box_button_open'));

        return $template->get();
    }

    /**
     * @param string $raw_content
     * @param array $blocks
     * @return string
     */
    protected function filterRawContentString($raw_content, $blocks)
    {
        // regex pattern matches anything in between '<!-- BEGIN <block name> -->' and '<!-- END <block name> -->'.
        // '{BLOCK_NAME}' has to be replaced with the actual block name.
        $regex_template = '/(?<=(<!--\sBEGIN\s{BLOCK_NAME}\s-->))([\s\S]*)(?=(<!--\sEND\s{BLOCK_NAME}\s-->))/';

        $content = '';
        foreach ($blocks as $block) {
            // strip whitespaces and only process block if it's not empty.
            $block = $this->stripWhitespaces($block);
            if ('' !== $block) {
                $regex = str_replace('{BLOCK_NAME}', $block, $regex_template);
                preg_match($regex, $raw_content, $matches);
                if (!empty($matches[0])) {
                    $content .= $matches[0];
                }
            }
        }

        return $content;
    }

    /**
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    protected function initForm()
    {
        $properties = $this->getProperties();
        $inputs = [
            self::F_EXTERNAL_MD => $this->ui->factory()->input()->field()->text(
                $this->getPlugin()->txt('form_md')
            )->withAdditionalTransformation(
                $this->getExternalUrlValidation()
            )->withValue(
                $properties[self::F_EXTERNAL_MD] ?? ''
            )->withRequired(
                true
            ),

            self::F_LINK_PREFIX => $this->ui->factory()->input()->field()->text(
                $this->getPlugin()->txt('form_link_prefix')
            )->withValue(
                $properties[self::F_LINK_PREFIX] ?? ''
            ),
        ];

        // only add blocks filter input if activated.
        if ($this->areFilterBlocksEnabled()) {
            $inputs[self::F_BLOCKS_FILTER] = $this->ui->factory()->input()->field()->text(
                $this->getPlugin()->txt('form_filter_blocks'),
                $this->getPlugin()->txt('form_info_filter_blocks')
            )->withValue(
                $properties[self::F_BLOCKS_FILTER] ?? ''
            );
        }

        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(
                self::class,
                (self::MODE_CREATE !== $this->getMode()) ? self::MODE_UPDATE : self::MODE_CREATE
            ),
            $inputs
        );
    }

    protected function processForm()
    {
        $form = $this->initForm();
        $form = $form->withRequest($this->http->request());
        $data = $form->getData();

        if (!empty($data) && null !== $data[self::F_EXTERNAL_MD]) {
            $properties = [
                self::F_EXTERNAL_MD => $data[self::F_EXTERNAL_MD],
                self::F_LINK_PREFIX => $data[self::F_LINK_PREFIX],
            ];

            if ($this->areFilterBlocksEnabled()) {
                $properties[self::F_BLOCKS_FILTER] = $data[self::F_BLOCKS_FILTER];
            }

            if (self::MODE_CREATE !== $this->getMode()) {
                $this->updateElement($properties);
            } else {
                $this->createElement($properties);
            }

            ilUtil::sendSuccess($this->getPlugin()->txt("msg_saved"), true);
            $this->returnToParent();
        }

        ilUtil::sendFailure($this->getPlugin()->txt("msg_invalid_url"));
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $form
            )
        );
    }

    protected function showForm()
    {
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $this->initForm()
            )
        );
    }

    /**
     * @return \ILIAS\Transformation\Transformation|\ILIAS\Refinery\Transformation
     */
    protected function getExternalUrlValidation()
    {
        $fn = static function ($value) {
            if (preg_match('/^(https:\/\/raw\.githubusercontent\.com\/ILIAS.*\.md)$/', $value)) {
                return $value;
            }

            return null;
        };

        if ($this->isVersionAboveSix()) {
            return $this->refinery->custom()->transformation(
                $fn
            );
        }

        return (new \ILIAS\Transformation\Factory())->custom($fn);
    }

    /**
     * @return bool
     */
    protected function isVersionAboveSix()
    {
        return (bool) version_compare('6.0', ILIAS_VERSION_NUMERIC, '<=');
    }

    /**
     * @return bool
     */
    protected function areFilterBlocksEnabled()
    {
        return (bool) ilMDViewerConfig::get(ilMDViewerConfig::KEY_MD_BLOCKS_FILTER_ACTIVE);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function stripWhitespaces($string)
    {
        return (string) preg_replace('/\s/', '', $string);
    }
}
