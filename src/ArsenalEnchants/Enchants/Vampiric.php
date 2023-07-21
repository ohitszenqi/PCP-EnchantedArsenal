<?php

namespace ArsenalEnchants\Enchants;

use pocketmine\block\Planks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentLevelTable;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentSlot;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;

class Vampiric extends Enchantment {

    const ENCHANT_ID = 76;

    
    public function __construct() {
        parent::__construct("Vampiric", Rarity::RARE, ItemFlags::SWORD, ItemFlags::AXE, 5);
    }
    
    public function getDisplayName() : string {
        return "Vampiric";
    }
    public function getIncompatibles() : array {
        return [EnchantmentIds::BANE_OF_ARTHROPODS, EnchantmentIds::SMITE, EnchantmentIds::FIRE_ASPECT, EnchantmentIds::LOOTING];
    }
    
    public function steal(int $damage, Player $player, int $level, Player $damaged) {
        // Calculate the amount of health to heal or steal based on the damage and enchantment level
        $heal = $damage * $level;
        
        // Check if the damage would result in healing or health steal
        if ($heal > 0) {
            // Healing: Increase the player's health by the calculated amount
            $maxHealth = $player->getMaxHealth();
            $currentHealth = $player->getHealth();
            $newHealth = min($maxHealth, $currentHealth + $heal);
            $player->setHealth($newHealth);
        } else {
            // Health Steal: Reduce the damaged player's health by the absolute value of the calculated amount
            $damagedPlayer = $damaged;
            $damagedPlayer->setHealth(max(0, $damagedPlayer->getHealth() + $heal));
            
            // Increase the player's health by the stolen amount (optional, if you want to give the health to the player)
            $maxHealth = $player->getMaxHealth();
            $currentHealth = $player->getHealth();
            $newHealth = min($maxHealth, $currentHealth - $heal);
            $player->setHealth($newHealth);
        }
    }
    public function getMcpeId(): int {
       return self::ENCHANT_ID;
    }

    public function getLevel(Item $item): int {
        $level = $item->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(self::ENCHANT_ID));
        return $level !== null ? $level : 0;
    }
}
