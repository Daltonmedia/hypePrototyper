<?php

namespace hypeJunction\Prototyper;

class ValidationStatus {

	protected $status;
	protected $messages = array();

	function __construct($status = true, $messages = array()) {
		$this->status = (bool) $status;
		$this->messages = (is_array($messages)) ? $messages : array();
	}
	
	private function setStatus($status = true) {
		$this->status = $status;
	}

	public function setFail($message = '') {
		$this->setStatus(false);
		$this->addMessage($message);
	}

	public function setSuccess($message = '') {
		$this->setStatus(true);
		$this->addMessage($message);
	}

	public function addMessage($message = '') {
		if ($message) {
			$this->messages[] = $message;
		}
	}

	public function getStatus() {
		return (bool) $this->status;
	}

	public function getMessages() {
		return $this->messages;
	}

	public function isValid() {
		return $this->getStatus() !== false;
	}

}
