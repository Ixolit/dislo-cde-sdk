<?php

namespace Ixolit\Dislo\CDE\Form;

use Ixolit\Dislo\CDE\Validator\InArrayValidator;

class DropDownField extends ChoiceField  {
	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return 'dropdown';
	}
}