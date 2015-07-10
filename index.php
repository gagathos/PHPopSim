<pre>
<?php

/* Variables */

$years = 20; //how many years do you want the simulation to run for?


require_once("Organism.php");

try{
	$humans = new Species("Human");

	for($i = 0; $i < $years; $i++){
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

function print_organism($org){
//for now let's do this cheaply
//TODO build views
print "<pre>";
print_r($org);
print "</pre>";
}

print_stat("Final Year", $humans->year);
print_stat("Count Alive", count($humans->organisms));
print_stat("Count Dead", count($humans->cemetary));
print_stat("Oldest Age", $humans->getOldest()->getAge($humans)); //This should be the same as years until we implement other ways for organisms to die
print_stat("Fertilest Fertility", $humans->getHighestGeneEffect('fertility'));
print_stat("Max Generation", $humans->getLatestGeneration());

//print_organism($humans->getOldest());

?>
