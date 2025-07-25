<?php
namespace pocketmine\network;
use pocketmine\network\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\p70\AddEntityPacket;
use pocketmine\network\protocol\p70\AddItemEntityPacket;
use pocketmine\network\protocol\p70\AddPaintingPacket;
use pocketmine\network\protocol\p70\AddPlayerPacket;
use pocketmine\network\protocol\p70\AdventureSettingsPacket;
use pocketmine\network\protocol\p70\AnimatePacket;
use pocketmine\network\protocol\p70\BatchPacket;
use pocketmine\network\protocol\p70\BlockEntityDataPacket;
use pocketmine\network\protocol\p70\BlockEventPacket;
use pocketmine\network\protocol\p70\ChangeDimensionPacket;
use pocketmine\network\protocol\p70\ChunkRadiusUpdatePacket;
use pocketmine\network\protocol\p70\ClientboundMapItemDataPacket;
use pocketmine\network\protocol\p70\ContainerClosePacket;
use pocketmine\network\protocol\p70\ContainerOpenPacket;
use pocketmine\network\protocol\p70\ContainerSetContentPacket;
use pocketmine\network\protocol\p70\ContainerSetDataPacket;
use pocketmine\network\protocol\p70\ContainerSetSlotPacket;
use pocketmine\network\protocol\p70\CraftingDataPacket;
use pocketmine\network\protocol\p70\CraftingEventPacket;
use pocketmine\network\protocol\p70\DisconnectPacket;
use pocketmine\network\protocol\p70\DropItemPacket;
use pocketmine\network\protocol\p70\EntityEventPacket;
use pocketmine\network\protocol\p70\ExplodePacket;
use pocketmine\network\protocol\p70\FullChunkDataPacket;
use pocketmine\network\protocol\p70\HurtArmorPacket;
use pocketmine\network\protocol\p70\InteractPacket;
use pocketmine\network\protocol\p70\ItemFrameDropPacket;
use pocketmine\network\protocol\p70\LevelEventPacket;
use pocketmine\network\protocol\p70\MapInfoRequestPacket;
use pocketmine\network\protocol\p70\MobArmorEquipmentPacket;
use pocketmine\network\protocol\p70\MobEffectPacket;
use pocketmine\network\protocol\p70\MobEquipmentPacket;
use pocketmine\network\protocol\p70\MoveEntityPacket;
use pocketmine\network\protocol\p70\MovePlayerPacket;
use pocketmine\network\protocol\p70\PlayerActionPacket;
use pocketmine\network\protocol\p70\PlayerInputPacket;
use pocketmine\network\protocol\p70\PlayerListPacket;
use pocketmine\network\protocol\p70\PlayStatusPacket;
use pocketmine\network\protocol\p70\RemoveBlockPacket;
use pocketmine\network\protocol\p70\RemoveEntityPacket;
use pocketmine\network\protocol\p70\RemovePlayerPacket;
use pocketmine\network\protocol\p70\RequestChunkRadiusPacket;
use pocketmine\network\protocol\p70\RespawnPacket;
use pocketmine\network\protocol\p70\SetDifficultyPacket;
use pocketmine\network\protocol\p70\SetEntityDataPacket;
use pocketmine\network\protocol\p70\SetEntityLinkPacket;
use pocketmine\network\protocol\p70\SetEntityMotionPacket;
use pocketmine\network\protocol\p70\SetHealthPacket;
use pocketmine\network\protocol\p70\SetPlayerGameTypePacket;
use pocketmine\network\protocol\p70\SetSpawnPositionPacket;
use pocketmine\network\protocol\p70\SetTimePacket;
use pocketmine\network\protocol\p70\StartGamePacket;
use pocketmine\network\protocol\p70\TakeItemEntityPacket;
use pocketmine\network\protocol\p70\TextPacket;
use pocketmine\network\protocol\p70\UpdateAttributesPacket;
use pocketmine\network\protocol\p70\UpdateBlockPacket;
use pocketmine\network\protocol\p70\UseItemPacket;
use pocketmine\network\protocol\p70\DataPacket as oldPkg;
use pocketmine\network\protocol\p70\BatchPacket as oldBpkg;
use pocketmine\Player;
class AnyVersionManager{
  public static $protocolVersions = [
    "0.15" => [84],
    "0.14" => [41, 42, 43, 44, 45, 46, 60, 70]];
public static function parsePacket(Player $player, $packet){
  if($packet  instanceof oldPkg or $packet instanceof oldBpkg){
    return $packet;
  }
  switch($player->getProtocol()){
    case 70:
    case 41:
    case 42:
    case 43:
    case 44:
    case 45:
    case 46:
    $pk = null;
    switch($packet->pid()){
      case Info::PLAY_STATUS_PACKET:
      $pk = new PlayStatusPacket();
      $pk->status = $packet->status;
      break;
      case Info::SERVER_TO_CLIENT_HANDSHAKE_PACKET:
      break;
      case Info::CLIENT_TO_SERVER_HANDSHAKE_PACKET:
      break;
      case Info::DISCONNECT_PACKET:
      $pk = new DisconnectPacket();
      $pk->message = $packet->message;
      break;
      case Info::BATCH_PACKET:
      $pk = new BatchPacket();
      $pk->payload = $packet->payload;
      break;
      case Info::TEXT_PACKET:
      $pk = new TextPacket();
      $pk->type = $packet->type;
      $pk->source = $packet->source;
      $pk->message = $packet->message;
      $pk->parameters = $packet->parameters;
      break;
      case Info::SET_TIME_PACKET:
      $pk = new SetTimePacket();
      $pk->time = $packet->time;
      $pk->started = $packet->started;
      break;
      case Info::START_GAME_PACKET:
      $pk = new StartGamePacket();
      $pk->seed=$packet->seed;
      $pk->dimension=$packet->dimension;
      $pk->generator=$packet->generator;
      $pk->gamemode=$packet->gamemode;
      $pk->eid=$packet->eid;
      $pk->spawnX=$packet->spawnX;
      $pk->spawnY=$packet->spawnY;
      $pk->spawnZ=$packet->spawnZ;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->unknown=$packet->unknown;
      break;
      case Info::ADD_PLAYER_PACKET:
      $pk = new AddPlayerPacket();
      $pk->uuid=$packet->uuid;
      $pk->username=$packet->username;
      $pk->eid=$packet->eid;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->speedX=$packet->speedX;
      $pk->speedY=$packet->speedY;
      $pk->speedZ=$packet->speedZ;
      $pk->pitch=$packet->pitch;
      $pk->yaw=$packet->yaw;
      $pk->item=$packet->item;
      $pk->metadata=$packet->metadata;
      break;
      case Info::ADD_ENTITY_PACKET:
      $pk = new AddEntityPacket();
      $pk->eid=$packet->eid;
      $pk->type=$packet->type;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->speedX=$packet->speedX;
      $pk->speedY=$packet->speedY;
      $pk->speedZ=$packet->speedZ;
      $pk->yaw=$packet->yaw;
      $pk->pitch=$packet->pitch;
      $pk->metadata=$packet->metadata;
      $pk->links=$packet->links;
      break;
      case Info::ADD_ITEM_ENTITY_PACKET:
      $pk = new AddItemEntityPacket();
      $pk->eid=$packet->eid;
      $pk->item=$packet->item;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->speedX=$packet->speedX;
      $pk->speedY=$packet->speedY;
      $pk->speedZ=$packet->speedZ;
      break;
      case Info::TAKE_ITEM_ENTITY_PACKET:
      $pk = new TakeItemEntityPacket();
      $pk->target=$packet->target;
      $pk->eid=$packet->eid;
      break;
      case Info::MOVE_ENTITY_PACKET:
      $pk = new MoveEntityPacket();
      $pk->entities=[[$packet->eid,$packet->x,$packet->y,$packet->z,$packet->yaw,$packet->headYaw,$packet->pitch]];
      break;
      case Info::MOVE_PLAYER_PACKET:
      $pk = new MovePlayerPacket();
      $pk->eid=$packet->eid;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->yaw=$packet->yaw;
      $pk->bodyYaw=$packet->bodyYaw;
      $pk->pitch=$packet->pitch;
      $pk->mode=$packet->mode;
      $pk->onGround=$packet->onGround;
      break;
      case Info::RIDER_JUMP_PACKET:
      break;
      case Info::REMOVE_BLOCK_PACKET:
      $pk = new RemoveBlockPacket();
      $pk->eid=$packet->eid;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      break;
      case Info::UPDATE_BLOCK_PACKET:
      $pk = new UpdateBlockPacket();
      $pk->records=[[$packet->x,$packet->z,$packet->y,$packet->blockId, $packet->blockData,$packet->flags]];
      break;
      case Info::ADD_PAINTING_PACKET:
      $pk = new AddPaintingPacket();
      $pk->eid=$packet->eid;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->direction=$packet->direction;
      $pk->title=$packet->title;
      break;
      case Info::EXPLODE_PACKET:
      $pk = new ExplodePacket();
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->radius=$packet->radius;
      $pk->records=$packet->records;
      break;
      case Info::LEVEL_EVENT_PACKET:
      $pk = new LevelEventPacket();
      $pk->evid=$packet->evid;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->data=$packet->data;
      break;
      case Info::BLOCK_EVENT_PACKET:
      $pk = new BlockEventPacket();
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->case1=$packet->case1;
      $pk->case2=$packet->case2;
      break;
      case Info::ENTITY_EVENT_PACKET:
      $pk = new EntityEventPacket();
      $pk->eid=$packet->eid;
      $pk->event=$packet->event;
      break;
      case Info::MOB_EFFECT_PACKET:
      $pk = new MobEffectPacket();
      $pk->eid=$packet->eid;
      $pk->eventId=$packet->eventId;
      $pk->effectId=$packet->effectId;
      $pk->amplifier=$packet->amplifier;
      $pk->particles=$packet->particles;
      $pk->duration=$packet->duration;
      break;
      case Info::UPDATE_ATTRIBUTES_PACKET:
      $pk = new UpdateAttributesPacket();
      $pk->entityId=$packet->entityId;
      $pk->entries=$packet->entries;
      break;
      case Info::MOB_EQUIPMENT_PACKET:
      $pk = new MobEquipmentPacket();
      $pk->eid=$packet->eid;
      $pk->item=$packet->item;
      $pk->slot=$packet->slot;
      $pk->selectedSlot=$packet->selectedSlot;
      break;
      case Info::MOB_ARMOR_EQUIPMENT_PACKET:
      $pk = new MobArmorEquipmentPacket();
      $pk->eid=$packet->eid;
      $pk->slots=$packet->slots;
      break;
      case Info::INTERACT_PACKET:
      $pk = new InteractPacket();
      $pk->action=$packet->action;
      $pk->eid=$packet->eid;
      $pk->target=$packet->target;
      break;
      case Info::USE_ITEM_PACKET:
      $pk = new UseItemPacket();
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->face=$packet->face;
      $pk->item=$packet->item;
      $pk->fx=$packet->fx;
      $pk->fy=$packet->fy;
      $pk->fz=$packet->fz;
      $pk->posX=$packet->posX;
      $pk->posY=$packet->posY;
      $pk->posZ=$packet->posZ;
      break;
      case Info::PLAYER_ACTION_PACKET:
      $pk = new PlayerActionPacket();
      $pk->eid=$packet->eid;
      $pk->action=$packet->action;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->face=$packet->face;
      break;
      case Info::HURT_ARMOR_PACKET:
      $pk = new HurtArmorPacket();
      $pk->health=$packet->health;
      break;
      case Info::SET_ENTITY_DATA_PACKET:
      $pk = new SetEntityDataPacket();
      $pk->eid=$packet->eid;
      $pk->metadata=$packet->metadata;
      break;
      case Info::SET_ENTITY_MOTION_PACKET:
      $pk = new SetEntityMotionPacket();
      $pk->entities=$packet->entities;
      break;
      case Info::SET_ENTITY_LINK_PACKET:
      $pk = new SetEntityLinkPacket();
      $pk->from=$packet->from;
      $pk->to=$packet->to;
      $pk->type=$packet->type;
      break;
      case Info::SET_HEALTH_PACKET:
      $pk = new SetHealthPacket();
      $pk->health=$packet->health;
      break;
      case Info::SET_SPAWN_POSITION_PACKET:
      $pk = new SetSpawnPositionPacket();
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      break;
      case Info::ANIMATE_PACKET:
      $pk = new AnimatePacket();
      $pk->action=$packet->action;
      $pk->eid=$packet->eid;
      break;
      case Info::RESPAWN_PACKET:
      $pk = new RespawnPacket();
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      break;
      case Info::DROP_ITEM_PACKET:
      $pk = new DropItemPacket();
      $pk->type=$packet->type;
      $pk->item=$packet->item;
      break;
      case Info::CONTAINER_OPEN_PACKET:
      $pk = new ContainerOpenPacket();
      $pk->windowid=$packet->windowid;
      $pk->type=$packet->type;
      $pk->slots=$packet->slots;
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->entityId=$packet->entityId;
      break;
      case Info::CONTAINER_CLOSE_PACKET:
      $pk = new ContainerClosePacket();
      $pk->windowid=$packet->windowid;
      break;
      case Info::CONTAINER_SET_SLOT_PACKET:
      $pk = new ContainerSetSlotPacket();
      $pk->windowid=$packet->windowid;
      $pk->slot=$packet->slot;
      $pk->hotbarSlot=$packet->hotbarSlot;
      $pk->item=$packet->item;
      break;
      case Info::CONTAINER_SET_DATA_PACKET:
      $pk = new ContainerSetDataPacket();
      $pk->windowid=$packet->windowid;
      $pk->property=$packet->property;
      $pk->value=$packet->value;
      break;
      case Info::CONTAINER_SET_CONTENT_PACKET:
      $pk = new ContainerSetContentPacket();
      $pk->windowid=$packet->windowid;
      $pk->slots=$packet->slots;
      $pk->hotbar=$packet->hotbar;
      break;
      case Info::CRAFTING_DATA_PACKET:
      $pk = new CraftingDataPacket();
      $pk->entries=$packet->entries;
      $pk->cleanRecipes=$packet->cleanRecipes;
      break;
      case Info::CRAFTING_EVENT_PACKET:
      $pk = new CraftingEventPacket();
      $pk->windowId=$packet->windowId;
      $pk->type=$packet->type;
      $pk->id=$packet->id;
      $pk->input=$packet->input;
      $pk->output=$packet->output;
      break;
      case Info::ADVENTURE_SETTINGS_PACKET:
      $pk = new AdventureSettingsPacket();
      $pk->flags=$packet->flags;
      $pk->userPermission=$packet->userPermission;
      $pk->globalPermission=$packet->globalPermission;
      break;
      case Info::BLOCK_ENTITY_DATA_PACKET:
      $pk=new BlockEntityDataPacket();
      $pk->x=$packet->x;
      $pk->y=$packet->y;
      $pk->z=$packet->z;
      $pk->namedtag=$packet->namedtag;
      break;
      case Info::PLAYER_INPUT_PACKET:
      $pk = new PlayerInputPacket();
      $pk->motX=$packet->motX;
      $pk->motY=$packet->motY;
      $pk->jumping=$packet->jumping;
      $pk->sneaking=$packet->sneaking;
      break;
      case Info::FULL_CHUNK_DATA_PACKET:
      $pk = new FullChunkDataPacket();
      $pk->chunkX=$packet->chunkX;
      $pk->chunkZ=$packet->chunkZ;
      $pk->order=$packet->order;
      $pk->data=$packet->data;
      break;
      case Info::SET_DIFFICULTY_PACKET:
      $pk = new SetDifficultyPacket();
      $pk->difficulty=$packet->difficulty;
      break;
      case Info::CHANGE_DIMENSION_PACKET:
      $pk = new ChangeDimensionPacket();
      $pk->dimension=$packet->dimension;
      break;
      case Info::SET_PLAYER_GAMETYPE_PACKET:
      $pk = new SetPlayerGameTypePacket();
      $pk->gamemode=$packet->gamemode;
      break;
      case Info::PLAYER_LIST_PACKET:
      $pk = new PlayerListPacket();
      $pk->entries=$packet->entries;
      $pk->type=$packet->type;
      break;
      case Info::TELEMETRY_EVENT_PACKET:
      break;
      case Info::SPAWN_EXPERIENCE_ORB_PACKET:
      break;
      case Info::CLIENTBOUND_MAP_ITEM_DATA_PACKET:
      $pk = new ClientboundMapItemDataPacket();
      $pk->mapId=$packet->mapId;
      $pk->type=$packet->type;
      $pk->scale=$packet->scale;
      $pk->width=$packet->width;
      $pk->height=$packet->height;
      $pk->xOffset=$packet->xOffset;
      $pk->yOffset=$packet->yOffset;
      $pk->colors=$packet->colors;
      $pk->isColorArray=$packet->isColorArray;
      break;
      case Info::MAP_INFO_REQUEST_PACKET:
      $pk = new MapInfoRequestPacket();
      $pk->mapId=$packet->mapId;
      break;
      case Info::REQUEST_CHUNK_RADIUS_PACKET:
      $pk = new ChunkRadiusUpdatePacket();
      $pk->radius=$packet->radius;
      break;
      case Info::CHUNK_RADIUS_UPDATED_PACKET:
      $pk = new ChunkRadiusUpdatePacket();
      $pk->radius=$packet->radius;
      break;
      case Info::ITEM_FRAME_DROP_ITEM_PACKET:
      break;
      case Info::REPLACE_SELECTED_ITEM_PACKET:
      break;
      case Info::ADD_ITEM_PACKET:
      break;
    }
    return $pk;
    break;
    default:
    return $packet;
    break;
  }
}
public static function isProtocol(Player $player, string $version){
  if(isset(self::$protocolVersions[$version]) and  in_array($player->getProtocol(), self::$protocolVersions[$version])){
    return true;
  }
  return false;
}
public static function getAcceptedProtocols(){
  $out = [];
  foreach(self::$protocolVersions as $data){
    $out = array_merge($out, $data);
  }
  return $out;
}
public static function getGameVersion(int $protocol){
  foreach(self::$protocolVersions as $version => $protocolVersion){
    if(in_array($protocol, $protocolVersion)){
      return $version;
    }
  }
  return null;
}
}
# I know, it's something not big for you, sorry :v
?>