<?php
/**
 * Logo Configuration File
 *
 * This is a PHP file that sets up variables specific for a template.
 * It can also be used to run PHP code for a template.
 *
 */

class Gifts_logo extends LogoMaker
{
	/**
	 * TextFieldCount
	 * If a logo uses a by-line or similar, this can come in handy
	*/
	public $TextFieldCount = 1;

	/**
	 * Name of the recommended template to use this logo for.
	*/
	public $FileType = 'png';

	public function __construct()
	{
		parent::__construct();
		$this->Text[0] = 'Gift Co.';
	}

	public function GenerateLogo()
	{
		$this->NewLogo($this->FileType); // defaults to png. can use jpg or gif as well

		$this->FontPath = dirname(__FILE__) . '/fonts/';

		$this->ImagePath = dirname(__FILE__) . '/';
		$backgroundImage = 'back.png';
		$this->SetBackgroundImage($backgroundImage, LOGOMAKER_REPEAT_X);
		$size = getimagesize(dirname(__FILE__)."/".$backgroundImage);
		$imageHeight = $size['1'];

		$textLeft = 20;
		$textSize = 41;

		// we need the height of the text box to position the image and then caculate the text position
		$textBox = $this->TextBox($this->Text[0], 'SCRIPTIN.ttf', 'ffffff', $textSize, 0, 0);

		// determine the y position for the text
		$yPos = 8+(($imageHeight - $textBox['height'])/2);

		$textPosition = $this->AddText($this->Text[0], 'SCRIPTIN.ttf', 'ffffff', $textSize, $textLeft, $yPos);
		$topRight = $textPosition['top_right_x'];

		$this->SetImageSize($topRight+20, $imageHeight);
		$this->CropImage = true;
		return $this->MakeLogo();
	}
}