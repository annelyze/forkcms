<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the settings-action, it will display a form to set general pages settings
 *
 * @author Tijs Verkoyen <tijs@sumocoders.be>
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendPagesSettings extends BackendBaseActionEdit
{
	/**
	 * Is the user a god user?
	 *
	 * @var bool
	 */
	protected $isGod = false;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}

	/**
	 * Loads the settings form
	 */
	private function loadForm()
	{
		$this->isGod = BackendAuthentication::getUser()->isGod();

		// init settings form
		$this->frm = new BackendForm('settings');

		// add fields for meta navigation
		$this->frm->addCheckbox('meta_navigation', BackendModel::getModuleSetting($this->getModule(), 'meta_navigation', false));

		// god user?
		if($this->isGod) $this->frm->addText('frontend_image_size', BackendModel::getModuleSetting($this->getModule(), 'frontend_image_size', null));
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		// parse additional variables
		$this->tpl->assign('isGod', $this->isGod);
	}

	/**
	 * Validates the settings form
	 */
	private function validateForm()
	{
		// form is submitted
		if($this->frm->isSubmitted())
		{
			// get new image size
			$newImageSize = ($this->frm->getField('frontend_image_size')->isFilled() ? $this->frm->getField('frontend_image_size')->getValue() : null);

			// validate image size format
			if($this->isGod && !empty($newImageSize) && (int) preg_match('/^([0-9]+x[0-9]*|[0-9]*x[0-9]+)$/', $newImageSize) <= 0)
			{
				$this->frm->getField('frontend_image_size')->addError(BL::err('InvalidImageSize'));
			}

			// form is validated
			if($this->frm->isCorrect())
			{
				// set our settings
				BackendModel::setModuleSetting($this->getModule(), 'meta_navigation', (bool) $this->frm->getField('meta_navigation')->getValue());
				if($this->isGod) BackendModel::setModuleSetting($this->getModule(), 'frontend_image_size', $newImageSize);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_saved_settings');

				// redirect to the settings page
				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}
