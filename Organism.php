<?php

//just a little helper function to help me control the script's output outside of code
function print_v($message){
	if($_GET['verbose'] == 1) print $message;
}

class Species {

	public $name;
	public $organisms = array();
	public $cemetary = array();
	public $year = 0;
	public $ticksperyear = 1;
	public $maxorganisms = 100;
	public $startingorganisms = 10;
	
	public function __construct($name){
		$this->name = $name;
		//create first parents
		for($i = 0; $i < $this->startingorganisms; $i++){
			$this->createOrganism();
		}
	}

	public function getOldest(){
		$maxage = 0;
		foreach($this->organisms as $organism){
			if($organism->getAge($this) > $maxage){
				$maxage = $organism->getAge($this);
				$oldest = $organism;
			}
		}
		return $oldest;
	}

	public function getLatestGeneration(){
		$maxgen = 0;
		foreach($this->organisms as $organism){
			if($organism->generation > $maxgen){
				$maxgen = $organism->generation;
			}
		}
		return $maxgen;
	}

	public function getPairs(){
		//let's get some random pairs for the purpose of mating and other interactions
		shuffle($this->organisms);
		$pairs = array();
		$i = 0;		
		while(count($pairs) < count($this->organisms)/2){
			$pairs[] = array($this->organisms[$i], $this->organisms[$i+1]);
			$i++;	
		}
		return $pairs;
	}

	public function simulate(){
		$pairs = $this->getPairs();
		foreach($pairs as $pair){
			//for now let's keep the pairs but assume there's some kind of compatibility feature, as an example
			if($pair[0]->canMate() && $pair[0]->willMateWith($pair[1]) && $pair[1]->canMate() && $pair[1]->willMateWith($pair[0])){
  				//WOW MATING IS HARD
				$neworganism = Organism::spawn($pair[0], $pair[1], $this);
				$neworganism->birthday = $this->year;
				$this->organisms[] = $neworganism;
			}
		}		
		$this->cull();
		$this->year++;
	}

	public function getHighestGeneEffect($name){
		$maxeffect = 0;		
		foreach($this->organisms as $organism){
			if($organism->getGene($name)->effect > $maxeffect){
				$maxeffect = $organism->getGene($name)->effect; 
			}
		}
		return $maxeffect;
	}
	
	public function getHighestStat($name){
		$maxeffect = 0;		
		foreach($this->organisms as $organism){
			if($organism->getStat($name) > $maxeffect){
				$maxeffect = $organism->getStat($name); 
			}
		}
		return $maxeffect;
	}
	

	public function cull(){
		//put dead organisms in the cemetary
		foreach($this->organisms as $key => $organism){
			//check if should die of old age
			if($organism->getGene('longetivity')->effect < $organism->getAge($this) && mt_rand(0, 15) > 11) $organism->kill("Old Age"); 
			if(!$organism->alive){
				$this->cemetary[] = $this->organisms[$key];
				unset($this->organisms[$key]);
			}
		}
	}
	
	public function createOrganism(){
		$this->organisms[] = new Organism($this);
	}
}

class Organism {
	
	public $alive = true;
	public $birthday = 0;
	public $status = "";
	public $hasmated = 0;
	public $generation = 0;
	public $coords;
	public $genes = array();
	public $stats = array();
	public $parent1;
	public $parent2;
	public $species;


	public function __construct(&$species){
		$this->genes = array(
		"height" => new Gene("height", "height", 5),
		"weight" => new Gene("weight", "weight", 150),
		"longetivity" => new Gene("longetivity", "longetivity", 100), 
		"color" => new Gene("color", "color", 3),
		"power" => new Gene("power", "power", 1),
		"speed" => new Gene("speed", "speed", 5),
		"aggression" => new Gene("aggression", "aggression", 3),
		"fertility" => new Gene("fertility", "fertility", 90), //fertility is our first important gene. The rest have no effect right now.
		"gene_integrity" => new Gene("gene_integrity", "gene_integrity", 50),
		);
		$this->species = $species;
	}

	public function getAge(&$species){
		return $species->year - $this->birthday;
	}

	static function createRandom( $coords){
	//different from spawn, creates a random
	}

