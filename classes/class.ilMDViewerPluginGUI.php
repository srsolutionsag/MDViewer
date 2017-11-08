<?php
include_once("./Services/COPage/classes/class.ilPageComponentPluginGUI.php");
require_once('./Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/vendor/autoload.php');
use GrahamCampbell\Markdown\Facades\Markdown;

/**
 * Class ilMDViewerPluginGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @ilCtrl_isCalledBy ilMDViewerPluginGUI: ilPCPluggedGUI
 */
class ilMDViewerPluginGUI extends ilPageComponentPluginGUI {

	const F_EXTERNAL_MD = 'external_md';
	const MODE_EDIT = 'edit';
	const MODE_PRESENTATION = 'presentation';


	public function executeCommand() {
		global $ilCtrl;
		/**
		 * @var $ilCtrl ilCtrl
		 */

		$next_class = $ilCtrl->getNextClass();

		switch ($next_class) {
			default:
				// perform valid commands
				$cmd = $ilCtrl->getCmd();
				if (in_array($cmd, array(
					"create",
					"save",
					self::MODE_EDIT,
					"edit2",
					"update",
					"cancel",
				))) {
					$this->$cmd();
				}
				break;
		}
	}


	public function insert() {
		global $tpl;

		$form = $this->initForm('create');
		$tpl->setContent($form->getHTML());
	}


	public function create() {
		global $tpl;

		$form = $this->initForm('create');
		if ($form->checkInput()) {
			$properties = array(
				self::F_EXTERNAL_MD => $form->getInput(self::F_EXTERNAL_MD),
			);
			if ($this->createElement($properties)) {
				ilUtil::sendSuccess($this->getPlugin()->txt("msg_saved"), true);
				$this->returnToParent();
			}
		}

		$form->setValuesByPost();

		$tpl->setContent($form->getHTML());
	}


	public function edit() {
		global $tpl;

		$form = $this->initForm('update');
		$form->setValuesByArray($this->getProperties());
		$tpl->setContent($form->getHTML());
	}


	public function update() {
		global $tpl;

		$form = $this->initForm('update');
		if ($form->checkInput()) {
			$properties = array(
				self::F_EXTERNAL_MD => $form->getInput(self::F_EXTERNAL_MD),
			);
			if ($this->updateElement($properties)) {
				ilUtil::sendSuccess($this->getPlugin()->txt("msg_saved"), true);
				$this->returnToParent();
			}
		}

		$form->setValuesByPost();

		$tpl->setContent($form->getHTML());
	}


	public function cancel() {
		$this->returnToParent();
	}


	/**
	 * Get HTML for element
	 *
	 * @param       $a_mode
	 * @param array $a_properties
	 * @param       $a_plugin_version
	 *
	 * @return mixed
	 */
	public function getElementHTML($a_mode, array $a_properties, $a_plugin_version) {
		//		global $DIC;
		//		$factory = $DIC->ui()->factory();
		//		$renderer = $DIC->ui()->renderer();
		switch ($a_mode) {
			case self::MODE_EDIT:
				//				$glyph = $renderer->render($factory->glyph()->settings());
				$glyph = '';

				return $glyph . $a_properties[self::F_EXTERNAL_MD];
			case self::MODE_PRESENTATION:
			default:

				$external_file = $a_properties[self::F_EXTERNAL_MD];
				$external_content_raw = @file_get_contents($external_file);
				/**
				 * @var $tpl ilTemplate
				 */
				$tpl = $this->getPlugin()->getTemplate('tpl.output.html');

				$parser = new \cebe\markdown\GithubMarkdown();
				$md_content = $parser->parse($external_content_raw);

				$tpl->setVariable('MD_CONTENT', $md_content);
				$tpl->setVariable('TEXT_INTRO', $this->getPlugin()->txt('box_intro_text'));
				$tpl->setVariable('TEXT_OUTRO', $this->getPlugin()->txt('box_outro_text'));
				$tpl->setVariable('HREF_ORIGINAL', $external_file);
				$tpl->setVariable('TEXT_ORIGINAL', $this->getPlugin()->txt('box_button_open'));

				return $tpl->get();
			//				return $renderer->render($factory->legacy($tpl->get()));
		}
	}


	/**
	 * @param string $mode create or update
	 * @return \ilPropertyFormGUI
	 */
	protected function initForm($mode = 'create') {
		global $ilCtrl;
		/**
		 * @var $ilCtrl ilCtrl
		 */
		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPlugin()->txt('form_title'));
		$form->addCommandButton($mode, $this->getPlugin()->txt("form_button_" . $mode));
		$form->addCommandButton("cancel", $this->getPlugin()->txt("form_button_cancel"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		$md = new ilTextInputGUI($this->getPlugin()->txt('form_md'), self::F_EXTERNAL_MD);
		$md->setValidationRegexp('/^https\\:\\/\\/raw\\.githubusercontent.com\\/ILIAS-.*\\.md/uUm');
		$md->setValidationFailureMessage('Only File ending with .md hosted somewhere beneath https://raw.githubusercontent.com/ILIAS-... are allowed');
		$form->addItem($md);

		return $form;
	}
}