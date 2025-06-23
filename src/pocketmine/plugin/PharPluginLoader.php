<?php namespace pocketmine\plugin;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\Server;
use pocketmine\utils\PluginException;
class PharPluginLoader implements PluginLoader
{
    private $server;
    public function __construct(Server $server)
    {
        $this->server = $server;
    }
    public function loadPlugin($file)
    {
        if (
            ($description = $this->getPluginDescription($file)) instanceof
            PluginDescription
        ) {
            $this->server
                ->getLogger()
                ->debug(
                    "Load: [" .
                        $description->getFullName() .
                        "] " .
                        mt_rand(0, 30) .
                        "%"
                );
            $dataFolder =
                dirname($file) . DIRECTORY_SEPARATOR . $description->getName();
            if (file_exists($dataFolder) and !is_dir($dataFolder)) {
                throw new \InvalidStateException(
                    "Projected dataFolder '" .
                        $dataFolder .
                        "' for " .
                        $description->getName() .
                        " exists and is not a directory"
                );
            }
            $file = "phar://$file";
            $className = $description->getMain();
            $this->server->getLoader()->addPath("$file/src");
            if (class_exists($className, true)) {
                $plugin = new $className();
                $this->initPlugin($plugin, $description, $dataFolder, $file);
                return $plugin;
            } else {
                throw new PluginException(
                    "Couldn't load plugin " .
                        $description->getName() .
                        ": main class not found"
                );
            }
        }
        return null;
    }
    public function getPluginDescription($file)
    {
        $phar = new \Phar($file);
        if (isset($phar["plugin.yml"])) {
            $pluginYml = $phar["plugin.yml"];
            if ($pluginYml instanceof \PharFileInfo) {
                return new PluginDescription($pluginYml->getContent());
            }
        }
        return null;
    }
    public function getPluginFilters()
    {
        return "/\\.phar$/i";
    }
    private function initPlugin(
        PluginBase $plugin,
        PluginDescription $description,
        $dataFolder,
        $file
    ) {
        $plugin->init($this, $this->server, $description, $dataFolder, $file);
        $plugin->onLoad();
    }
    public function enablePlugin(Plugin $plugin)
    {
        if ($plugin instanceof PluginBase and !$plugin->isEnabled()) {
            $this->server
                ->getLogger()
                ->debug(
                    "Loaded: [" .
                        $plugin->getDescription()->getFullName() .
                        "] 100%"
                );
            $plugin->setEnabled(true);
            $this->server
                ->getPluginManager()
                ->callEvent(new PluginEnableEvent($plugin));
        }
    }
    public function disablePlugin(Plugin $plugin)
    {
        if ($plugin instanceof PluginBase and $plugin->isEnabled()) {
            $this->server
                ->getLogger()
                ->debug(
                    "Unloaded: [" .
                        $plugin->getDescription()->getFullName() .
                        "] 0%"
                );
            $this->server
                ->getPluginManager()
                ->callEvent(new PluginDisableEvent($plugin));
            $plugin->setEnabled(false);
        }
    }
}
