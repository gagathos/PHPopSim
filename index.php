<pre>
<?php

require_once("Organism.php");

try{
	$humans = new Species("Human");

	for($i = 0; $i < 600; $i++){
		$humans->simulate();
	}
} catch (Exception $e){
	var_dump($e);
}

function print_stat($title, $value){
print "<div>";
print "<b>".$title.":</b>";
print $value;
print "</div>";
}


print_stat("Count Alive", count($humans->organisms));
print_stat("Count Dead", count($humans->cemetary));
print_stat("Oldest Age", $humans->getOldest()->getAge($humans));
print_stat("Max Generation", $humans->getLatestGeneration());
?>
