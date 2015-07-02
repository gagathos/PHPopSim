# PHPopSim 
PHP OOP Population simulator.  This is an abstract concept that might later be developed into a game or something else.

The idea is to test deterministic vs non-deterministic genetic mutation in populations.  In our simplest case, more fertile individuals should give birth to more fertile offspring and after many generations there should be lots of fertility. But there's a lot of tuning to do to make this "realistic."

Eventually, there will be an AI layer that abstracts genes into behavior, where all genes are not necessarily deterministic but could lead to emergent behaviors that affect the outcome of the simulation. For instance, an organism that is attracted to resource-rich areas would probably do better than an organism that wanders without caring; other predispositions could lead to worse genetic diversity (inbreeding), overpopulation/population crashes and other more advanced population control mechanisms.

The purpose of simulating these mechanisms is to determine whether player-driven interventions could exist to gameify the simulation, and what those interventions could be.
