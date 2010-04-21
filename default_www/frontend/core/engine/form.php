<?php

/**
 * FrontendForm, this is our extended version of SpoonForm.
 *
 * @package		frontend
 * @subpackage	core
 *
 * @author 		Davy Hellemans <davy@netlash.com>
 * @since		2.0
 */
class FrontendForm extends SpoonForm
{
	/**
	 * The header instance
	 *
	 * @var	FrontendHeader
	 */
	private $header;


	/**
	 * The URL instance
	 *
	 * @var	FrontendURL
	 */
	private $URL;


	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	string $name				Name of the form.
	 * @param	string[optional] $action	The action (URL) whereto the form will be submitted, if not provided it will be autogenerated.
	 * @param	string[optional] $method	The method to use when submiting the form, default is POST.
	 * @param	string[optional] $hash		The id of the anchor to append to the action-URL.
	 * @param	bool[optional] $useToken	Should we automagically add a formtoken?
	 */
	public function __construct($name, $action = null, $method = 'post', $hash = null, $useToken = true)
	{
		// init some properties
		$this->URL = Spoon::getObjectReference('url');
		$this->header = Spoon::getObjectReference('header');

		// redefine
		$name = (string) $name;
		$hash = ($hash !== null) ? (string) $hash : null;
		$useToken = (bool) $useToken;

		// build the action if it wasn't provided
		$action = ($action === null) ? '/'. $this->URL->getQueryString() : (string) $action;

		// append hash
		if($hash !== null) $action .'#'. $hash;

		// call the real form-class
		parent::__construct($name, $action, $method, $useToken);

		// add default classes
		$this->setParameter('id', $name);
		$this->setParameter('class', 'forkForms submitWithLink');
	}


	/**
	 * Adds a button to the form
	 *
	 * @return	SpoonButton
	 * @param	string $name				Name of the button.
	 * @param	string $value				The value (or label) that will be printed.
	 * @param	string[optional] $type		The type of the button (submit is default).
	 * @param	string[optional] $class		Class(es) that will be applied on the button.
	 */
	public function addButton($name, $value, $type = 'submit', $class = null)
	{
		// redefine
		$name = (string) $name;
		$value = (string) $value;
		$type = (string) $type;
		$class = ($class !== null) ? (string) $class : 'inputText inputButton';

		// do a check, only enable this if we use forms that are submitted with javascript
		if($type == 'submit' && $name == 'submit') throw new FrontendException('You can\'t add buttons with the name submit. JS freaks out when we replace the buttons with a link and use that link to submit the form.');

		// call the real form class
		return parent::addButton($name, $value, $type, $class);
	}