	static function spawn($parent1,  $parent2, &$species){
		$organism = new Organism($species);
		$organism->genes = Organism::splice($parent1, $parent2);
		//let's make this more realistic (ha!)
		if(mt_rand(0, 100) > $parent1->getGene("fertility")->effect) $organism->kill("stillborn");
		if(mt_rand(0, 100) > $parent2->getGene("fertility")->effect) $organism->kill("stillborn");

		//update generation
		$organism->generation = max($parent1->generation, $parent2->generation) + 1;
		//$organism->parent1 = $parent1;
		//$organism->parent2 = $parent2;
		return $organism;
	}	

	public function getNearestOpenCoords(){
		//for now return this coords + 1
		$coords = new Coords();
		$coords->x = $this->coords->x + 1;
		$coords->y = $this->coords->y + 1;
	}

	public function kill($reason){
		$this->status = $reason;
		$this->alive = false;
		//print_v( "\n Organism was killed! $reason");
	}

	public function canMate(){
		//is of proper mating age
		return $this->getAge($this->species) > 12 && $this->getAge($this->species) < 50;
	}

	public function willMateWith($partner){
		//Appreciates and loves their partner enough to form a child

		//Let's leave this to age compatibility for now...

		if(abs($partner->getAge($this->species) - $this->getAge($this->species)) < 15){
			return true;
		} else {
			//uh oh a creepy old person! Add anger to the victim!
			if($partner->getAge($this->species) > $this->getAge($this->species)){ 
				$this->addToStat("anger", 1);
				if($this->getStat("anger") > 10) { //TODO factor in Anger genetics
					$partner->kill("Murdered for being a creep");
				}
			}
			if($partner->getAge($this->species) < $this->getAge($this->species)){
				 $partner->addToStat("anger", 1);
				 if($partner->getStat("anger") > 10){
					$this->kill("Murdered for being a creep"); //Holy crap! A fight resulting in death.
				 }
			}
			return false;
		}
	}

	public function getGene($name){
		return $this->genes[$name];		
	}
	public function getStat($name){
		return @$this->stats[$name];		
	}	
	public function setStat($name, $value){
		$this->stats[$name] = $value; //for now let's use key->value pairs and then see if we really need those Stat objects...	
	}
	public function addToStat($name, $value){
		$this->setStat($name, $this->getStat($name) + $value);
	}


	public static function splice(Organism $organism1, Organism $organism2){
		$newGenes = array();
		foreach($organism1->genes as $name => $gene){
			if($gene2 = $organism2->getGene($name)){
				$newGenes[$name] = Gene::splice($gene, $gene2);
			}
		}
		return $newGenes;
		
	}



}

class Coords {

	public $x;
	public $y;

}

class Gene { 
	public $name;
	public $stat;
	public $effect;
	public $integrity = 90;
	
	public function __construct($name, $stat, $effect, $integrity = 50){
		$this->name = $name;
		$this->stat = $stat;
		$this->effect = $effect;
		$this->integrity = $integrity;
	}

	public static function splice(Gene $gene1, Gene $gene2){
		if($gene1->name != $gene2->name) throw new Exception("Splicing unlike genes");
		$newGene = new Gene($gene1->name, $gene1->stat, ($gene1->effect + $gene2->effect) / 2);
		$newGene->mutate();
		return $newGene;
	}
	
	public function mutate(){
		if($this->integrity < 100){
			$delta = (mt_rand($this->integrity - 100,100- $this->integrity) ) / 100;
			$this->effect = $this->effect + ($this->effect * $delta);

		}
	}

}


/* //We might use this later... for now let's not and see what happens
class Stat {
	//stat for an organism, usually affected by a gene but can change after other interactions in the simulation.
	// For instance, if it gets injured, if it has a child etc
	public $name;
	public $value;
	public $type; //type is probably variable type, quantitative, qualitative, etc... may affect mutating operations in the future?
}
*/
//These classes should probably be populated from an external file format

class Behavior {

	

	public $name; //name of the behavior
	public $activities = array(); //activities in this behavior. Ex: Mate Seeking

}

class Activity {
		
	//an activity that can have success/failure probability and affect outcomes
	public $name;
	public $callback; //callback function to find out about success or failure!

}
