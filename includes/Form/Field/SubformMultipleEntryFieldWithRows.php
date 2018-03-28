<?php

namespace Catalyst\Form\Field;

use \Catalyst\Form\Form;

/**
 * Allows for a set of fields to be used in order to add to a collection
 * Use cases: commission type stages, payemnt options
 */
class SubformMultipleEntryFieldWithRows extends SubformMultipleEntryField {
	/**
	 * Return the field's HTML input
	 * 
	 * @return string The HTML to display
	 */
	public function getHtml() : string {
		$str = '';

		$str .= '<p';
		$str .= ' class="no-bottom-margin col s12">';

		$str .= '<strong>';
		$str .= htmlspecialchars($this->getLabel());
		$str .= '</strong>';

		$str .= '</p>';

		$str .= '<div';
		$str .= ' class="subform-entry-container col s12"';
		$str .= ' id="'.htmlspecialchars($this->getId()).'"';
		$str .= '>';

		$str .= '<div';
		$str .= ' class="subform-entry-sub-container col s12"';
		$str .= '>';

		$str .= '</div>';

		$str .= '</div>';

		$str .= '<button';
		$str .= ' type="button"';
		$str .= ' class="btn waves-effect waves-light add-sub-container-field-btn"';
		$str .= '>';

		$str .= "add row";
		
		$str .= '</button>';

		$str .= '<div';
		$str .= ' id="'.htmlspecialchars($this->getId()).'-subform"';
		$str .= '>';

		foreach ($this->getFields() as $field) {
			$field->setForm($this->getForm());
			$str .= $field->getHtml();
		}

		$str .= '<button';
		$str .= ' type="button"';
		$str .= ' class="btn waves-effect waves-light '.htmlspecialchars($this->getAdditionButtonClasses()).'"';
		$str .= '>';

		$str .= "add";
		
		$str .= '</button>';

		$str .= '</div>';

		return $str;
	}

	/**
	 * Return JS code to store the field's value in $formDataName
	 * 
	 * @param string $formDataName The name of the FormData variable
	 * @return string Code to use to store field in $formDataName
	 */
	public function getJsAggregator(string $formDataName) : string {
		$str = '';

		$str .= 'for (var entry of $('.json_encode('#'.$this->getId()).').find('.json_encode(".".self::ENTRY_ITEM).')) {';

		$str .= 'var itemData = JSON.stringify($(entry).attr("data-data"));';
		$str .= 'itemData["row"] = $(entry).index();';
		$str .= $formDataName.'.append('.json_encode($this->getDistinguisher().'[]').', JSON.parse(itemData));';

		$str .= '}';

		return $str;
	}

	/**
	 * Check the field's forms on the servers side
	 * 
	 * @param array $requestArr Array to find the form data in
	 */
	public function checkServerSide(?array &$requestArr=null) : void {
		if (is_null($requestArr)) {
			$requestArr = &$_REQUEST;
		}
		if ($this->isRequired()) {
			if (!array_key_exists($this->getDistinguisher(), $requestArr)) {
				$this->throwMissingError();
			}
		} else {
			if (!array_key_exists($this->getDistinguisher(), $requestArr)) {
				return;
			}
		}
		if (!is_array($requestArr[$this->getDistinguisher()])) {
			$this->throwInvalidError();
		}
		foreach ($requestArr[$this->getDistinguisher()] as &$entry) {
			if (json_decode($entry) === false) {
				$this->throwInvalidError();
			}
			$entry = json_decode($entry, true);
			if (!is_numeric($entry["row"])) {
				$this->throwInvalidError();
			}
			if ($entry["row"] < 0) {
				$this->throwInvalidError();
			}
			foreach ($this->getFields() as $field) {
				$field->setForm($this->getForm());
				$field->checkServerSide($entry);
			}
		}
	}
}
