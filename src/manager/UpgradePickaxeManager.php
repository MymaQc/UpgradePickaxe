<?php

namespace upgradepickaxe\manager;

use onebone\economyapi\EconomyAPI;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use upgradepickaxe\librairie\SimpleForm;
use upgradepickaxe\UpgradePickaxe;
use upgradepickaxe\util\UpgradePickaxeIds;

final class UpgradePickaxeManager {

    use SingletonTrait;

    /**
     * @param Item $item
     * @return SimpleForm
     */
    public function sendUpgradeUi(Item $item): SimpleForm {
        $config = UpgradePickaxe::getInstance()->getConfig();
        $form = new SimpleForm(function (Player $player, $data) use ($item, $config) {
            if (!is_null($data)) {
                switch ($data) {
                    case 0:
                        $price = $this->getPriceToUpgrade($item);
                        if ($this->canUpgrade($player, $price)) {
                            $this->upgradePickaxe($player, $item, $price);
                            $player->sendMessage(str_replace(["{level}", "{price}"], [$this->getPickaxeLevel($this->getNextPickaxeId($item)), $price], $config->getNested("upgrade-pickaxe.message.upgrade-success")));
                        } else {
                            $player->sendMessage($config->getNested("upgrade-pickaxe.message.no-money"));
                        }
                        break;
                    case 1:
                        return;
                }
            }
        });
        $form->setTitle($config->getNested("upgrade-pickaxe.form.title"));
        $form->setContent(str_replace(["{item}", "{level}", "{price}"], [$this->getFrenchItemName($item), $this->getPickaxeLevel($this->getNextPickaxeId($item)), $this->getPriceToUpgrade($item)], $config->getNested("upgrade-pickaxe.form.content")));
        $form->addButton(str_replace(["{line}", "{price}"], [TextFormat::EOL, $this->getPriceToUpgrade($item)], $config->getNested("upgrade-pickaxe.form.button-upgrade")));
        $form->addButton($config->getNested("upgrade-pickaxe.form.button-cancel"));
        return $form;
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param int $price
     * @return void
     */
    public function upgradePickaxe(Player $player, Item $item, int $price): void {
        $nextPickaxeId = $this->getNextPickaxeId($item);
        $player->getInventory()->remove(ItemFactory::getInstance()->get($item->getId(), $item->getMeta()));
        $player->getInventory()->addItem(ItemFactory::getInstance()->get($nextPickaxeId));
        $player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.anvil_use", $player->getLocation()->x, $player->getLocation()->y, $player->getLocation()->z, 1, 1));
        EconomyAPI::getInstance()->reduceMoney($player, $price);
    }

    /**
     * @param int|Item $item
     * @return int
     */
    public function getPickaxeLevel(int|Item $item): int {
        $item = $item instanceof Item ? $item->getId() : $item;
        return match ($item) {
            UpgradePickaxeIds::WOODEN_PICKAXE => 1,
            UpgradePickaxeIds::STONE_PICKAXE => 2,
            UpgradePickaxeIds::IRON_PICKAXE => 3,
            UpgradePickaxeIds::DIAMOND_PICKAXE => 4,
            UpgradePickaxeIds::NETHERITE_PICKAXE => 5,
            default => 0
        };
    }

    /**
     * @param Block $block
     * @return int
     */
    public function getNecessaryLevelToBreakOre(Block $block): int {
        return match ($block->getId()) {
            BlockLegacyIds::COAL_ORE => 1,
            BlockLegacyIds::IRON_ORE => 2,
            BlockLegacyIds::GOLD_ORE => 3,
            BlockLegacyIds::DIAMOND_ORE => 4,
            BlockLegacyIds::EMERALD_ORE => 5,
            default => 0
        };
    }

    /**
     * @param Item $item
     * @return int
     */
    public function getNextPickaxeId(Item $item): int {
        return match ($item->getId()) {
            UpgradePickaxeIds::WOODEN_PICKAXE => UpgradePickaxeIds::STONE_PICKAXE,
            UpgradePickaxeIds::STONE_PICKAXE => UpgradePickaxeIds::IRON_PICKAXE,
            UpgradePickaxeIds::IRON_PICKAXE => UpgradePickaxeIds::DIAMOND_PICKAXE,
            UpgradePickaxeIds::DIAMOND_PICKAXE => UpgradePickaxeIds::NETHERITE_PICKAXE
        };
    }

    /**
     * @param Item $item
     * @return int|null
     */
    public function getPriceToUpgrade(Item $item): ?int {
        $config = UpgradePickaxe::getInstance()->getConfig();
        return match ($item->getId()) {
            UpgradePickaxeIds::WOODEN_PICKAXE => intval($config->getNested("upgrade-pickaxe.settings.upgrade-price.stone")),
            UpgradePickaxeIds::STONE_PICKAXE => intval($config->getNested("upgrade-pickaxe.settings.upgrade-price.iron")),
            UpgradePickaxeIds::IRON_PICKAXE => intval($config->getNested("upgrade-pickaxe.settings.upgrade-price.diamond")),
            UpgradePickaxeIds::DIAMOND_PICKAXE => intval($config->getNested("upgrade-pickaxe.settings.upgrade-price.netherite")),
            default => null
        };
    }

    /**
     * @param Item $item
     * @return string
     */
    public function getFrenchItemName(Item $item): string {
        return match ($item->getVanillaName()) {
            "Wooden Pickaxe" => "Pioche en bois",
            "Stone Pickaxe" => "Pioche en pierre",
            "Iron Pickaxe" => "Pioche en fer",
            "Diamond Pickaxe" => "Pioche en diamant",
        };
    }

    /**
     * @param Block $block
     * @return string
     */
    public function getFrenchBlockName(Block $block): string {
        return match ($block->getName()) {
            "Coal Ore" => "Minerai de charbon",
            "Iron Ore" => "Minerai de fer",
            "Gold Ore" => "Minerai d'or",
            "Diamond Ore" => "Minerai de diamant",
            "Emerald Ore" => "Minerai d'émeraude"
        };
    }

    /**
     * @param Block $block
     * @param int $level
     * @return bool
     */
    public function hasNecessaryLevelToBreakOre(Block $block, int $level): bool {
        return $this->getNecessaryLevelToBreakOre($block) <= $level;
    }

    /**
     * @param Player $player
     * @param int $price
     * @return bool
     */
    public function canUpgrade(Player $player, int $price): bool {
        return EconomyAPI::getInstance()->myMoney($player) >= $price;
    }

    /* @return string */
    public function getCommandName(): string {
        return strval(UpgradePickaxe::getInstance()->getConfig()->getNested("upgrade-pickaxe.command.name"));
    }

    /* @return Translatable|string */
    public function getCommandDescription(): Translatable|string {
        return strval(UpgradePickaxe::getInstance()->getConfig()->getNested("upgrade-pickaxe.command.description"));
    }

    /* @return Translatable|string|null */
    public function getCommandUsage(): Translatable|string|null {
        return UpgradePickaxe::getInstance()->getConfig()->getNested("upgrade-pickaxe.command.usage");
    }

    /* @return array */
    public function getCommandAliases(): array {
        return UpgradePickaxe::getInstance()->getConfig()->getNested("upgrade-pickaxe.command.aliases");
    }

}
