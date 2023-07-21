<?php

namespace ArsenalEnchants;

use ArsenalEnchants\Enchants\DeathBringer;
use ArsenalEnchants\Enchants\ELooter;
use ArsenalEnchants\Enchants\Looter as EnchantsLooter;
use ArsenalEnchants\Enchants\Nuke;
use ArsenalEnchants\Enchants\Prospector;
use ArsenalEnchants\Enchants\Vampiric;
use ArsenalEnchants\Task\Looter;
use ArsenalEnchants\Task\Diamond;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {



    # PROPERTY OF POCKETPINAS

    # FROM: ZENQI, USE THE ENCHANTMENT WITH  $item->addEnchantment(new EnchantmentInstance($this->DeathBringer, 5));

    # PER LEVEL IS MULTIPLIED WITH 2.5 YOU CAN MODIFY IT IN DEATHBRINGER.PHP


    private $cooldowns = [];

    private $playerQueue = array();
    public DeathBringer $DeathBringer;
    public Nuke $Nuke;
    public Vampiric $Vampiric;
    public $db = null;
    public ELooter $Looter;
    public $checker = [];
    public Prospector $Prospector;
    public $db1 = [];
    public TaskHandler $task;
    public static $main;
    public TaskHandler $tsk;
    public TaskHandler $dia;

    public array $ce;
    public function onEnable() : void {
        $this->getLogger()->info("ArsenalEnchants loaded successfully.");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
       
    }

   

    public function returnself()  : self {
        return self::$main;
    }
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {


        if ($sender instanceof Player) {
            if ($command->getName() === "enchant") {
                $player = $sender;
               
                $item = $player->getInventory()->getItemInHand();
                $item->addEnchantment(new EnchantmentInstance($this->Prospector, 1));

                $player->getInventory()->setItemInHand($item);
            }
        }
            return true;
        }
   
    public function onLoad() : void {
        self::$main = $this;
        $this->DeathBringer = new DeathBringer();
        $this->Nuke = new Nuke();
        $this->Vampiric = new Vampiric;
        $this->Looter = new ELooter();
        $this->Prospector = new Prospector();

        $this->ce = [$this->DeathBringer, $this->Nuke, $this->Vampiric, $this->Looter, $this->Prospector];
        EnchantmentIdMap::getInstance()->register($this->Prospector->getMcpeId(), $this->Prospector);
        EnchantmentIdMap::getInstance()->register($this->DeathBringer->getMcpeId(), $this->DeathBringer);
        EnchantmentIdMap::getInstance()->register($this->Nuke->getMcpeId(), $this->Nuke);
        EnchantmentIdMap::getInstance()->register($this->Vampiric->getMcpeId(), $this->Vampiric);
        EnchantmentIdMap::getInstance()->register($this->Looter->getMcpeId(), $this->Looter);
    }


   public function toRomanNumerals($number) {
        $romanNumerals = array(
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V'
        );
    
        if ($number < 1 || $number > 5) {
            return false;
        }
    
        return $romanNumerals[$number];
    }

    public function displayEnchants(ItemStack $itemStack): ItemStack {
        $item = TypeConverter::getInstance()->netItemStackToCore($itemStack);
        $lore = [];
        if (count($item->getEnchantments()) > 0) {
            foreach ($item->getEnchantments() as $enchantmentInstance) {
                $enchantment = $enchantmentInstance->getType();
                  if ($enchantment instanceof $this->DeathBringer || $enchantment instanceof $this->Looter || $enchantment instanceof $this->Nuke || $enchantment instanceof $this->Prospector || $enchantment instanceof $this->Vampiric) {
                    $info = TextFormat::RESET . TextFormat::LIGHT_PURPLE . $enchantment->getName() . " " . $this->toRomanNumerals($enchantmentInstance->getLevel());
                    $lore[] = $info;
                  }
            }
        }
        if ($item->getNamedTag()->getTag(Item::TAG_DISPLAY)) $item->getNamedTag()->setTag("OriginalDisplayTag", $item->getNamedTag()->getTag(Item::TAG_DISPLAY)->safeClone());
        $item->setLore($lore);
        
        return TypeConverter::getInstance()->coreItemStackToNet($item);
    }
    

    
    private function isOnCooldown(Player $player) : bool {
        $name = $player->getName();
        if (isset($this->cooldowns[$name]) && $this->cooldowns[$name] > time()) {
            $remainingTime = $this->cooldowns[$name] - time();
            $player->sendMessage("on Cooldown.");
            return true;
        }
        return false;
    }

    private function applyCooldown(Player $player, int $seconds) {
        $name = $player->getName();
        $this->cooldowns[$name] = time() + $seconds;
    }

    public function onDamage(EntityDamageByEntityEvent $event) : void {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        

        
        if ($damager instanceof Player) {
            $item = $damager->getInventory()->getItemInHand();
        
            # SECTION OF DEATHBRINGER
            if ($item->hasEnchantment($this->DeathBringer)) { // Use $this->DeathBringer instead of $this->DeathBringer->getMcpeId()
                $event->setBaseDamage($this->DeathBringer->getDamageIncrease($event->getBaseDamage(), $item->getEnchantmentLevel($this->DeathBringer)));
            }
        
            # SECTION OF VAMPIRIC
            if ($item->hasEnchantment($this->Vampiric)) { // Use $this->Vampiric instead of $this->Vampiric->getMcpeId()
                if ($this->isOnCooldown($damager)) {
                    return;
                }
                $this->Vampiric->steal($event->getBaseDamage(), $damager, $item->getEnchantmentLevel($this->Vampiric), $player); // Pass $this->Vampiric instead of $this->DeathBringer->getMcpeId()
        
                $this->applyCooldown($damager, 2);
        
                $absorbed = $event->getFinalDamage() * $item->getEnchantmentLevel($this->Vampiric); // Use $this->Vampiric instead of $this->DeathBringer->getMcpeId()
        
                $damager->sendMessage("Dealt " . $event->getBaseDamage() . " to Player, player health is now " . $player->getHealth() . "\nAbsorbed " . $event->getFinalDamage() . ", healed about " . $absorbed);
            }
        }
    }

   

    public function onPacket(DataPacketSendEvent $event ) : void {
        $packets  = $event->getPackets();
        foreach($packets as $packet) {
            if ($packet instanceof InventorySlotPacket) {
                $packet->item = new ItemStackWrapper($packet->item->getStackId(), $this->displayEnchants($packet->item->getItemStack()));
                
            }

            if ($packet instanceof InventoryContentPacket) {
                foreach ($packet->items as $i => $item) {
                    $packet->items[$i] = new ItemStackWrapper($item->getStackId(), $this->displayEnchants($item->getItemStack()));
                }
            }
        }
    }


    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $block = $event->getBlock();
    
        if ($item->hasEnchantment($this->Nuke)) {
            $enchant = $item->getEnchantment($this->Nuke);
            $level = $enchant->getLevel();
            $this->Nuke->breakSurroundingBlocks($level, $block, $player);
        }
    }
    
    public function isChestplate(Item $item): bool {
        $chestplateIds = [
            ItemFactory::getInstance()->get(ItemIds::CHAINMAIL_CHESTPLATE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::IRON_CHESTPLATE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::GOLDEN_CHESTPLATE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::DIAMOND_CHESTPLATE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::LEATHER_TUNIC)->getId()
        ];
        return in_array($item->getId(), $chestplateIds);
    }

    public function isPickaxe(Item $item): bool {
        $chestplateIds = [
            ItemFactory::getInstance()->get(ItemIds::IRON_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::GOLD_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::DIAMOND_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::WOODEN_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::STONE_PICKAXE)->getId()
        ];
        return in_array($item->getId(), $chestplateIds);
    }
    
    public function onLeave(PlayerQuitEvent $event) {
        $player = $event->getPlayer();

        if (in_array($player->getName(), $this->checker, true)) {
            $this->task->cancel();

        }
    }


    public function onDrop(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();
    
        $enc = $player->getArmorInventory()->getChestplate()->hasEnchantment($this->Looter);
    
        if ($enc) {
            if ($this->db === null) {
                $this->db = 5;
                if ($this->task !== null) {
                    $this->task->cancel();
                }
                $player->sendMessage("Looter will be on cooldown.");
    
                $this->getScheduler()->scheduleDelayedTask(new class($this, $player) extends Task {
                    private $plugin;
                    private $player;
    
                    public function __construct(Main $plugin, Player $player) {
                        $this->plugin = $plugin;
                        $this->player = $player;
                    }
    
                    public function onRun() : void {
                        $this->player->sendMessage("Looter is finished");
                        $this->plugin->task = $this->plugin->getScheduler()->scheduleRepeatingTask(new Looter($this->player), 20);
                        $this->plugin->db = null;
                    }
                }, 5 * 20); // Delay of 5 seconds (20 ticks per second)
            }
        }
    }
    

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        # SECTION OF LOOTER
        if ($player->getArmorInventory()->getChestplate()->hasEnchantment($this->Looter)) {
            $this->task = $this->getScheduler()->scheduleRepeatingTask(new Looter($player), 20);
            $this->checker[] = $player->getName();
        }

        # SECTION OF PROSPECTOR
       foreach ($event->getPlayer()->getInventory()->getContents() as $item) {
        if ($item->hasEnchantment($this->Prospector) && $this->isPickaxe($item)) {
            $this->task = $this->getScheduler()->scheduleRepeatingTask(new DIamond($player, $this), 20);
            $this->checker[] = $player->getName();
        }
       }
    }

    public function onPickaxe(InventoryTransactionEvent $event) {
        $player = $event->getTransaction()->getSource();
        $inventory = $player->getInventory();

        foreach($inventory->getContents() as $items) {
            if ($items->hasEnchantment($this->Prospector) && $this->isPickaxe($items) && !in_array($player->getName(), $this->db1, true)) {
                $this->dia = $this->getScheduler()->scheduleRepeatingTask(new Diamond($player, $this), 20);
                $this->db1[] = $player->getName();
            }
        }
    }
    public function onArmor(InventoryTransactionEvent $event) {
         $type = $event->getTransaction()->getInventories();
         foreach($type as $inventories)
            if ($inventories instanceof ArmorInventory) {
                $armor = $event->getTransaction()->getSource()->getArmorInventory()->getChestplate();
                if ($armor->hasEnchantment($this->Looter)) {
                    if (!in_array($event->getTransaction()->getSource()->getName(), $this->checker, true)) {
                        $this->task = $this->getScheduler()->scheduleRepeatingTask(new Looter($event->getTransaction()->getSource()), 20);
                        $this->checker[] =  $event->getTransaction()->getSource()->getName();
                    }
                }
            }
    }





}
