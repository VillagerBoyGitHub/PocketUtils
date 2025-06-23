<?php

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class TellCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.tell.description",
			"%commands.message.usage",
			["w", "whisper", "msg", "m"]
		);
		$this->setPermission("pocketmine.command.tell");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			$sender->sendMessage(new TranslationContainer("§a» §fUsage: §e/tell <player> <msg>", [$this->usageMessage]));
			return false;
		}

		$name = strtolower(array_shift($args));
		$player = $sender->getServer()->getPlayer($name);
		$message = implode(" ", $args);

		if($player === $sender){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "§c» §fYou §ccannot §fsend a message to yourself!"));
			return true;
		}

		if($player instanceof Player){
			$sender->sendMessage("§e".$sender->getName()." §8»§e " . $player->getName() . "§8|§a " . $message);
			$player->sendMessage("§e" . $sender->getName() . " §8|§e " . $player->getName() . "§8»§a " . $message);

			// **Broadcast to Players with "pocketmine.command.tell.spy" Permission**
			foreach(Server::getInstance()->getOnlinePlayers() as $spy){
				if($spy->hasPermission("pocketmine.command.tell.spy") && $spy !== $sender && $spy !== $player){
					$spy->sendMessage("§7[Spy] §e".$sender->getName()." §8->§e " . $player->getName() . "§8: §a" . $message);
				}
			}
		}else{
			$sender->sendMessage(new TranslationContainer("§c» §fPlayer is not found!"));
		}

		return true;
	}
}