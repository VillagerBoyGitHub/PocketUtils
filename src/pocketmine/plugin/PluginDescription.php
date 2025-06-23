<?php
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

namespace pocketmine\plugin;
use pocketmine\permission\Permission;
use pocketmine\utils\PluginException;
class PluginDescription
{
    private $name;
    private $main;
    private $api;
    private $depend = [];
    private $softDepend = [];
    private $loadBefore = [];
    private $version;
    private $commands = [];
    private $description = null;
    private $authors = [];
    private $website = null;
    private $prefix = null;
    private $order = PluginLoadOrder::POSTWORLD;
    private $geniapi;
    private $permissions = [];
    public function __construct($yamlString)
    {
        $this->loadMap(
            !is_array($yamlString) ? \yaml_parse($yamlString) : $yamlString
        );
    }
    private function loadMap(array $plugin)
    {
        $this->name = preg_replace("[^A-Za-z0-9 _.-]", "", $plugin["name"]);
        if ($this->name === "") {
            throw new PluginException("Invalid PluginDescription name");
        }
        $this->name = str_replace(" ", "_", $this->name);
        $this->version = $plugin["version"] ?? "unknown";

        if($this->version == null) {
            echo $this->name . " doesn't have a plugin version.\n";
        }
        $this->main = $plugin["main"];
        $this->api = !is_array($plugin["api"])
            ? [$plugin["api"]]
            : $plugin["api"];
        if (!isset($plugin["geniapi"])) {
            $this->geniapi = ["1.0.0"];
        } else {
            $this->geniapi = !is_array($plugin["geniapi"])
                ? [$plugin["geniapi"]]
                : $plugin["geniapi"];
        }
        if (stripos($this->main, "pocketmine\\") === 0) {
            throw new PluginException(
                "Invalid PluginDescription main, cannot start within the PocketMine namespace"
            );
        }
        if (isset($plugin["commands"]) and is_array($plugin["commands"])) {
            $this->commands = $plugin["commands"];
        }
        if (isset($plugin["depend"])) {
            $this->depend = (array) $plugin["depend"];
        }
        if (isset($plugin["softdepend"])) {
            $this->softDepend = (array) $plugin["softdepend"];
        }
        if (isset($plugin["loadbefore"])) {
            $this->loadBefore = (array) $plugin["loadbefore"];
        }
        if (isset($plugin["website"])) {
            $this->website = $plugin["website"];
        }
        if (isset($plugin["description"])) {
            $this->description = $plugin["description"];
        }
        if (isset($plugin["prefix"])) {
            $this->prefix = $plugin["prefix"];
        }
        if (isset($plugin["load"])) {
            $order = strtoupper($plugin["load"]);
            if (!defined(PluginLoadOrder::class . "::" . $order)) {
                throw new PluginException("Invalid PluginDescription load");
            } else {
                $this->order = constant(PluginLoadOrder::class . "::" . $order);
            }
        }
        $this->authors = [] ?? "unknown";
        if (isset($plugin["author"])) {
            $this->authors[] = $plugin["author"];
        }
        if (isset($plugin["authors"])) {
            foreach ($plugin["authors"] as $author) {
                $this->authors[] = $author;
            }
        }
        if (isset($plugin["permissions"])) {
            $this->permissions = Permission::loadPermissions(
                $plugin["permissions"]
            );
        }
    }

    private function getAuthorsAsString(): string {
		$authors = $this->getAuthors();
        if($this->getAuthors() == "unknown") {
            return "unknown";
        }
		$flatAuthors = [];

		foreach ($authors as $entry) {
			if (is_array($entry)) {
				$flatAuthors = array_merge($flatAuthors, $entry);
			} else {
				$flatAuthors[] = $entry;
			}
		}

		return implode(", ", $flatAuthors);
	}
    public function getFullName()
    {
        if($this->version == "unknown") {
            return "§e" . $this->name . "§e by " . (string)$this->getAuthorsAsString() . ". The version isn't specified.";
        }
        return "§e" . $this->name . "§e by " . $this->getAuthorsAsString() . " §6v" . $this->version; //I just implemented the authors! :)
    }
    public function getCompatibleApis()
    {
        return $this->api;
    }
    public function getCompatibleGeniApis()
    {
        return $this->geniapi;
    }
    public function getAuthors()
    {
        return $this->authors;
    }
    public function getPrefix()
    {
        return $this->prefix;
    }
    public function getCommands()
    {
        return $this->commands;
    }
    public function getDepend()
    {
        return $this->depend;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getLoadBefore()
    {
        return $this->loadBefore;
    }
    public function getMain()
    {
        return $this->main;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getOrder()
    {
        return $this->order;
    }
    public function getPermissions()
    {
        return $this->permissions;
    }
    public function getSoftDepend()
    {
        return $this->softDepend;
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function getWebsite()
    {
        return $this->website;
    }
}
