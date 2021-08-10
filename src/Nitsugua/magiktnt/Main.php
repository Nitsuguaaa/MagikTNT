<?php

declare(strict_types=1);

namespace Nitsugua\magiktnt;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\Entity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\Listener;
use Nitsugua\magiktnt\task\explode;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

    /** @var Config $settings */
    private $settings;
    /** @var Config $db */
    private $db;

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->settings = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "Henlo welcome to confusing configs with Nitsu" => "[NOTE]Obsidian durability was made possible by Demonslayer3861. Go check out his/her standalone Obsidian Breaker at https://github.com/Demonslayer3861/Obsidian-breaker-TNT Demonslayer3861’s license on Obsidian health and Obsidian health checker: https://github.com/Demonslayer3861/Obsidian-breaker-TNT/blob/main/License[NOTE]",
            "[DURABILITY CHECKER]" => "[CONFIG]",
            "[NOTE] ITEM TO CHECK HOW MUCH DURABILITY AN OBSIDIAN HAS, DAMAGE IS OPTIONAL" => "[NOTE]",
            "obsidian-durability-checker" => [
                "id" => Item::POTATO,
                "damage" => 0
            ],
            "[OBSIDIAN HEALTH]" => "[CONFIG]",
            "[NOTE] SETS ON HOW MUCH TNT IS NEEDED FOR AN OBSIDIAN TO BREAK, 1 OBSIDIAN HEALTH = 1 TNT EXPLODE" => "[NOTE]",
            "obsidian-health" => 7,
            "[IGNORE WATER]" => "[CONFIG]",
            "[NOTE]SET TO TRUE IF YOU WANT AN OBSIDIAN TO BREAK EVEN IF THE TNT IS UNDERWATER" => "[NOTE]",
            "ignore-water" => true,
            "[TNT CRAFTING]" => "[CONFIG]",
            "[NOTE]SET TO TRUE IS YOU WANT TO DISABLE TNT CRAFTING" => "[NOTE]",
            "[NOTE]E.G. IF YOU THINK TNT IS OP THEN YOU CAN SET THIS TO FALSE AND OBTAIN TNT VIA /GIVE OR SOMETHING THAT YOU CAN THINK OFF" =>"[NOTE]",
            "tnt-crafting" => true

        ]);

        $this->db = new Config($this->getDataFolder() . "db.json", Config::JSON);

        $this->initObsidianBlock();
    }

    private function initObsidianBlock(): void{
        $class = new MyObsidian($this);
        BlockFactory::registerBlock($class, true);
    }

    public function onDisable(){
        $this->config()->save();
        $this->getDB()->save();
    }

    public function onBreak(BlockBreakEvent $event){

        $player = $event->getPlayer();
        $levels = $this->getServer()->getLevels();

        if($event->getBlock()->getId() === Block::TNT) {
            foreach ($levels as $level) {
                $tnt = new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z);
                $level->setBlock($tnt, Block::get(0));
            }
            $event->setDrops([]);
            /* @var PrimedTNT $entity */
            $entity = Entity::createEntity("PrimedTNT", $player->getLevel(), Entity::createBaseNBT($player));
            $entity->setMotion($player->getDirectionVector()->normalize()->multiply(2));
            $entity->spawnToAll();
            if ($this->settings->get("ignore-water") === true) {
                $this->getScheduler()->scheduleDelayedTask(new explode($this, $player, $entity), 77);
            }
        }
    }

    public function onExplodeEntity(EntityExplodeEvent $ev) : void{
        $entity = $ev->getEntity();

        if(!$entity instanceof PrimedTNT) return;

        $bList = $ev->getBlockList();

        $db = $this->getDB()->getAll();

        foreach($bList as $i => $block){
            if($block instanceof MyObsidian){
                $index = ($block->getX() . ":" . $block->getY() . ":" . $block->getZ() . ":" . $block->getLevel()->getName());
                if(!isset($db["obsidians"][$index])){
                    $db["obsidians"][$index] = 1;
                }else{
                    $db["obsidians"][$index] += 1;
                }

                if($db["obsidians"][$index] >= $this->getConfig()->get("obsidian-health")){
                    unset($db["obsidians"][$index]);
                }else{
                    unset($bList[$i]);
                }
            }
        }
        $ev->setBlockList($bList);

        $this->getDB()->setAll($db);
    }

    /**
     * @return Config
     */
    public function getDB(): Config{
        return $this->db;
    }

    public function config() {
        return $this->settings;
    }

    public function onCraft(CraftItemEvent $event) {

        if ($this->settings->get("tnt-crafting") === true) {
            $results = $event->getRecipe()->getResults();

            foreach ($results as $result) {
                if ($result->getId() === Item::TNT) {
                    $event->setCancelled();
                }
            }
        }
    }
}
