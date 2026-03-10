<?php
// BattleState.php - Classe para armazenar estado serializável
class BattleState {
    public $player1_name;
    public $player2_name;
    public $character1_type;
    public $character2_type;
    public $character1_name;
    public $character2_name;
    public $turn;
    public $log;
    public $char1_hp;
    public $char1_maxHp;
    public $char1_energy;
    public $char1_maxEnergy;
    public $char1_attack;
    public $char1_defense;
    public $char1_speed;
    public $char2_hp;
    public $char2_maxHp;
    public $char2_energy;
    public $char2_maxEnergy;
    public $char2_attack;
    public $char2_defense;
    public $char2_speed;
    public $actions;
    public $ready;
    
    public function __construct($battle) {
        $state = $battle->getState();
        
        $this->player1_name = $state['player1']['name'];
        $this->player2_name = $state['player2']['name'];
        $this->character1_type = $state['player1']['character']->getType();
        $this->character2_type = $state['player2']['character']->getType();
        $this->character1_name = $state['player1']['character']->getName();
        $this->character2_name = $state['player2']['character']->getName();
        $this->turn = $state['turn'];
        $this->log = $state['log'];
        
        $this->char1_hp = $state['player1']['character']->getCurrentHp();
        $this->char1_maxHp = $state['player1']['character']->getMaxHp();
        $this->char1_energy = $state['player1']['character']->getCurrentEnergy();
        $this->char1_maxEnergy = $state['player1']['character']->getMaxEnergy();
        $this->char1_attack = $state['player1']['character']->getAttack();
        $this->char1_defense = $state['player1']['character']->getDefense();
        $this->char1_speed = $state['player1']['character']->getSpeed();
        
        $this->char2_hp = $state['player2']['character']->getCurrentHp();
        $this->char2_maxHp = $state['player2']['character']->getMaxHp();
        $this->char2_energy = $state['player2']['character']->getCurrentEnergy();
        $this->char2_maxEnergy = $state['player2']['character']->getMaxEnergy();
        $this->char2_attack = $state['player2']['character']->getAttack();
        $this->char2_defense = $state['player2']['character']->getDefense();
        $this->char2_speed = $state['player2']['character']->getSpeed();
        
        $this->actions = $battle->getActions();
        $this->ready = $battle->getReady();
    }
}
?>