<?php

namespace pocketmine\permission;

use pocketmine\Server;

abstract class DefaultPermissions{
	const ROOT = "pocketmine";

	/**
	 * @param Permission $perm
	 * @param Permission $parent
	 *
	 * @return Permission
	 */
	public static function registerPermission(Permission $perm, Permission $parent = null){
		if($parent instanceof Permission){
			$parent->getChildren()[$perm->getName()] = true;

			return self::registerPermission($perm);
		}
		Server::getInstance()->getPluginManager()->addPermission($perm);

		return Server::getInstance()->getPluginManager()->getPermission($perm->getName());
	}

	public static function registerCorePermissions(){
		$parent = self::registerPermission(new Permission(self::ROOT, "Allows using all PocketMine commands and utilities"));

		$broadcasts = self::registerPermission(new Permission(self::ROOT . ".broadcast", "Allows the user to receive all broadcast messages"), $parent);

		self::registerPermission(new Permission(self::ROOT . ".broadcast.admin", "Allows the user to receive administrative broadcasts", Permission::DEFAULT_OP), $broadcasts);
		self::registerPermission(new Permission(self::ROOT . ".broadcast.user", "Allows the user to receive user broadcasts", Permission::DEFAULT_TRUE), $broadcasts);

		$broadcasts->recalculatePermissibles();

		$commands = self::registerPermission(new Permission(self::ROOT . ".command", "Allows using all PocketMine commands"), $parent);

		$whitelist = self::registerPermission(new Permission(self::ROOT . ".command.whitelist", "Allows the user to modify the server whitelist", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.add", "Allows the user to add a player to the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.remove", "Allows the user to remove a player to the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.reload", "Allows the user to reload the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.enable", "Allows the user to enable the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.disable", "Allows the user to disable the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.list", "Allows the user to list all the players on the server whitelist"), $whitelist);
		$whitelist->recalculatePermissibles();
		$op = self::registerPermission(new Permission(self::ROOT . ".command.op", "Allows the user to change operators", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.op.give", "Allows the user to give a player operator status"), $op);
		self::registerPermission(new Permission(self::ROOT . ".command.op.take", "Allows the user to take a players operator status"), $op);
		$op->recalculatePermissibles();

		$save = self::registerPermission(new Permission(self::ROOT . ".command.save", "Allows the user to save the worlds", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.save.enable", "Allows the user to enable automatic saving"), $save);
		self::registerPermission(new Permission(self::ROOT . ".command.save.disable", "Allows the user to disable automatic saving"), $save);
		self::registerPermission(new Permission(self::ROOT . ".command.save.perform", "Allows the user to perform a manual save"), $save);
		$save->recalculatePermissibles();

		$time = self::registerPermission(new Permission(self::ROOT . ".command.time", "Allows the user to alter the time", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.time.add", "Allows the user to fast-forward time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.set", "Allows the user to change the time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.start", "Allows the user to restart the time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.stop", "Allows the user to stop the time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.query", "Allows the user query the time"), $time);
		$time->recalculatePermissibles();

		$kill = self::registerPermission(new Permission(self::ROOT . ".command.kill", "Allows the user to kill players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.kill.self", "Allows the user to commit suicide", Permission::DEFAULT_TRUE), $kill);
		self::registerPermission(new Permission(self::ROOT . ".command.kill.other", "Allows the user to kill other players"), $kill);
		$kill->recalculatePermissibles();
		self::registerPermission(new Permission(self::ROOT . ".command.tell", "Allows the user to privately message another player", Permission::DEFAULT_TRUE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.say", "Allows the user to talk as the console", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.give", "Allows the user to give items to players", Permission::DEFAULT_OP), $commands);
		
		$effect = self::registerPermission(new Permission(self::ROOT . ".command.effect", "Allows the user to give/take potion effects", Permission::DEFAULT_FALSE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.effect.other", "Allows the user to give/take potion effects for other", Permission::DEFAULT_OP), $commands);
		$effect->recalculatePermissibles();
		
		self::registerPermission(new Permission(self::ROOT . ".command.enchant", "Allows the user to enchant items", Permission::DEFAULT_FALSE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.particle", "Allows the user to create particle effects", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.teleport", "Allows the user to teleport players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.kick", "Allows the user to kick players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.stop", "Allows the user to stop the server", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.list", "Allows the user to list all online players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.plugins", "Allows the user to view the list of plugins", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.reload", "Allows the user to reload the server settings", Permission::DEFAULT_FALSE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.version", "Allows the user to view the version of the server", Permission::DEFAULT_TRUE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.gamemode", "Allows the user to change the gamemode of players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.defaultgamemode", "Allows the user to change the default gamemode", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.seed", "Allows the user to view the seed of the world", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.status", "Allows the user to view the server performance", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.gc", "Allows the user to fire garbage collection tasks", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.dumpmemory", "Allows the user to dump memory contents", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.timings", "Allows the user to records timings for all plugin events", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.spawnpoint", "Allows the user to change player's spawnpoint", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.setworldspawn", "Allows the user to change the world spawn", Permission::DEFAULT_FALSE), $commands);
		
		self::registerPermission(new Permission(self::ROOT . ".command.extractphar", "", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.extractplugin", "", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.makeplugin", "", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.makeserver", "", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.weather", "", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.loadplugin", "", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.lvdat", "", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.biome", "" , Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.cave", "" , Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.setblock", "" , Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.fill", "" , Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.summon", "" , Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.xp", "" , Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.chunkinfo", "" , Permission::DEFAULT_OP), $commands);

		$commands->recalculatePermissibles();

		$parent->recalculatePermissibles();
	}
}
