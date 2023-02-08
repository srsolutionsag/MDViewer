<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Michelf\MarkdownExtra;
use ILIAS\Refinery\Transformation;
use ILIAS\HTTP\GlobalHttpState;

/**
 * Class ilMDViewerPluginGUI
 * @author            Fabian Schmid <fabian@sr.solution>
 * @author            Thibeau Fuhrer <thibeau@sr.solutions>
 * @ilCtrl_isCalledBy ilMDViewerPluginGUI: ilPCPluggedGUI
 */
class ilMDViewerPluginGUI extends ilPageComponentPluginGUI
{
    public const F_LINK_PREFIX = 'link_prefix_url';
    public const F_EXTERNAL_MD = 'external_md';
    public const F_BLOCKS_FILTER = 'filtered_blocks';
    public const F_SHOW_SOURCE = 'show_source';
    public const MODE_EDIT = 'edit';
    public const MODE_PREVIEW = 'preview';
    public const MODE_PRESENTATION = 'presentation';
    public const MODE_CREATE = "create";
    public const MODE_UPDATE = 'update';
    public const CMD_CANCEL = 'cancel';

    protected ilGlobalTemplateInterface $tpl;

    protected \ILIAS\Refinery\Factory $refinery;

    protected ilObjUser $user;

    protected GlobalHttpState $http;

    protected ilCtrl $ctrl;

    protected \ILIAS\DI\UIServices $ui;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->refinery = $DIC->refinery();

        parent::__construct();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::MODE_PREVIEW:
            case self::CMD_CANCEL:
                $this->{$cmd}();
                break;

            case self::MODE_EDIT:
            case self::MODE_CREATE:
            case self::MODE_UPDATE:
                $this->setMode($cmd);
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

    public function insert(): void
    {
        $this->showForm();
    }

    public function edit(): void
    {
        $this->showForm();
    }

    public function create(): void
    {
        $this->processForm();
    }

    public function update(): void
    {
        $this->processForm();
    }

    public function cancel(): void
    {
        $this->returnToParent();
    }

    public function getElementHTML(
        string $a_mode,
        array $a_properties,
        string $plugin_version
    ): string {
        if (!$this->isPresentationMode($a_mode)) {
            return $a_properties[self::F_EXTERNAL_MD];
        }

        /** @var $template ilTemplate */
        $template = $this->getPlugin()->getTemplate('tpl.output.html', true, true);
        $external_file = $a_properties[self::F_EXTERNAL_MD];
        $link_prefix = $a_properties[self::F_LINK_PREFIX];
        $link_prefix = ('' === $link_prefix) ?
            rtrim(dirname($external_file), "/") . "/" :
            $link_prefix;

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

        if (isset($a_properties[self::F_SHOW_SOURCE]) && '' !== $a_properties[self::F_SHOW_SOURCE]) {
            $template->setVariable('TEXT_INTRO', $this->getPlugin()->txt('box_intro_text'));
            $template->setVariable('TEXT_OUTRO', $this->getPlugin()->txt('box_outro_text'));
            $template->setVariable('HREF_ORIGINAL', $external_file);
            $template->setVariable('TEXT_ORIGINAL', $this->getPlugin()->txt('box_button_open'));
        }

        return $template->get();
    }

    protected function filterRawContentString(string $raw_content, array $blocks): string
    {
        // regex pattern matches anything in between '[//]: # (BEGIN <block name>)' and '[//]: # (END <block name>)'.
        // '{BLOCK_NAME}' has to be replaced with the actual block name.
        $regex_template = '/(?<=(\[\/\/\]:\s#\s\(BEGIN\s{BLOCK_NAME}\)))([\s\S]*)(?=(\[\/\/\]:\s#\s\(END\s{BLOCK_NAME}\)))/';

        $content = '';
        foreach ($blocks as $block) {
            // strip whitespaces and only process block if it's not empty.
            $block = $this->stripWhitespaces($block);
            $block = preg_quote($block, '\\');
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

    protected function initForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $properties = $this->getProperties();
        $inputs = [
            self::F_EXTERNAL_MD => $this->ui->factory()->input()->field()->text(
                $this->getPlugin()->txt('form_md'),
                $this->getPlugin()->txt('form_md_info')
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

        $inputs[self::F_SHOW_SOURCE] = $this->ui->factory()->input()->field()->checkbox(
            $this->getPlugin()->txt('form_show_source')
        )->withValue(
            (bool) ($properties[self::F_SHOW_SOURCE] ?? true)
        );

        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(
                self::class,
                ($this->isCreationMode()) ? self::MODE_CREATE : self::MODE_UPDATE
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
                self::F_SHOW_SOURCE => $data[self::F_SHOW_SOURCE],
            ];

            if ($this->areFilterBlocksEnabled()) {
                $properties[self::F_BLOCKS_FILTER] = $data[self::F_BLOCKS_FILTER];
            }

            if ($this->isCreationMode()) {
                $this->createElement($properties);
            } else {
                $this->updateElement($properties);
            }

            $this->tpl->setOnScreenMessage('success', $this->getPlugin()->txt("msg_saved"), true);
            $this->returnToParent();
        }

        $this->tpl->setOnScreenMessage('failure', $this->getPlugin()->txt("msg_invalid_url"));

        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $form
            )
        );
    }

    protected function showForm(): void
    {
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $this->initForm()
            )
        );
    }

    protected function getExternalUrlValidation(): Transformation
    {
        return $this->refinery->custom()->transformation(static function ($value) {
            if (preg_match('/^(https:\/\/raw\.githubusercontent\.com\/ILIAS.*\.md)$/', $value)) {
                return $value;
            }

            return null;
        });
    }

    protected function isCreationMode(): bool
    {
        return (
            self::MODE_CREATE === $this->ctrl->getCmd() ||
            ilPageComponentPlugin::CMD_INSERT === $this->getMode()
        );
    }

    protected function isPresentationMode(string $mode): bool
    {
        return (
            self::MODE_PRESENTATION === $mode ||
            self::MODE_PREVIEW === $mode
        );
    }

    protected function areFilterBlocksEnabled(): bool
    {
        return (bool) ilMDViewerConfig::getConfigValue(ilMDViewerConfig::KEY_MD_BLOCKS_FILTER_ACTIVE);
    }

    protected function stripWhitespaces(string $string): string
    {
        return (string) preg_replace('/\s/', '', $string);
    }
}
