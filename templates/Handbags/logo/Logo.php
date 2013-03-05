<?php

class Store_Logo_Handbags extends Store_Logo
{
	protected $_textFieldCount = 3;

	protected $_exampleStrings = array(
		'The',
		'Handbag',
		'Boutique',
	);

	protected function _configureLogo(Store_Logo_Maker $maker)
	{
		$image = $maker->addImage(dirname(__FILE__) . '/left.png', 0);
		$imageWidth = $image['width'];
		$right = $imageWidth;

		$text1X = 75;
		$text1Y = 15;
		$text2X = 50;
		$text2Y = 15;
		$text3X = 145;
		$text3Y = 15;

		if ($this->getTextField(0)) {
			$text = new Store_Logo_Text();
			$text
				->setColor('#FFFFFF')
				->setFont($this->getFont('Bauhaus.otf'))
				->setFontSize(20);

			$dimensions = $maker->getTextDimensions($this->getTextField(0), $text, $text1X, $text1Y);
			$text2Y = $text1Y + $dimensions['height'] - 7;
			$right = max($right, $dimensions['width']);
		}

		if ($this->getTextField(1)) {
			$text2 = new Store_Logo_Text();
			$text2
				->setColor('#200e00')
				->setFont($this->getFont('Bauhaus.otf'))
				->setFontSize(46);

			$dimensions = $maker->getTextDimensions($this->getTextField(1), $text2, $text2X, $text2Y);
			$text3Y = $text2Y + $dimensions['height'] - 3;
			$right = max($right, $dimensions['width']);

		}

		if ($this->getTextField(2)) {
			$text3 = new Store_Logo_Text();
			$text3
				->setColor('#FFFFFF')
				->setFont($this->getFont('Bauhaus.otf'))
				->setFontSize(28);
			$dimensions = $maker->getTextDimensions($this->getTextField(2), $text3, $text3X, $text3Y);
			$right = max($right, $dimensions['width']);
		}

		$maker->addImage(dirname(__FILE__) . '/right.png', $right);

		if($right > $imageWidth) {
			// Work out what we need to repeat
			$repeatCount = ceil(($right-$imageWidth)/3);
			$where = $imageWidth;
			for($i = 0; $i < $repeatCount; ++$i) {
				$maker->addImage(dirname(__FILE__) . '/fill.png', $where, -1);
				$where += 3;
			}
		}

		if ($this->getTextField(0)) {
			$maker->addText($this->getTextField(0), $text, $text1X, $text1Y);
		}

		if ($this->getTextField(1)) {
			$maker->addText($this->getTextField(1), $text2, $text2X, $text2Y);
		}

		if ($this->getTextField(2)) {
			$maker->addText($this->getTextField(2), $text3, $text3X, $text3Y);
		}

		$maker->setBackgroundTransparent(true);
		$maker->autoSize();
	}
}
