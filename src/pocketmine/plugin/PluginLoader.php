<?php namespace pocketmine\plugin;
/*

 * Patched by Robert. https://villagerboy.xyz
 * 
 * GitHub: https://github.com/@VillagerBoyGithub
 * 
 * Check out SodiumNodes for some cheap servers: 
 * https://sodiumnodes.org
 * 
 * 
 *
*/

interface PluginLoader
{
    public function loadPlugin($file); // let's not forget the Plugin parameter here guys!
    public function getPluginDescription($file);
    public function getPluginFilters();
    public function enablePlugin(Plugin $plugin);
    public function disablePlugin(Plugin $plugin);
}
