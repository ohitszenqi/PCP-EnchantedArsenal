<?php

namespace ArsenalEnchants\Enchants;

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

class DeathBringer extends Enchantment {

    const ENCHANT_ID = 69;

    
    public function __construct() {
        parent::__construct("DeathBringer", Rarity::RARE, ItemFlags::SWORD, ItemFlags::AXE, 5);
    }
    
    public function getDisplayName() : string {
        return "Deathbringer";
    }
    public function getIncompatibles() : array {
        return [EnchantmentIds::BANE_OF_ARTHROPODS, EnchantmentIds::SMITE, EnchantmentIds::FIRE_ASPECT, EnchantmentIds::LOOTING];
    }
    
    public function getDamageIncrease(int $level, int $dmg) : float {
        return $dmg + (2.5 * $level);
    }

    public function getMcpeId(): int {
       return self::ENCHANT_ID;
    }

    public function getLevel(Item $item): int {
        $level = $item->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(self::ENCHANT_ID));
        return $level !== null ? $level : 0;
    }
}
