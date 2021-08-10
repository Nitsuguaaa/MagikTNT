<?php


namespace Nitsugua\magiktnt;

use pocketmine\{
    block\Obsidian, item\Item, Player, utils\TextFormat
};

use Nitsugua\magiktnt\Main;

class MyObsidian extends Obsidian{

    /** @var int $id */
    protected $id = self::OBSIDIAN;

    /** @var Main $plugin */
    private $plugin;
    /** @var string[] $interact */
    private $interact = [];

    public function __construct(Main $plugin, int $meta = 0){
        $this->plugin = $plugin;
        parent::__construct($meta);
    }

    public function onActivate(Item $item, Player $player = null): bool{
        if($player === null) return false;

        if(!isset($this->interact[$player->getName()])){
            $this->interact[$player->getName()] = 1;
        }

        if(round($this->interact[$player->getName()], 1) + 1 <= round(microtime(true), 1)){
            $this->interact[$player->getName()] = microtime(true);

            $checkerValue = $this->plugin->config()->get("obsidian-durability-checker");
            $checker = Item::get($checkerValue["id"], $checkerValue["damage"] ?? 0);
            if($item->equals($checker, true, false)){
                $db = $this->plugin->getDB()->getAll();
                if(isset($db["obsidians"][$index = ($this->getX() . ":" . $this->getY() . ":" . $this->getZ() . ":" . $this->getLevel()->getName())])){
                    $player->sendMessage("This Obsidian has " . TextFormat::LIGHT_PURPLE . ($this->plugin->config()->get("obsidian-health") - $db["obsidians"][$index]) . "/" . $this->plugin->config()->get("obsidian-health") . TextFormat::RESET . " durability left");
                }else{
                    $player->sendMessage("This Obsidian has " . TextFormat::LIGHT_PURPLE . $this->plugin->config()->get("obsidian-health") . "/" . $this->plugin->config()->get("obsidian-health") . TextFormat::RESET . " durability left");
                }
            }
        }
        return false;
    }

    public function getBlastResistance(): float{
        return 10;
    }
}