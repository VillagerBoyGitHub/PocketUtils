<?php
namespace pocketmine\network;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\AddPaintingPacket;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\AdventureSettingsPacket;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\protocol\ContainerClosePacket;
use pocketmine\network\protocol\ContainerOpenPacket;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetDataPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\CraftingDataPacket;
use pocketmine\network\protocol\CraftingEventPacket;
use pocketmine\network\protocol\ChangeDimensionPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\DropItemPacket;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\ItemFrameDropItemPacket;
use pocketmine\network\protocol\MapInfoRequestPacket;
use pocketmine\network\protocol\p70\AnyversionInfo;
use pocketmine\network\protocol\RequestChunkRadiusPacket;
use pocketmine\network\protocol\SetEntityLinkPacket;
use pocketmine\network\protocol\BlockEntityDataPacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\HurtArmorPacket;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\network\protocol\DisconnectPacket;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\network\protocol\PlayStatusPacket;
use pocketmine\network\protocol\TextPacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\PlayerActionPacket;
use pocketmine\network\protocol\MobArmorEquipmentPacket;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\network\protocol\RemoveBlockPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\RemovePlayerPacket;
use pocketmine\network\protocol\RespawnPacket;
use pocketmine\network\protocol\SetDifficultyPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\SetPlayerGameTypePacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\StartGamePacket;
use pocketmine\network\protocol\TakeItemEntityPacket;
use pocketmine\network\protocol\BlockEventPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\network\protocol\PlayerInputPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;
class Network{
public static$BATCH_THRESHOLD=512;
private$packetPool;
private$server;
private$interfaces=[];
private$advancedInterfaces=[];
private$upload=0;
private$download=0;
private$name;
public function __construct(Server$server){$this->registerPackets();$this->server=$server;}
public function addStatistics($upload,$download){
$this->upload+=$upload;
$this->download+=$download;}
public function getUpload(){
return$this->upload;}
public function getDownload(){
return$this->download;}
public function resetStatistics(){
$this->upload=0;
$this->download=0;}
public function getInterfaces(){
return$this->interfaces;}
public function processInterfaces() {
foreach ($this->interfaces as $interface) {
try {
$interface->process();
} catch (\Throwable $e) {
$logger = $this->server->getLogger();
if(\pocketmine\DEBUG > 1) {
if($logger instanceof MainLogger) {
$logger->logException($e);}}
$interface->emergencyShutdown();
$this->unregisterInterface($interface);
$logger->critical($this->server->getLanguage()->translateString("pocketmine.server.networkError",[get_class($interface),$e->getMessage()]));}}}
public function registerInterface(SourceInterface$interface){
$this->interfaces[$hash=spl_object_hash($interface)] = $interface;
if($interface instanceof AdvancedSourceInterface){
$this->advancedInterfaces[$hash]=$interface;
$interface->setNetwork($this);}
$interface->setName($this->name);}
public function unregisterInterface(SourceInterface$interface){
unset($this->interfaces[$hash=spl_object_hash($interface)],$this->advancedInterfaces[$hash]);}
public function setName($name){
$this->name=(string)$name;
foreach($this->interfaces as$interface){
$interface->setName($this->name);}}
public function getName(){
return$this->name;}
public function updateName(){
foreach($this->interfaces as$interface){
$interface->setName($this->name);}}
public function registerPacket($id,$class){
$this->packetPool[$id]=new$class;}
public function getServer(){
return$this->server;
}
public function processBatch($packet,Player$p){
$str=\zlib_decode($packet->payload,1024*1024*128);
$len=strlen($str);
$offset=0;
try{
while($offset<$len){
$pkLen=Binary::readInt(substr($str,$offset,4));
$offset+=4;
$buf=substr($str,$offset,$pkLen);
$offset+=$pkLen;
if(strlen($buf)===0){
throw new\InvalidStateException("Empty or invalid BatchPacket received");}
if(($pk=$this->getPacket(ord($buf{0})))!==null){
if($pk::NETWORK_ID===Info::BATCH_PACKET){
throw new\InvalidStateException("Invalid BatchPacket inside BatchPacket");}
$pk->setBuffer($buf,1);
$pk->decode();
$p->handleDataPacket($pk);
if($pk->getOffset()<=0){
return;}}}
}catch(\Throwable$e){
if(\pocketmine\DEBUG>1){
$logger=$this->server->getLogger();
if($logger instanceof MainLogger){
$logger->debug("BatchPacket 0x".bin2hex($packet->payload));
$logger->logException($e);}}}}
public function getPacket($id){
$class=$this->packetPool[$id];
if($class!==null){
return clone$class;}
return null;}
public function sendPacket($address,$port,$payload){
foreach($this->advancedInterfaces as$interface){
$interface->sendRawPacket($address,$port,$payload);}}
public function blockAddress($address,$timeout=300){
foreach($this->advancedInterfaces as$interface){
$interface->blockAddress($address,$timeout);}}
public function unblockAddress($address){
foreach($this->advancedInterfaces as$interface){
$interface->unblockAddress($address);}}
private function registerPackets(){
$this->packetPool=new\SplFixedArray(256);
$this->registerPacket(ProtocolInfo::BATCH_PACKET,BatchPacket::class);
$this->registerPacket(ProtocolInfo::LOGIN_PACKET,LoginPacket::class);
$this->registerPacket(ProtocolInfo::PLAY_STATUS_PACKET,PlayStatusPacket::class);
$this->registerPacket(ProtocolInfo::DISCONNECT_PACKET,DisconnectPacket::class);
$this->registerPacket(ProtocolInfo::TEXT_PACKET,TextPacket::class);
$this->registerPacket(ProtocolInfo::SET_TIME_PACKET,SetTimePacket::class);
$this->registerPacket(ProtocolInfo::START_GAME_PACKET,StartGamePacket::class);
$this->registerPacket(ProtocolInfo::ADD_PLAYER_PACKET,AddPlayerPacket::class);
$this->registerPacket(ProtocolInfo::ADD_ENTITY_PACKET,AddEntityPacket::class);
$this->registerPacket(ProtocolInfo::REMOVE_ENTITY_PACKET,RemoveEntityPacket::class);
$this->registerPacket(ProtocolInfo::ADD_ITEM_ENTITY_PACKET,AddItemEntityPacket::class);
$this->registerPacket(ProtocolInfo::TAKE_ITEM_ENTITY_PACKET,TakeItemEntityPacket::class);
$this->registerPacket(ProtocolInfo::MOVE_ENTITY_PACKET,MoveEntityPacket::class);
$this->registerPacket(ProtocolInfo::MOVE_PLAYER_PACKET,MovePlayerPacket::class);
$this->registerPacket(ProtocolInfo::REMOVE_BLOCK_PACKET,RemoveBlockPacket::class);
$this->registerPacket(ProtocolInfo::UPDATE_BLOCK_PACKET,UpdateBlockPacket::class);
$this->registerPacket(ProtocolInfo::ADD_PAINTING_PACKET,AddPaintingPacket::class);
$this->registerPacket(ProtocolInfo::EXPLODE_PACKET,ExplodePacket::class);
$this->registerPacket(ProtocolInfo::LEVEL_EVENT_PACKET,LevelEventPacket::class);
$this->registerPacket(ProtocolInfo::BLOCK_EVENT_PACKET,BlockEventPacket::class);
$this->registerPacket(ProtocolInfo::ENTITY_EVENT_PACKET,EntityEventPacket::class);
$this->registerPacket(ProtocolInfo::MOB_EQUIPMENT_PACKET,MobEquipmentPacket::class);
$this->registerPacket(ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET,MobArmorEquipmentPacket::class);
$this->registerPacket(ProtocolInfo::INTERACT_PACKET,InteractPacket::class);
$this->registerPacket(ProtocolInfo::USE_ITEM_PACKET,UseItemPacket::class);
$this->registerPacket(ProtocolInfo::PLAYER_ACTION_PACKET,PlayerActionPacket::class);
$this->registerPacket(ProtocolInfo::HURT_ARMOR_PACKET,HurtArmorPacket::class);
$this->registerPacket(ProtocolInfo::SET_ENTITY_DATA_PACKET,SetEntityDataPacket::class);
$this->registerPacket(ProtocolInfo::SET_ENTITY_MOTION_PACKET,SetEntityMotionPacket::class);
$this->registerPacket(ProtocolInfo::SET_ENTITY_LINK_PACKET,SetEntityLinkPacket::class);
$this->registerPacket(ProtocolInfo::SET_HEALTH_PACKET,SetHealthPacket::class);
$this->registerPacket(ProtocolInfo::SET_SPAWN_POSITION_PACKET,SetSpawnPositionPacket::class);
$this->registerPacket(ProtocolInfo::ANIMATE_PACKET,AnimatePacket::class);
$this->registerPacket(ProtocolInfo::RESPAWN_PACKET,RespawnPacket::class);
$this->registerPacket(ProtocolInfo::DROP_ITEM_PACKET,DropItemPacket::class);
$this->registerPacket(ProtocolInfo::CONTAINER_OPEN_PACKET,ContainerOpenPacket::class);
$this->registerPacket(ProtocolInfo::CONTAINER_CLOSE_PACKET,ContainerClosePacket::class);
$this->registerPacket(ProtocolInfo::CONTAINER_SET_SLOT_PACKET,ContainerSetSlotPacket::class);
$this->registerPacket(ProtocolInfo::CONTAINER_SET_DATA_PACKET,ContainerSetDataPacket::class);
$this->registerPacket(ProtocolInfo::CONTAINER_SET_CONTENT_PACKET,ContainerSetContentPacket::class);
$this->registerPacket(ProtocolInfo::CRAFTING_DATA_PACKET,CraftingDataPacket::class);
$this->registerPacket(ProtocolInfo::CRAFTING_EVENT_PACKET,CraftingEventPacket::class);
$this->registerPacket(ProtocolInfo::ADVENTURE_SETTINGS_PACKET,AdventureSettingsPacket::class);
$this->registerPacket(ProtocolInfo::BLOCK_ENTITY_DATA_PACKET,BlockEntityDataPacket::class);
$this->registerPacket(ProtocolInfo::FULL_CHUNK_DATA_PACKET,FullChunkDataPacket::class);
$this->registerPacket(ProtocolInfo::SET_DIFFICULTY_PACKET,SetDifficultyPacket::class);
$this->registerPacket(ProtocolInfo::PLAYER_LIST_PACKET,PlayerListPacket::class);
$this->registerPacket(ProtocolInfo::PLAYER_INPUT_PACKET,PlayerInputPacket::class);
$this->registerPacket(ProtocolInfo::SET_PLAYER_GAMETYPE_PACKET,SetPlayerGameTypePacket::class);
$this->registerPacket(ProtocolInfo::CHANGE_DIMENSION_PACKET,ChangeDimensionPacket::class);
$this->registerPacket(ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET,RequestChunkRadiusPacket::class);
$this->registerPacket(ProtocolInfo::CHUNK_RADIUS_UPDATED_PACKET,ChunkRadiusUpdatedPacket::class);
$this->registerPacket(ProtocolInfo::ITEM_FRAME_DROP_ITEM_PACKET,ItemFrameDropItemPacket::class);
$this->registerPacket(ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET,ClientboundMapItemDataPacket::class);
$this->registerPacket(ProtocolInfo::MAP_INFO_REQUEST_PACKET,MapInfoRequestPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::LOGIN_PACKET,\pocketmine\network\protocol\p70\LoginPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::PLAY_STATUS_PACKET,\pocketmine\network\protocol\p70\PlayStatusPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::DISCONNECT_PACKET,\pocketmine\network\protocol\p70\DisconnectPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::BATCH_PACKET,\pocketmine\network\protocol\p70\BatchPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::TEXT_PACKET,\pocketmine\network\protocol\p70\TextPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_TIME_PACKET,\pocketmine\network\protocol\p70\SetTimePacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::START_GAME_PACKET,\pocketmine\network\protocol\p70\StartGamePacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::ADD_PLAYER_PACKET,\pocketmine\network\protocol\p70\AddPlayerPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::REMOVE_PLAYER_PACKET,\pocketmine\network\protocol\p70\RemovePlayerPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::ADD_ENTITY_PACKET,\pocketmine\network\protocol\p70\AddEntityPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::REMOVE_ENTITY_PACKET,\pocketmine\network\protocol\p70\RemoveEntityPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::ADD_ITEM_ENTITY_PACKET,\pocketmine\network\protocol\p70\AddItemEntityPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::TAKE_ITEM_ENTITY_PACKET,\pocketmine\network\protocol\p70\TakeItemEntityPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::MOVE_ENTITY_PACKET,\pocketmine\network\protocol\p70\MoveEntityPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::MOVE_PLAYER_PACKET,\pocketmine\network\protocol\p70\MovePlayerPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::REMOVE_BLOCK_PACKET,\pocketmine\network\protocol\p70\RemoveBlockPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::UPDATE_BLOCK_PACKET,\pocketmine\network\protocol\p70\UpdateBlockPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::ADD_PAINTING_PACKET,\pocketmine\network\protocol\p70\AddPaintingPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::EXPLODE_PACKET,\pocketmine\network\protocol\p70\ExplodePacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::LEVEL_EVENT_PACKET,\pocketmine\network\protocol\p70\LevelEventPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::BLOCK_EVENT_PACKET,\pocketmine\network\protocol\p70\BlockEventPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::ENTITY_EVENT_PACKET,\pocketmine\network\protocol\p70\EntityEventPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::MOB_EQUIPMENT_PACKET,\pocketmine\network\protocol\p70\MobEquipmentPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::MOB_ARMOR_EQUIPMENT_PACKET,\pocketmine\network\protocol\p70\MobArmorEquipmentPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::INTERACT_PACKET,\pocketmine\network\protocol\p70\InteractPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::USE_ITEM_PACKET,\pocketmine\network\protocol\p70\UseItemPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::PLAYER_ACTION_PACKET,\pocketmine\network\protocol\p70\PlayerActionPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::HURT_ARMOR_PACKET,\pocketmine\network\protocol\p70\HurtArmorPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_ENTITY_DATA_PACKET,\pocketmine\network\protocol\p70\SetEntityDataPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_ENTITY_MOTION_PACKET,\pocketmine\network\protocol\p70\SetEntityMotionPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_ENTITY_LINK_PACKET,\pocketmine\network\protocol\p70\SetEntityLinkPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_HEALTH_PACKET,\pocketmine\network\protocol\p70\SetHealthPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_SPAWN_POSITION_PACKET,\pocketmine\network\protocol\p70\SetSpawnPositionPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::ANIMATE_PACKET,\pocketmine\network\protocol\p70\AnimatePacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::RESPAWN_PACKET,\pocketmine\network\protocol\p70\RespawnPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::DROP_ITEM_PACKET,\pocketmine\network\protocol\p70\DropItemPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CONTAINER_OPEN_PACKET,\pocketmine\network\protocol\p70\ContainerOpenPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CONTAINER_CLOSE_PACKET,\pocketmine\network\protocol\p70\ContainerClosePacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CONTAINER_SET_SLOT_PACKET,\pocketmine\network\protocol\p70\ContainerSetSlotPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CONTAINER_SET_DATA_PACKET,\pocketmine\network\protocol\p70\ContainerSetDataPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CONTAINER_SET_CONTENT_PACKET,\pocketmine\network\protocol\p70\ContainerSetContentPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CRAFTING_DATA_PACKET,\pocketmine\network\protocol\p70\CraftingDataPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CRAFTING_EVENT_PACKET,\pocketmine\network\protocol\p70\CraftingEventPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::ADVENTURE_SETTINGS_PACKET,\pocketmine\network\protocol\p70\AdventureSettingsPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::BLOCK_ENTITY_DATA_PACKET,\pocketmine\network\protocol\p70\BlockEntityDataPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::FULL_CHUNK_DATA_PACKET,\pocketmine\network\protocol\p70\FullChunkDataPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_DIFFICULTY_PACKET,\pocketmine\network\protocol\p70\SetDifficultyPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::PLAYER_LIST_PACKET,\pocketmine\network\protocol\p70\PlayerListPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::PLAYER_INPUT_PACKET,\pocketmine\network\protocol\p70\PlayerInputPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::SET_PLAYER_GAMETYPE_PACKET,\pocketmine\network\protocol\p70\SetPlayerGameTypePacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CHANGE_DIMENSION_PACKET,\pocketmine\network\protocol\p70\ChangeDimensionPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CHUNK_RADIUS_UPDATE_PACKET,\pocketmine\network\protocol\p70\ChunkRadiusUpdatePacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::REQUEST_CHUNK_RADIUS_PACKET,\pocketmine\network\protocol\p70\RequestChunkRadiusPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::CLIENTBOUND_MAP_ITEM_DATA_PACKET,\pocketmine\network\protocol\p70\ClientboundMapItemDataPacket::class);
$this->registerPacket(\pocketmine\network\protocol\p70\Info::MAP_INFO_REQUEST_PACKET,\pocketmine\network\protocol\p70\MapInfoRequestPacket::class);}}