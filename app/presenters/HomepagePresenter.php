<?php

namespace App\Presenters;

use Nette;
use Nette\Security\Identity;
use Tracy\Debugger;



class HomepagePresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @var \Kdyby\Facebook\Facebook @inject
	 */
	public $facebook;

	/**
	 * @var int
	 */
	public $fbPageId;



	public function handlePublishPost()
	{
		$accounts = $this->facebook->api('/me/accounts', ['access_token' => $this->facebook->getAccessToken()]);
		foreach ($accounts->data as $page) {
			if ($page->id == $this->fbPageId) {
				$this->facebook->api('/' . $this->fbPageId . '/feed', 'POST', [
					'link' => 'https://www.kdyby.org/',
					'message' => 'testing publishing on page',
					'caption' => 'testing caption',
					'description' => 'testing description',
					'access_token' => $page->access_token,
				]);
			}
		}
	}



	/**
	 * @return \Kdyby\Facebook\Dialog\LoginDialog
	 */
	protected function createComponentFbLogin()
	{
		$dialog = $this->facebook->createDialog('login');
		/** @var \Kdyby\Facebook\Dialog\LoginDialog $dialog */

		$dialog->onResponse[] = function (\Kdyby\Facebook\Dialog\LoginDialog $dialog) {
			$fb = $dialog->getFacebook();

			if (!$fb->getUser()) {
				$this->flashMessage("Sorry bro, facebook authentication failed.");
				return;
			}

			try {
				$me = $fb->api('/me');
				$this->user->login(new Identity($me->id, [], (array) $me));

			} catch (\Kdyby\Facebook\FacebookApiException $e) {
				\Tracy\Debugger::log($e, 'facebook');
				$this->flashMessage("Sorry bro, facebook authentication failed hard.");
			}

			$this->redirect('this');
		};

		return $dialog;
	}

}