	/**
	 * Adds a single checkbox.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	bool[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addCheckbox($name, $checked = false, $class = null, $classError = null)
	{
		// redefine
		$name = (string) $name;
		$checked = (bool) $checked;
		$class = ($class !== null) ? (string) $class : 'inputCheckbox';
		$classError = ($classError !== null) ? (string) $classError : 'inputCheckboxError';

		// return element
		return parent::addCheckbox($name, $checked, $class, $classError);
	}


	/**
	 * Adds a datefield to the form
	 * a datepicker will be available by default.
	 *
	 * @return	SpoonDateField
	 * @param	string $name					Name of the element
	 * @param	int[optional] $value			The value for the element
	 * @param	string[optional] $type			The type (from, till, range) of the datepicker
	 * @param	int[optional] $date				The date to use.
	 * @param	int[optional] $date2			The second date for a rangepicker.
	 * @param	string[optional] $class			Class(es) that have to be applied on the element.
	 * @param	string[optional] $classError	Class(es) that have to be applied when an error occurs on the element.
	 */
	public function addDate($name, $value = null, $type = null, $date = null, $date2 = null, $class = null, $classError = null)
	{
		// redefine
		$name = (string) $name;
		$value = ($value !== null) ? (int) $value : null;
		$type = SpoonFilter::getValue($type, array('from', 'till', 'range'), 'none');
		$date = ($date !== null) ? (int) $date : null;
		$date2 = ($date2 !== null) ? (int) $date2 : null;
		$class = ($class !== null) ? (string) $class : 'inputText inputDate';
		$classError = ($classError !== null) ? (string) $classError : 'inputTextError inputDateError';

		// validate
		if($type == 'from' && ($date == 0 || $date == null)) throw new FrontendException('A datefield with type "from" should have a valid date-parameter.');
		if($type == 'till' && ($date == 0 || $date == null)) throw new FrontendException('A datefield with type "till" should have a valid date-parameter.');
		if($type == 'range' && ($date == 0 || $date2 == 0 || $date == null || $date2 == null)) throw new FrontendException('A datefield with type "range" should have 2 valid date-parameters.');

		// @later	get prefered mask & first day
		$mask = 'd/m/Y';
		$firstday = 1;

		// rebuild mask
		$relMask = str_replace(array('d', 'm', 'Y', 'j', 'n'), array('dd', 'mm', 'yy', 'd', 'm'), $mask);

		// build rel
		$rel = $relMask .':::'. $firstday;

		// add extra classes based on type
		switch($type)
		{
			case 'from':
				$class .= ' inputDatefieldFrom';
				$classError .= ' inputDatefieldFrom';
				$rel .= ':::'. date('Y-m-d', $date);
			break;

			case 'till':
				$class .= ' inputDatefieldTill';
				$classError .= ' inputDatefieldTill';
				$rel .= ':::'. date('Y-m-d', $date);
			break;

			case 'range':
				$class .= ' inputDatefieldRange';
				$classError .= ' inputDatefieldRange';
				$rel .= ':::'. date('Y-m-d', $date) .':::'. date('Y-m-d', $date2);
			break;

			default:
				$class .= ' inputDatefieldNormal';
				$classError .= ' inputDatefieldNormal';
			break;
		}

		// call parent
		parent::addDate($name, $value, $mask, $class, $classError);

		// set attributes
		parent::getField($name)->setAttributes(array('rel' => $rel));

		// fetch field
		return parent::getField($name);
	}


