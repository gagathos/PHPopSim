<?php


class Species {

	public $name;
	public $organisms = array();
	public $cemetary = array();
	public $year = 0;
	public $ticksperyear = 1;
	public $maxorganisms = 100;
	
	public function __construct($name){
		$this->name = $name;
		//create first parents
		$this->createOrganism();
		$this->createOrganism();
	}

	public function getOldest(){
		$maxage = 0;
		foreach($this->organisms as $organism){
			if($organism->getAge($this) > $maxage){
				$maxage = $organism->getAge($this);
				$oldest = $organism;
			}
		}
		return $organism;
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

	public function simulate(){
		//for now let's do random pairs
		$organism1 = $this->organisms[mt_rand(0, count($this->organisms) - 1)];
		$organism2 = $this->organisms[mt_rand(0, count($this->organisms) - 1)];
		//make sure organism is not mating with itself (awkward!)
		while($organism1 === $organism2){
			$organism2 = @$this->organisms[mt_rand(0, count($this->organisms) - 1)]; //TODO: Do this better
		}
		if($organism1 && $organism2){
			$neworganism = Organism::spawn($organism1, $organism2);
			$neworganism->birthday = $this->year;
			$this->organisms[] = $neworganism;
		}		
		$this->cull();
		$this->year++;
	}

	public function cull(){
		//put dead organisms in the cemetary
		foreach($this->organisms as $key => $organism){
			if(!$organism->alive){
				$this->cemetary[] = $this->organisms[$key];
				unset($this->organisms[$key]);
			}
		}
	}
	
	public function createOrganism(){
		$this->organisms[] = new Organism();
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
	public $parent1;
	public $parent2;


	public function __construct(){
		$this->genes = array(
		"height" => new Gene("height", "height", 5),
		"weight" => new Gene("weight", "weight", 150),
		"longetivity" => new Gene("longetivity", "longetivity", 100), 
		"color" => new Gene("color", "color", 3),
		"power" => new Gene("power", "power", 1),
		"speed" => new Gene("speed", "speed", 5),
		"agression" => new Gene("aggression", "aggression", 3),
		"fertility" => new Gene("fertility", "fertility", 90), //fertility is our first important gene. The rest have no effect right now.
		"gene_integrity" => new Gene("gene_integrity", "gene_integrity", 50),
		);
	}

	public function getAge($species){
		return $species->year - $this->birthday;
	}

	static function createRandom( $coords){
	//different from spawn, creates a random
	}

	static function spawn($parent1,  $parent2){
		$organism = new Organism;
		$organism->genes = Organism::splice($parent1, $parent2);
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
	}

	public function getGene($name){
		return $this->genes[$name];		
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
	public $integrity = 50;
	
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

