<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Matthias Mullie <forkcms@mullie.eu>
 * @author Tijs Verkoyen <tijs@sumocoders.be>
 * @author Davy Hellemans <davy.hellemans@netlash.com>
 * @author Jelmer Snoeck <jelmer@siphoc.com>
 * @author Annelies Van Extergem <annelies@annelyze.be>
 */
class BackendPagesAdd extends BackendBaseActionAdd
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// add js
		$this->header->addJS('jstree/jquery.tree.js', null, false);
		$this->header->addJS('jstree/lib/jquery.cookie.js', null, false);
		$this->header->addJS('jstree/plugins/jquery.tree.cookie.js', null, false);

		// add css
		$this->header->addCSS('/backend/modules/pages/js/jstree/themes/fork/style.css', null, true);

		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('add');

		// create elements
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');

		// meta
		$this->meta = new BackendMeta($this->frm, null, 'title', true);
		$this->meta->setURLCallback('BackendPagesModel', 'getURL', array(0, null, false));
	}

	/**
	 * Parse
	 */
	protected function parse()
	{
		parent::parse();

		// parse some variables
		$this->tpl->assign('prefixURL', rtrim(BackendPagesModel::getFullURL(1), '/'));

		// parse the form
		$this->frm->parse($this->tpl);

		// parse the tree
		$this->tpl->assign('tree', BackendPagesModel::getTreeHTML());
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// set callback for generating an unique URL
			$this->meta->setURLCallback('BackendPagesModel', 'getURL');

			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));

			// validate meta
			$this->meta->validate();

			// no errors?
			if($this->frm->isCorrect())
			{
				// init var
				$parentId = 0;
				$data = null;
				$defaultTemplateId = BackendModel::getModuleSetting('pages', 'default_template', false);

				// is no default template is found, just get the first template
				if($defaultTemplateId === false)
				{
					$firstTemplate = array_shift(BackendExtensionsModel::getTemplates());
					$defaultTemplateId = $firstTemplate['id'];
				}

				// build page record
				$page['id'] = BackendPagesModel::getMaximumPageId() + 1;
				$page['user_id'] = BackendAuthentication::getUser()->getUserId();
				$page['parent_id'] = $parentId;
				$page['template_id'] = (int) $defaultTemplateId;
				$page['meta_id'] = (int) $this->meta->save();
				$page['language'] = BackendLanguage::getWorkingLanguage();
				$page['type'] = 'root';
				$page['title'] = $this->frm->getField('title')->getValue();
				$page['navigation_title'] = $this->frm->getField('title')->getValue();
				$page['navigation_title_overwrite'] = 'N';
				$page['hidden'] = 'N';
				$page['status'] = 'draft';
				$page['publish_on'] = BackendModel::getUTCDate();
				$page['created_on'] = BackendModel::getUTCDate();
				$page['edited_on'] = BackendModel::getUTCDate();
				$page['allow_move'] = 'Y';
				$page['allow_children'] = 'Y';
				$page['allow_edit'] = 'Y';
				$page['allow_delete'] = 'Y';
				$page['sequence'] = BackendPagesModel::getMaximumSequence($parentId) + 1;
				$page['data'] = null;

				// set navigation title
				if($page['navigation_title'] == '') $page['navigation_title'] = $page['title'];

				// insert page, store the id, we need it when building the blocks
				$page['revision_id'] = BackendPagesModel::insert($page);

				// trigger an event
				BackendModel::triggerEvent($this->getModule(), 'after_add', $page);

				// build the cache
				BackendPagesModel::buildCache(BL::getWorkingLanguage());

				// everything is saved, so redirect to the edit action
				$this->redirect(BackendModel::createURLForAction('edit') . '&id=' . $page['id'] . '&report=saved-as-draft&var=' . urlencode($page['title']) . '&highlight=row-' . $page['revision_id'] . '&draft=' . $page['revision_id']);
			}
		}
	}
}