	/**
	 * Adds a single dropdown.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	string[optional] $selected
	 * @param	bool[optional] $multipleSelection
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addDropdown($name, array $values, $selected = null, $multipleSelection = false, $class = null, $classError = null)
	{
		// redefine
		$name = (string) $name;
		$values = (array) $values;
		$selected = ($selected !== null) ? (string) $selected : null;
		$multipleSelection = (bool) $multipleSelection;
		$class = ($class !== null) ? (string) $class : 'select';
		$classError = ($classError !== null) ? (string) $classError : 'selectError';

		// special classes for multiple
		if($multipleSelection)
		{
			$class .= ' selectMultiple';
			$classError .= ' selectMultipleError';
		}

		// return element
		return parent::addDropdown($name, $values, $selected, $multipleSelection, $class, $classError);
	}


	/**
	 * Adds a single file field.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addFile($name, $class = null, $classError = null)
	{
		// redefine
		$name = (string) $name;
		$class = ($class !== null) ? (string) $class : 'inputFile';
		$classError = ($classError !== null) ? (string) $classError : 'inputFileError';

		// return element
		return parent::addFile($name, $class, $classError);
	}


	/**
	 * Adds a single image field.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addImage($name, $class = null, $classError = null)
	{
		// redefine
		$name = (string) $name;
		$class = ($class !== null) ? (string) $class : 'inputFile inputImage';
		$classError = ($classError !== null) ? (string) $classError : 'inputFileError inputImageError';

		// return element
		return parent::addImage($name, $class, $classError);
	}


	/**
	 * Adds a single multiple checkbox.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	bool[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addMultiCheckbox($name, array $values, $checked = null, $class = null, $classError = null)
	{
		// redefine
		$name = (string) $name;
		$values = (array) $values;
		$checked = ($checked !== null) ? (bool) $checked : null;
		$class = ($class !== null) ? (string) $class : 'inputCheckbox';
		$classError = ($classError !== null) ? (string) $classError : 'inputCheckboxError';

		// return element
		return parent::addMultiCheckbox($name, $values, $checked, $class, $classError);
	}


	/**
	 * Adds a single password field.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addPassword($name, $value = null, $maxlength = null, $class = null, $classError = null, $HTML = false)
	{
		// redefine
		$name = (string) $name;
		$value = ($value !== null) ? (string) $value : null;
		$maxlength = ($maxlength !== null) ? (int) $maxlength : null;
		$class = ($class !== null) ? (string) $class : 'inputText inputPassword';
		$classError = ($classError !== null) ? (string) $classError : 'inputTextError inputPasswordError';
		$HTML = (bool) $HTML;

		// return element
		return parent::addPassword($name, $value, $maxlength, $class, $classError, $HTML);
	}


	/**
	 * Adds a single radiobutton.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	string[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addRadiobutton($name, array $values, $checked = null, $class = null, $classError = null)
	{
		// redefine
		$name = (string) $name;
		$values = (array) $values;
		$checked = ($checked !== null) ? (string) $checked : null;
		$class = ($class !== null) ? (string) $class : 'inputRadio';
		$classError = ($classError !== null) ? (string) $classError : 'inputRadioError';

		// return element
		return parent::addRadiobutton($name, $values, $checked, $class, $classError);
	}


	/**
	 * Adds a single textarea.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addTextarea($name, $value = null, $class = null, $classError = null, $HTML = false)
	{
		// redefine
		$name = (string) $name;
		$value = ($value !== null) ? (string) $value : null;
		$class = ($class !== null) ? (string) $class : 'textarea';
		$classError = ($classError !== null) ? (string) $classError : 'textareaError';
		$HTML = (bool) $HTML;

		// return element
		return parent::addTextarea($name, $value, $class, $classError, $HTML);
	}


	/**
	 * Adds a single textfield.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addText($name, $value = null, $maxlength = null, $class = null, $classError = null, $HTML = false)
	{
		// redefine
		$name = (string) $name;
		$value = ($value !== null) ? (string) $value : null;
		$maxlength = ($maxlength !== null) ? (int) $maxlength : null;
		$class = ($class !== null) ? (string) $class : 'inputText';
		$classError = ($classError !== null) ? (string) $classError : 'inputTextError';
		$HTML = (bool) $HTML;

		// return element
		return parent::addText($name, $value, $maxlength, $class, $classError, $HTML);
	}


	/**
	 * Adds a single timefield.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addTime($name, $value = null, $class = null, $classError = null)
	{
		$name = (string) $name;
		$value = ($value !== null) ? (string) $value : null;
		$class = ($class !== null) ? (string) $class : 'inputText inputTime';
		$classError = ($classError !== null) ? (string) $classError : 'inputTextError inputTimeError';

		// return element
		return parent::addTime($name, $value, $class, $classError);
	}


	/**
	 * Generate a token for usage in the forms
	 *
	 * @return	string
	 */
	public static function generateToken()
	{
		// generate a secret value
		$token = md5(SpoonSession::getSessionId() . time());

		// store in session
		SpoonSession::set('form_token', $token);

		// return
		return $token;
	}


	/**
	 * Fetches all the values for this form as key/value pairs
	 *
	 * @return	array
	 * @param	mixed[optional] $excluded		Which elements should be excluded?
	 */
	public function getValues($excluded = array('form', 'save'))
	{
		return parent::getValues($excluded);
	}


	/**
	 * Parse the form
	 *
	 * @return	void
	 * @param	FrontendTemplate $tpl	The template instance wherein the form will be parsed.
	 */
	public function parse(SpoonTemplate $tpl)
	{
		// parse the form
		parent::parse($tpl);

		// validate the form
		$this->validate();

		// if the form is submitted but there was an error, assign a general error
		if($this->isSubmitted() && !$this->isCorrect()) $tpl->assign('formError', true);
	}
}

?>