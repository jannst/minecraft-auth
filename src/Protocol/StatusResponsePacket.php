<?php
namespace PublicUHC\MinecraftAuth\Protocol;

use PublicUHC\MinecraftAuth\ReactServer\DataTypes\VarInt;

class StatusResponsePacket {

    const PACKET_ID = 0;

    private $version = '0';
    private $protocol = 0;
    private $max_players = 0;
    private $online_count = 0;
    private $online_players = [];
    private $description = 'A Minecraft Server';
    private $favicon = null;

    /**
     * @return string the name of the Minecraft version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version the name of the Minecraft version
     * @return $this;
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return int the protocol number
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param int $protocol the protocol number
     * @return $this;
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * @return int the max players to show
     */
    public function getMaxPlayers()
    {
        return $this->max_players;
    }

    /**
     * @param int $max_players the max players to show
     * @return $this
     */
    public function setMaxPlayers($max_players)
    {
        $this->max_players = $max_players;
        return $this;
    }

    /**
     * @return int the online amount to show
     */
    public function getOnlineCount()
    {
        return $this->online_count;
    }

    /**
     * @param int $online_count the online amount to show
     * @return $this;
     */
    public function setOnlineCount($online_count)
    {
        $this->online_count = $online_count;
        return $this;
    }

    /**
     * @return array list of online player names
     */
    public function getOnlinePlayers()
    {
        return $this->online_players;
    }

    /**
     * @param array $online_players list of online player names
     * @return $this
     */
    public function setOnlinePlayers($online_players)
    {
        $this->online_players = $online_players;
    }

    /**
     * @return string the server description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description the server description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return String the favicon base64encoded image
     */
    public function getFavicon()
    {
        return $this->favicon;
    }

    /**
     * @param String $favicon the favicon base64encoded image
     * @return $this
     */
    public function setFavicon($favicon)
    {
        $this->favicon = $favicon;
        return $this;
    }

    public function encode()
    {
        $payload = [
            'version' => [
                'name'      => $this->version,
                'protocol'  => $this->protocol
            ],
            'players' => [
                'max'       => $this->max_players,
                'online'    => $this->online_count,
                'sample'    => []
            ],
            'description'   => [
                'text'  => $this->description
            ],
        ];
        if($this->favicon != null) {
            $payload['favicon'] = $this->favicon;
        }
        foreach($this->online_players as $player) {
            array_push($payload['players']['sample'], [
                'name'  => $player,
                'id'    => ''
            ]);
        }

        $jsonString = utf8_encode(json_encode($payload));
        $jsonStringLen = strlen($jsonString);
        echo " <- STATUS RESPONSE - JSON UTF8 DATA (LEN - $jsonStringLen: $jsonString\n";
        echo " <- STATUS RESPONSE - JSON UTF8 DATA (HEX): 0x".bin2hex($jsonString)."\n";

        $jsonStringLengthVarInt = VarInt::writeUnsignedVarInt($jsonStringLen);
        echo " <- STATUS RESPONSE - STRING LENGTH (O): ".$jsonStringLengthVarInt->getValue()."\n";
        echo " <- STATUS RESPONSE - STRING LENGTH (E): 0x".bin2hex($jsonStringLengthVarInt->getEncoded())."\n";

        $jsonObjectEncoded = $jsonStringLengthVarInt->getEncoded() . $jsonString;
        echo " <- ENCODED JSON OBJECT (RAW): ".$jsonObjectEncoded."\n";
        echo " <- ENCODED JSON OBJECT (HEX): 0x".bin2hex($jsonObjectEncoded)."\n";

        $packetIDVarInt = VarInt::writeUnsignedVarInt(self::PACKET_ID);
        echo " <- STATUS RESPONSE - PACKET ID: ".self::PACKET_ID."\n";
        echo " <- STATUS RESPONSE - PACKET ID VARINT (O): ".$packetIDVarInt->getValue()."\n";
        echo " <- STATUS RESPONSE - PACKET ID VARINT (E): 0x".bin2hex($packetIDVarInt->getEncoded())."\n";

        $packetLengthVarInt = VarInt::writeUnsignedVarInt($packetIDVarInt->getDataLength() + strlen($jsonObjectEncoded));
        echo " <- STATUS RESPONSE - PACKET LENGTH: ".($packetIDVarInt->getDataLength() + strlen($jsonObjectEncoded))."\n";
        echo " <- STATUS RESPONSE - PACKET LENGTH (O): ".$packetLengthVarInt->getValue()."\n";
        echo " <- STATUS RESPONSE - PACKET LENGTH (E): 0x".bin2hex($packetLengthVarInt->getEncoded())."\n";

        $encoded = $packetLengthVarInt->getEncoded() . $packetIDVarInt->getEncoded() . $jsonObjectEncoded;
        echo 'ENCODED RESPONSE (HEX): 0x' . bin2hex($encoded) . "\n";
        echo "ENCODED RESPONSE (RAW): $encoded\n";
        return $encoded;
    }
} 