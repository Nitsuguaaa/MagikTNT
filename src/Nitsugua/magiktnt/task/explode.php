<?php

namespace Nitsugua\magiktnt\task;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Nitsugua\magiktnt\Main;

class explode extends Task
{
    public function __CONSTRUCT(Main $main, Player $player, $entity) {
        $this->main = $main;
        $this->entity = $entity;
        $this->player = $player;
    }
    public function onRun(int $currentTick)
    {
        $levels = $this->main->getServer()->getLevels();
        $entity = $this->entity;

        $pos = new Vector3($entity->getX(), $entity->getY() , $entity->getZ());
        foreach ($levels as $level) {
            $level->setBlock($pos, Block::get(0));
        }

    }

}