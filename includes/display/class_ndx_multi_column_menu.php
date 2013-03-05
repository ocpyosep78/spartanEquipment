<?php
/**
 * Version 1.1.0
 * this version works
 * limitations: it will try to use all the columns specified in max_columns
 * and it will balance those rows
 * note: problem:
 * 8 8 8 8 8 8 8 8 with 4 and 24 does not balance properly because column 4 is affected by column 1, not an adjacent column
*/

class NDX_MULTI_COLUMN_MENU 
{
	// input
	
	var $category_totals = array (); // $category_totals[colx][colcatx] 
	var $max_columns = 0;
	var $max_rows_per_column = 0;
	
	// control
	var $ncolumns = 0;
	var $colx = -1;
	var $colcatx = -1;
	var $remain = array ();
	var $used = 0;
	var $ncategories = 0;
	
	// output
	var $column_totals = array();
	var $column_totals_string = "";


function __construct($max_columns, $max_rows_per_column) {
	$this->max_columns = $max_columns;
	$this->max_rows_per_column = $max_rows_per_column;
}	

/**
*	Main Function
* 	$category_totals is an array of the number of elements in a category including the category itself
*	@param $categorory_totals = array (5,3,4,2);
*/

public function create_multi_column_menu ($category_totals)
{
	$this->fit_into_columns ($category_totals);
	$this->balance_columns();
	
	return($this->column_totals);

//	$this->column_totals_string = $this->category_total_to_string ();
//	return ($this->column_totals_string);
}

public function category_total_to_string ()
{
	$s = "";
	foreach ($this->column_totals as $ct)
	{
		$s .= "| ";
		foreach ($ct as $cat)
		{
			$s .= "$cat ";
		}
	}
	$s .= "|";
	return ($s);
}

// in case we reuse the same variable
// alternately, unset and new
public function init_multi_column_menu ()
{
	$this->category_totals = array ();	
	$this->ncolumns = 0;
	$this->colx = -1;
	$this->colcatx = -1;
	$this->remain = array ();
	$this->used = 0;
	$this->ncategories = 0;
	
	// output
	$this->column_totals = array();
	$this->column_totals_string = "";	
}
/* -------------------  */

protected function fit_into_columns ($category_totals)
{
	$this->category_totals = $category_totals;
	
	$this->init_column();
	foreach ($category_totals as $c)
	{
		$this->ncategories++;
		// if it fits move into the column
		// subtract from remaining available
		if ($c <= $this->remain[$this->colx])
		{
			$this->add_to_column($c);
		}
		
		// doesn't fit  
		else
		{
			// if something in this column add  a column and add to column
			if ($this->used > 0)
			{
				$this->init_column();
			}
			$this->add_to_column($c);
		}
	}
}

/**
*	keep balancing until there are no more changes
*/
protected function balance_columns()
{
	$mod = true;
	while ($mod)
	{
		$mod = $this->balance_column_loop();
	}
}

/**
*	balance the columns if possible
*	start with the second column and compare to the previous column
*/
protected function balance_column_loop()
{
	// copy to local for ease and speed

//	$limit = $this->colx;
//	$x = 0; // current col
//	$p = 0; // previous col
	$mod = false;

//	foreach ($this->column_totals as $ct)
	// going backwards through columns
        $x1 = min($this->ncategories-1,$this->max_columns-1);
	for ($x=max($x1, $this->colx); $x>0; $x--)
	{
		$newcol = false;
		$p = $x - 1; // set p to prior column
		if (!isset($this->column_totals[$p])) // the column doesn't exist
		{
			$this->init_column($p);
		}		
		if (!isset($this->column_totals[$x])) // the column doesn't exist
		{
			$this->init_column($x);
			$newcol = true;
		}

		// compare remainder and keep the value we'd like to improve
		if ($this->remain[$x] > $this->remain[$p])
		{
			$r1 = $this->remain[$p]; 		// must be the min
			$last_value = $this->last_cat($p);	// last value in prev col
			$r2 = min($this->remain[$p] + $last_value, $this->remain[$x] - $last_value);
			if ($r2 > $r1 || $newcol)
			{
				$this->move_last ($p, $x);
				$mod = true;
			}
		}
	}
	return($mod);

}


/**
*	iniitialize a new column
*	generally an index will not be passed and we add the next column
*	during balancing we may add indexes from the right and we'll pass the index value
*/
protected function init_column($index=0)
{
	$this->ncolumns++;
	$x = $index > 0 ? $index: ++$this->colx;
	$this->remain[$x] = $this->max_rows_per_column;
	$this->used = 0;
	$this->colcatx = -1;
//	$this->column_totals_string .= "| ";
}

protected function add_to_column($c)
{
	$this->colcatx++;
	$this->remain[$this->colx] -= $c;			
	$this->used += $c;
	$this->column_totals[$this->colx][$this->colcatx] = $c;
//	$this->column_totals_string .= "$c ";
}

// returns the last value of the given column
protected function last_cat ($colx)
{
	$lastx = count($this->column_totals[$colx])-1;
	return ($this->column_totals[$colx][$lastx]);
}

protected function move_last ($pcolx, $colx)
{
	// get and unset last value from prev column
	$last_pcatx = count($this->column_totals[$pcolx])-1;
	$last_value = $this->column_totals[$pcolx][$last_pcatx];
	$this->remain[$pcolx] += $last_value;
	unset($this->column_totals[$pcolx][$last_pcatx]);
	
	// insert as first in current column
	// it may be an empty column
	if (isset($this->column_totals[$colx]))
	{
		array_unshift ($this->column_totals[$colx], $last_value);
	}
	// and it is empty, then
	else
	{
		$this->column_totals[$colx][0] = $last_value;
	}
	$this->remain[$colx] -= $last_value;
}

 
} // end class NDX_MULTI_COLUMN_MENU 


?>