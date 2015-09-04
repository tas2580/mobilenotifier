<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* Class, based on Chat-API (https://github.com/WHAnonymous/Chat-API)
*/

namespace tas2580\mobilenotifier\src;

define('WA_SERVER', 's.whatsapp.net');
define('WA_DEVICE', 'S40');
define('WA_VER', '2.12.96');
define('WA_PORT', '80');
//define('WA_PORT', '443');


class whatsapp
{

	/** @var  $challenge_data */
	protected $challenge_data;

	/** @var  $input_key */
	protected $input_key;

	/** @var  $output_key */
	protected $output_key;

	/** @var  $login_status */
	protected $login_status;

	/** @var  $password */
	protected $password;

	/** @var  $phone_number */
	protected $phone_number;

	/** @var  $server_received_id */
	protected $server_received_id;

	/** @var  $socket */
	protected $socket;

	/** @var  $login_time */
	protected $login_time;

	/** @var  $writer */
	protected $writer;

	/** @var  $reader */
	protected $reader;

	/**
	 * Default class constructor.
	 *
	 * @param string 	$phone_number			The user phone number including the country code without '+' or '00'.
	 */
	public function __construct($phone_number)
	{
		$this->writer = new BinTreeNodeWriter();
		$this->reader = new BinTreeNodeReader();
		$this->phone_number = $phone_number;
		$this->login_status =  false;
	}


	/**
	 * Connect (create a socket) to the WhatsApp network.
	 *
	 * @return bool
	 */
	public function connect()
	{
		if ($this->is_connected())
		{
			return true;
		}

		/* Create a TCP/IP socket. */
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket !== false)
		{
			$result = socket_connect($socket, 'e' . rand(1, 16) . '.whatsapp.net', WA_PORT);
			if ($result === false)
			{
				$socket = false;
			}
		}

		if ($socket !== false)
		{
			socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 2, 'usec' => 0));
			socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 2, 'usec' => 0));
			$this->socket = $socket;
			return true;
		}
	}


	/**
	 * Send the nodes to the WhatsApp server to log in.
	 *
	 * @param string 	$password			The user password
	 */
	public function login($password)
	{
		$this->password = $password;
		if ($this->is_connected() && !empty($this->login_status) && $this->login_status === true)
		{
			return true;
		}

		$this->writer->resetKey();
		$this->reader->resetKey();
		$resource = WA_DEVICE . '-' . WA_VER . '-' . WA_PORT;
		$data = $this->writer->StartStream(WA_SERVER, $resource);
		$feat = $this->create_features_node();
		$auth = $this->create_auth_node();
		$this->send_data($data);
		$this->send_node($feat);
		$this->send_node($auth);

		$this->poll_message();
		$this->poll_message();
		$this->poll_message();

		if ($this->challenge_data != null)
		{
			$data = new protocol_node('response', null, null, $this->authenticate());
			$this->send_node($data);
			$this->reader->setKey($this->input_key);
			$this->writer->setKey($this->output_key);
			while (!$this->poll_message())
			{
			}
		}

		if ($this->login_status === false)
		{
			return false;
		}

		$this->send_active_status();
		$this->login_time = time();

		return true;
	}


	/**
	 * Set your profile picture.
	 *
	 * @param string $filepath URL of image
	 */
	public function set_picture($filepath)
	{
		$nodeID = dechex(1);
		$data = $this->create_icon($filepath, 639);
		$preview = $this->create_icon($filepath, 96);
		$picture = new protocol_node('picture', array('type' => 'image'), null, $data);
		$preview = new protocol_node('picture', array('type' => 'preview'), null, $preview);
		$node = new protocol_node('iq', array('id' => $nodeID, 'to' => $this->phone_number . "@" . WA_SERVER, 'type' => 'set', 'xmlns' => 'w:profile:picture'), array($picture, $preview), null);
		$this->send_node($node);
		$this->wait_for_server($nodeID);
	}

	/**
	 * Update the user status.
	 *
	 * @param string $text The text of the message status to send.
	 */
	public function set_status($text)
	{
		$child = new protocol_node('status', null, null, $text);
		$node = new protocol_node('iq', array('to' => WA_SERVER, 'type' => 'set', 'id' => dechex(1), 'xmlns' => 'status'), array($child), null);
		$this->send_node($node);
		$this->wait_for_server();
	}


	/**
	 * Send a text message to the user.
	 *
	 * @param string $to  The recipient.
	 * @param string $text The text message.
	 * @param $id
	 *
	 * @return string     Message ID.
	 */
	public function send($to, $text, $id = null)
	{
		$body_node = new protocol_node('body', null, null, $text);
		$id = $this->send_message_node($to, $body_node, $id);
		$this->wait_for_server($id);

		return $id;
	}



	/**
	 * Add stream features.
	 *
	 * @return protocol_node Return itself.
	 */
	protected function create_features_node()
	{
		$readreceipts = new protocol_node('readreceipts', null, null, null);
		$groupsv2 = new protocol_node('groups_v2', null, null, null);
		$privacy = new protocol_node("privacy", null, null, null);
		$presencev2 = new protocol_node("presence", null, null, null);
		$parent = new protocol_node("stream:features", null, array($readreceipts, $groupsv2, $privacy, $presencev2), null);

		return $parent;
	}


	/**
	 * Add the authentication nodes.
	 *
	 * @return protocol_node Returns an authentication node.
	 */
	protected function create_auth_node()
	{
		$data = null;
		if ($this->challenge_data)
		{
			$key = wa_pbkdf2('sha1', base64_decode($this->password), $this->challenge_data, 16, 20, true);
			$this->input_key = new KeyStream($key[2], $key[3]);
			$this->output_key = new KeyStream($key[0], $key[1]);
			$this->reader->setKey($this->input_key);
			$array = "\0\0\0\0" . $this->phone_number . $this->challenge_data . time();
			$this->challenge_data = null;
			$data = $this->output_key->EncodeMessage($array, 0, strlen($array), false);
		}

		return new protocol_node("auth", array('mechanism' => 'WAUTH-2', 'user' => $this->phone_number), null, $data);
	}


	/**
	 * Send the active status. User will show up as "Online" (as long as socket is connected).
	 */
	public function send_active_status()
	{
		$message_node = new protocol_node("presence", array("type" => "active"), null, "");
		$this->send_node($message_node);
	}

	/**
	 * Send node to the servers.
	 *
	 * @param              $to
	 * @param protocol_node $node
	 * @param null         $id
	 *
	 * @return string            Message ID.
	 */
	protected function send_message_node($to, $node, $id = null)
	{
		$msgId = ($id == null) ? $this->create_msg_id() : $id;
		$messageNode = new protocol_node("message", array('to' => $to . '@' . WA_SERVER, 'type' => 'text', 'id' => $msgId, 't' => time()), array($node), "");
		$this->send_node($messageNode);
		$this->wait_for_server($msgId);
		return $msgId;
	}


	/**
	 * Create a unique msg id.
	 *
	 * @return string
	 *   A message id string.
	 */
	protected function create_msg_id()
	{
		return $this->login_time . '-1';
	}

	/**
	 * Wait for WhatsApp server to acknowledge *it* has received message.
	 * @param string $id The id of the node sent that we are awaiting acknowledgement of.
	 * @param int    $timeout
	 */
	public function wait_for_server($id = 0, $timeout = 5)
	{
		$id = ($id === 0) ? dechex(1) : $id;

		$time = time();
		$this->server_received_id = false;
		do
		{
			$this->poll_message();
		}
		while (($this->server_received_id !== $id) && (time() - $time < $timeout));
	}

	/**
	 * Do we have an active socket connection to WhatsApp?
	 *
	 * @return bool
	 */
	public function is_connected()
	{
		return ($this->socket !== null);
	}

	/**
	 * Disconnect from the WhatsApp network.
	 */
	public function disconnect()
	{
		if (is_resource($this->socket))
		{
			@socket_shutdown($this->socket, 2);
			@socket_close($this->socket);
			$this->socket = null;
			$this->login_status = false;
		}
	}

	/**
	 * Fetch a single message node
	 * @param  bool   $autoReceipt
	 * @param  string $type
	 * @return bool
	 *
	 */
	public function poll_message($autoReceipt = true, $type = "read")
	{
		if (!$this->is_connected())
		{
			return false;
		}

		$r = array($this->socket);
		$w = array();
		$e = array();

		if ((socket_select($r, $w, $e, 2, 0)) && $stanza = $this->read_stanza())
		{
			$this->process_inbound_data($stanza, $autoReceipt, $type);
			return true;
		}
	}



	/**
	 * Authenticate with the WhatsApp Server.
	 *
	 * @return string Returns binary string
	 */
	protected function authenticate()
	{
		$keys = KeyStream::GenerateKeys(base64_decode($this->password), $this->challenge_data);
		$this->input_key = new KeyStream($keys[2], $keys[3]);
		$this->output_key = new KeyStream($keys[0], $keys[1]);
		$array = "\0\0\0\0" . $this->phone_number . $this->challenge_data;
		$response = $this->output_key->EncodeMessage($array, 0, 4, strlen($array) - 4);
		return $response;
	}

	/**
	 * Process inbound data.
	 *
	 * @param      $data
	 * @param bool $autoReceipt
	 * @param      $type
	 *
	 */
	protected function process_inbound_data($data, $autoReceipt = true, $type = 'read')
	{
		$node = $this->reader->nextTree($data);
		if ($node != null)
		{
			$this->server_received_id = $node->getAttribute('id');

			if ($node->getTag() == 'challenge')
			{
				$this->challenge_data = $node->getData();
			}
			else if ($node->getTag() == 'failure')
			{
				$this->login_status =  false;
			}
			else if (($node->getTag() == 'success') && ($node->getAttribute('status') == 'active'))
			{
				$this->login_status = true;
			}
			if ($node->getTag() == 'stream:error')
			{
				$this->disconnect();
			}
		}
	}

	/**
	 * Read 1024 bytes from the whatsapp server.
	 *
	 */
	public function read_stanza()
	{
		$buff = '';
		if ($this->socket != null)
		{
			$header = @socket_read($this->socket, 3);//read stanza header
			if ($header === false)
			{
				socket_close($this->socket);
				$this->socket = null;
			}

			if (strlen($header) == 0)
			{
				return false;
			}
			if (strlen($header) != 3)
			{
				return false;
			}
			$treeLength = (ord($header[0]) & 0x0F) << 16;
			$treeLength |= ord($header[1]) << 8;
			$treeLength |= ord($header[2]) << 0;

			//read full length
			$buff = socket_read($this->socket, $treeLength);
			$len = strlen($buff);

			while (strlen($buff) < $treeLength)
			{
				$toRead = $treeLength - strlen($buff);
				$buff .= socket_read($this->socket, $toRead);
				if ($len == strlen($buff))
				{
					break;
				}
				$len = strlen($buff);
			}

			if (strlen($buff) != $treeLength)
			{
				return;
			}
			$buff = $header . $buff;
		}

		return $buff;
	}

	/**
	 * Send data to the WhatsApp server.
	 * @param string $data
	 *
	 */
	protected function send_data($data)
	{
		if ($this->socket != null)
		{
			if (socket_write($this->socket, $data, strlen($data)) === false)
			{
				$this->disconnect();
			}
		}
	}

	/**
	 * Send node to the WhatsApp server.
	 * @param protocol_node $node
	 * @param bool         $encrypt
	 */
	protected function send_node($node, $encrypt = true)
	{
		$this->send_data($this->writer->write($node, $encrypt));
	}

	/*
	* Resize an image to use it as profile image
	*
	* @param	$file
	* @param	$size
	*/
	private function create_icon($file, $size = 100)
	{
		list($width, $height) = getimagesize($file);
		if ($width > $height)
		{
			$y = 0;
			$x = ($width - $height) / 2;
			$smallestSide = $height;
		}
		else
		{
			$x = 0;
			$y = ($height - $width) / 2;
			$smallestSide = $width;
		}
		$image_p = imagecreatetruecolor($size, $size);
		$image = imagecreatefromstring(file_get_contents($file));
		imagecopyresampled($image_p, $image, 0, 0, $x, $y, $size, $size, $smallestSide, $smallestSide);
		ob_start();
		imagejpeg($image_p);
		$i = ob_get_contents();
		ob_end_clean();
		imagedestroy($image);
		imagedestroy($image_p);
		return $i;
	}



}


function wa_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
{
	$algorithm = strtolower($algorithm);
	if ( !in_array($algorithm, hash_algos(), true))
	{
		die('PBKDF2 ERROR: Invalid hash algorithm.');
	}
	if ($count <= 0 || $key_length <= 0)
	{
		die('PBKDF2 ERROR: Invalid parameters.');
	}

	$hash_length = strlen(hash($algorithm, "", true));
	$block_count = ceil($key_length / $hash_length);

	$output = "";
	for ($i = 1; $i <= $block_count; $i++)
	{
		$last = $salt . pack("N", $i);
		$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
		for ($j = 1; $j < $count; $j++)
		{
			$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
		}
		$output .= $xorsum;
	}

	return ($raw_output) ? substr($output, 0, $key_length) : bin2hex(substr($output, 0, $key_length));
}


class KeyStream
{
	private $rc4;
	private $seq;
	private $macKey;

	public function __construct($key, $macKey)
	{
		$this->rc4    = new rc4($key, 768);
		$this->macKey = $macKey;
	}

	public static function GenerateKeys($password, $nonce)
	{
		$array  = array("key", "key", "key", "key");
		$array2 = array(1, 2, 3, 4);
		$nonce .= '0';
		for ($j = 0; $j < count($array); $j++)
		{
			$nonce[(strlen($nonce) - 1)] = chr($array2[$j]);
			$foo = wa_pbkdf2("sha1", $password, $nonce, 2, 20, true);
			$array[$j] = $foo;
		}
		return $array;
	}

	public function DecodeMessage($buffer, $macOffset, $offset, $length)
	{
		$mac = $this->computeMac($buffer, $offset, $length);
		//validate mac
		for ($i = 0; $i < 4; $i++)
		{
			$foo = ord($buffer[$macOffset + $i]);
			$bar = ord($mac[$i]);
			if ($foo !== $bar)
			{
				return false;
			}
		}
		return $this->rc4->cipher($buffer, $offset, $length);
	}

	public function EncodeMessage($buffer, $macOffset, $offset, $length)
	{
		$data = $this->rc4->cipher($buffer, $offset, $length);
		$mac = $this->computeMac($data, $offset, $length);
		return substr($data, 0, $macOffset) . substr($mac, 0, 4) . substr($data, $macOffset + 4);
	}

	private function computeMac($buffer, $offset, $length)
	{
		$hmac = hash_init("sha1", HASH_HMAC, $this->macKey);
		hash_update($hmac, substr($buffer, $offset, $length));
		$array = chr($this->seq >> 24) . chr($this->seq >> 16) . chr($this->seq >> 8) . chr($this->seq);
		hash_update($hmac, $array);
		$this->seq++;
		return hash_final($hmac, true);
	}
}

class rc4
{
	private $s;
	private $i;
	private $j;

	public function __construct($key, $drop)
	{
		$this->s = range(0, 255);
		for ($i = 0, $j = 0; $i < 256; $i++)
		{
			$k = ord($key{$i % strlen($key)});
			$j = ($j + $k + $this->s[$i]) & 255;
			$this->swap($i, $j);
		}

		$this->i = 0;
		$this->j = 0;
		$this->cipher(range(0, $drop), 0, $drop);
	}

	public function cipher($data, $offset, $length)
	{
		$out = $data;
		for ($n = $length; $n > 0; $n--)
		{
			$this->i = ($this->i + 1) & 0xff;
			$this->j = ($this->j + $this->s[$this->i]) & 0xff;
			$this->swap($this->i, $this->j);
			$d = ord($data{$offset});
			$out[$offset] = chr($d ^ $this->s[($this->s[$this->i] + $this->s[$this->j]) & 0xff]);
			$offset++;
		}

		return $out;
	}

	protected function swap($i, $j)
	{
		$c = $this->s[$i];
		$this->s[$i] = $this->s[$j];
		$this->s[$j] = $c;
	}
}

class TokenMap
{
	private static $primaryStrings = array( "", "", "", "account", "ack", "action", "active", "add", "after", "all", "allow", "apple", "auth", "author", "available", "bad-protocol", "bad-request", "before", "body", "broadcast", "cancel", "category", "challenge", "chat", "clean", "code", "composing", "config", "contacts", "count", "create", "creation", "debug", "default", "delete", "delivery", "delta", "deny", "digest", "dirty", "duplicate", "elapsed", "enable", "encoding", "error", "event", "expiration", "expired", "fail", "failure", "false", "favorites", "feature", "features", "feature-not-implemented", "field", "first", "free", "from", "g.us", "get", "google", "group", "groups", "groups_v2", "http://etherx.jabber.org/streams", "http://jabber.org/protocol/chatstates", "ib", "id", "image", "img", "index", "internal-server-error", "ip", "iq", "item-not-found", "item", "jabber:iq:last", "jabber:iq:privacy", "jabber:x:event", "jid", "kind", "last", "leave", "list", "max", "mechanism", "media", "message_acks", "message", "method", "microsoft", "missing", "modify", "mute", "name", "nokia", "none", "not-acceptable", "not-allowed", "not-authorized", "notification", "notify", "off", "offline", "order", "owner", "owning", "p_o", "p_t", "paid", "participant", "participants", "participating", "paused", "picture", "pin", "ping", "platform", "port", "presence", "preview", "probe", "prop", "props", "query", "raw", "read", "readreceipts", "reason", "receipt", "relay", "remote-server-timeout", "remove", "request", "required", "resource-constraint", "resource", "response", "result", "retry", "rim", "s_o", "s_t", "s.us", "s.whatsapp.net", "seconds", "server-error", "server", "service-unavailable", "set", "show", "silent", "stat", "status", "stream:error", "stream:features", "subject", "subscribe", "success", "sync", "t", "text", "timeout", "timestamp", "to", "true", "type", "unavailable", "unsubscribe", "uri", "url", "urn:ietf:params:xml:ns:xmpp-sasl", "urn:ietf:params:xml:ns:xmpp-stanzas", "urn:ietf:params:xml:ns:xmpp-streams", "urn:xmpp:ping", "urn:xmpp:whatsapp:account", "urn:xmpp:whatsapp:dirty", "urn:xmpp:whatsapp:mms", "urn:xmpp:whatsapp:push", "urn:xmpp:whatsapp", "user", "user-not-found", "value", "version", "w:g", "w:p:r", "w:p", "w:profile:picture", "w", "wait", "WAUTH-2", "xmlns:stream", "xmlns", "1", "chatstate", "crypto", "phash", "enc", "class", "off_cnt", "w:g2", "promote", "demote", "creator", "Bell.caf", "Boing.caf", "Glass.caf", "Harp.caf", "TimePassing.caf", "Tri-tone.caf", "Xylophone.caf", "background", "backoff", "chunked", "context", "full", "in", "interactive", "out", "registration", "sid", "urn:xmpp:whatsapp:sync", "flt", "s16", "u8", "adpcm", "amrnb", "amrwb", "mp3", "pcm", "qcelp", "wma", "h263", "h264", "jpeg");

	private static $secondaryStrings = array( "mpeg4", "wmv", "audio/3gpp", "audio/aac", "audio/amr", "audio/mp4", "audio/mpeg", "audio/ogg", "audio/qcelp", "audio/wav", "audio/webm", "audio/x-caf", "audio/x-ms-wma", "image/gif", "image/jpeg", "image/png", "video/3gpp", "video/avi", "video/mp4", "video/mpeg", "video/quicktime", "video/x-flv", "video/x-ms-asf", "302", "400", "401", "402", "403", "404", "405", "406", "407", "409", "410", "500", "501", "503", "504", "abitrate", "acodec", "app_uptime", "asampfmt", "asampfreq", "audio", "clear", "conflict", "conn_no_nna", "cost", "currency", "duration", "extend", "file", "fps", "g_notify", "g_sound", "gcm", "gone", "google_play", "hash", "height", "invalid", "jid-malformed", "latitude", "lc", "lg", "live", "location", "log", "longitude", "max_groups", "max_participants", "max_subject", "mimetype", "mode", "napi_version", "normalize", "orighash", "origin", "passive", "password", "played", "policy-violation", "pop_mean_time", "pop_plus_minus", "price", "pricing", "redeem", "Replaced by new connection", "resume", "signature", "size", "sound", "source", "system-shutdown", "username", "vbitrate", "vcard", "vcodec", "video", "width", "xml-not-well-formed", "checkmarks", "image_max_edge", "image_max_kbytes", "image_quality", "ka", "ka_grow", "ka_shrink", "newmedia", "library", "caption", "forward", "c0", "c1", "c2", "c3", "clock_skew", "cts", "k0", "k1", "login_rtt", "m_id", "nna_msg_rtt", "nna_no_off_count", "nna_offline_ratio", "nna_push_rtt", "no_nna_con_count", "off_msg_rtt", "on_msg_rtt", "stat_name", "sts", "suspect_conn", "lists", "self", "qr", "web", "w:b", "recipient", "w:stats", "forbidden", "aurora.m4r", "bamboo.m4r", "chord.m4r", "circles.m4r", "complete.m4r", "hello.m4r", "input.m4r", "keys.m4r", "note.m4r", "popcorn.m4r", "pulse.m4r", "synth.m4r", "filehash", "max_list_recipients", "en-AU", "en-GB", "es-MX", "pt-PT", "zh-Hans", "zh-Hant", "relayelection", "relaylatency", "interruption", "Apex.m4r", "Beacon.m4r", "Bulletin.m4r", "By The Seaside.m4r", "Chimes.m4r", "Circuit.m4r", "Constellation.m4r", "Cosmic.m4r", "Crystals.m4r", "Hillside.m4r", "Illuminate.m4r", "Night Owl.m4r", "Opening.m4r", "Playtime.m4r", "Presto.m4r", "Radar.m4r", "Radiate.m4r", "Ripples.m4r", "Sencha.m4r", "Signal.m4r", "Silk.m4r", "Slow Rise.m4r", "Stargaze.m4r", "Summit.m4r", "Twinkle.m4r", "Uplift.m4r", "Waves.m4r", "voip", "eligible", "upgrade", "planned", "current", "future", "disable", "expire", "start", "stop", "accuracy", "speed", "bearing", "recording", "encrypt", "key", "identity", "w:gp2", "admin", "locked", "unlocked", "new", "battery", "archive", "adm", "plaintext_size", "compressed_size", "delivered", "msg", "pkmsg", "everyone", "v", "transport", "call-id");

	public static function TryGetToken($string, &$subdict, &$token)
	{
		$foo = array_search($string, self::$primaryStrings);
		if ($foo)
		{
			$token = $foo;
			return true;
		}
		$foo = array_search($string, self::$secondaryStrings);
		if ($foo)
		{
			$subdict = true;
			$token   = $foo;
			return true;
		}
	}

	public static function GetToken($token, &$subdict, &$string)
	{
		//override subdict
		if (!$subdict && $token >= 236 && $token < (236 + count(self::$secondaryStrings)))
		{
			$subdict = true;
		}

		$tokenMap = ($subdict) ? self::$secondaryStrings : self::$primaryStrings;

		if ($token < 0 || $token > count($tokenMap))
		{
			return false;
		}

		$string = $tokenMap[$token];
		if (!$string)
		{
			return false;
		}
	}

}


class protocol_node
{
	private $tag;
	private $attributeHash;
	private $children;
	private $data;
	private static $cli = null;

	/**
	 * check if call is from command line
	 * @return bool
	 */
	private static function isCli()
	{
		if (self::$cli === null)
		{
			self::$cli = (php_sapi_name() == "cli") ? true : false;
		}
		return self::$cli;
	}

	/**
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * @return string[]
	 */
	public function getAttributes()
	{
		return $this->attributeHash;
	}

	/**
	 * @return protocol_node[]
	 */
	public function getChildren()
	{
		return $this->children;
	}

	public function __construct($tag, $attributeHash, $children, $data)
	{
		$this->tag           = $tag;
		$this->attributeHash = $attributeHash;
		$this->children      = $children;
		$this->data          = $data;
	}

	/**
	 * @param string $indent
	 * @param bool   $isChild
	 * @return string
	 */
	public function nodeString($indent = "", $isChild = false)
	{
		//formatters
		$lt = "<";
		$gt = ">";
		$nl = "\n";
		if (!self::isCli())
		{
			$lt = "&lt;";
			$gt = "&gt;";
			$nl = "<br />";
			$indent = str_replace(" ", "&nbsp;", $indent);
		}

		$ret = $indent . $lt . $this->tag;
		if ($this->attributeHash != null)
		{
			foreach ($this->attributeHash as $key => $value)
			{
				$ret .= " " . $key . "=\"" . $value . "\"";
			}
		}
		$ret .= $gt;
		if (strlen($this->data) > 0)
		{
			$ret .= (strlen($this->data) <= 1024) ? $this->data : " " . strlen($this->data) . " byte data";
		}
		if ($this->children)
		{
			$ret .= $nl;
			$foo = array();
			foreach ($this->children as $child)
			{
				$foo[] = $child->nodeString($indent . "  ", true);
			}
			$ret .= implode($nl, $foo) . $nl . $indent;
		}
		$ret .= $lt . "/" . $this->tag . $gt;

		if (!$isChild)
		{
			$ret .= $nl;
			if (!self::isCli())
			{
				$ret .= $nl;
			}
		}

		return $ret;
	}

	/**
	 * @param $attribute
	 * @return string
	 */
	public function getAttribute($attribute)
	{
		return (isset($this->attributeHash[$attribute])) ? $this->attributeHash[$attribute] : '';
	}


	//get children supports string tag or int index
	/**
	 * @param $tag
	 * @return protocol_node
	 */
	public function getChild($tag)
	{
		$ret = null;
		if ($this->children)
		{
			if (is_int($tag))
			{
				return (isset($this->children[$tag])) ? $this->children[$tag] : null;
			}
			foreach ($this->children as $child)
			{
				if (strcmp($child->tag, $tag) == 0)
				{
					return $child;
				}
				$ret = $child->getChild($tag);
				if ($ret)
				{
					return $ret;
				}
			}
		}

		return null;
	}

}

class BinTreeNodeReader
{
	private $input;
	/** @var $key KeyStream */
	private $key;

	public function resetKey()
	{
		$this->key = null;
	}

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function nextTree($input = null)
	{
		if ($input != null)
		{
			$this->input = $input;
		}
		$firstByte  = $this->peekInt8();
		$stanzaFlag = ($firstByte & 0xF0) >> 4;
		$stanzaSize = $this->peekInt16(1) | (($firstByte & 0x0F) << 16);
		if ($stanzaSize > strlen($this->input))
		{
			return false;
		}
		$this->readInt24();
		if (($stanzaFlag & 8) && isset($this->key))
		{
			$realSize = $stanzaSize - 4;
			$this->input = $this->key->DecodeMessage($this->input, $realSize, 0, $realSize);// . $remainingData;
		}
		if ($stanzaSize > 0)
		{
			return $this->nextTreeInternal();
		}

		return null;
	}

	protected function readNibble()
	{
		$byte = $this->readInt8();

		$ignoreLastNibble = (bool) ($byte & 0x80);
		$size = ($byte & 0x7f);
		$nrOfNibbles = $size * 2 - (int) $ignoreLastNibble;

		$data = $this->fillArray($size);
		$string = '';

		for ($i = 0; $i < $nrOfNibbles; $i++)
		{
			$byte = $data[(int) floor($i / 2)];
			$ord = ord($byte);

			$shift = 4 * (1 - $i % 2);
			$decimal = ($ord & (15 << $shift)) >> $shift;

			switch ($decimal)
			{
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
					$string .= $decimal;
					break;
				case 10:
				case 11:
					$string .= chr($decimal - 10 + 45);
					break;
				default:
					return false;
			}
		}

		return $string;
	}

	protected function getToken($token)
	{
		$ret     = "";
		$subdict = false;
		TokenMap::GetToken($token, $subdict, $ret);
		if (!$ret)
		{
			$token = $this->readInt8();
			TokenMap::GetToken($token, $subdict, $ret);
			if (!$ret)
			{
				return false;
			}
		}

		return $ret;
	}

	protected function readString($token)
	{
		$ret = "";

		if ($token == -1)
		{
			return false;
		}

		if (($token > 2) && ($token < 0xf5))
		{
			$ret = $this->getToken($token);
		}
		else if ($token == 0)
		{
			$ret = "";
		}
		else if ($token == 0xfc)
		{
			$size = $this->readInt8();
			$ret  = $this->fillArray($size);
		}
		else if ($token == 0xfd)
		{
			$size = $this->readInt24();
			$ret  = $this->fillArray($size);
		}
		else if ($token == 0xfa)
		{
			$user   = $this->readString($this->readInt8());
			$server = $this->readString($this->readInt8());
			if ((strlen($user) > 0) && (strlen($server) > 0))
			{
				$ret = $user . "@" . $server;
			}
			else if (strlen($server) > 0)
			{
				$ret = $server;
			}
		}
		else if ($token == 0xff)
		{
			$ret = $this->readNibble();
		}

		return $ret;
	}

	protected function readAttributes($size)
	{
		$attributes  = array();
		$attribCount = ($size - 2 + $size % 2) / 2;

		for ($i = 0; $i < $attribCount; $i++)
		{
			$key = $this->readString($this->readInt8());
			$value = $this->readString($this->readInt8());
			$attributes[$key] = $value;
		}

		return $attributes;
	}

	protected function nextTreeInternal()
	{
		$token = $this->readInt8();
		$size  = $this->readListSize($token);
		$token = $this->readInt8();
		if ($token == 1)
		{
			$attributes = $this->readAttributes($size);
			return new protocol_node("start", $attributes, null, "");
		}
		else if ($token == 2)
		{
			return null;
		}
		$tag = $this->readString($token);
		$attributes = $this->readAttributes($size);
		if (($size % 2) == 1)
		{
			return new protocol_node($tag, $attributes, null, "");
		}
		$token = $this->readInt8();
		if ($this->isListTag($token))
		{
			return new protocol_node($tag, $attributes, $this->readList($token), "");
		}

		return new protocol_node($tag, $attributes, null, $this->readString($token));
	}

	protected function isListTag($token)
	{
		return ($token == 248 || $token == 0 || $token == 249);
	}

	protected function readList($token)
	{
		$size = $this->readListSize($token);
		$ret = array();
		for ($i = 0; $i < $size; $i++)
		{
			array_push($ret, $this->nextTreeInternal());
		}
		return $ret;
	}

	protected function readListSize($token)
	{
		if ($token == 0xf8)
		{
			return $this->readInt8();
		}
		else if ($token == 0xf9)
		{
			return $this->readInt16();
		}
	}

	protected function peekInt24($offset = 0)
	{
		$ret = 0;
		if (strlen($this->input) >= (3 + $offset))
		{
			$ret = ord(substr($this->input, $offset, 1)) << 16;
			$ret |= ord(substr($this->input, $offset + 1, 1)) << 8;
			$ret |= ord(substr($this->input, $offset + 2, 1)) << 0;
		}
		return $ret;
	}

	protected function readInt24()
	{
		$ret = $this->peekInt24();
		if (strlen($this->input) >= 3)
		{
			$this->input = substr($this->input, 3);
		}
		return $ret;
	}

	protected function peekInt16($offset = 0)
	{
		$ret = 0;
		if (strlen($this->input) >= (2 + $offset))
		{
			$ret = ord(substr($this->input, $offset, 1)) << 8;
			$ret |= ord(substr($this->input, $offset + 1, 1)) << 0;
		}
		return $ret;
	}

	protected function readInt16()
	{
		$ret = $this->peekInt16();
		if ($ret > 0)
		{
			$this->input = substr($this->input, 2);
		}
		return $ret;
	}

	protected function peekInt8($offset = 0)
	{
		$ret = 0;
		if (strlen($this->input) >= (1 + $offset))
		{
			$sbstr = substr($this->input, $offset, 1);
			$ret = ord($sbstr);
		}
		return $ret;
	}

	protected function readInt8()
	{
		$ret = $this->peekInt8();
		if (strlen($this->input) >= 1)
		{
			$this->input = substr($this->input, 1);
		}
		return $ret;
	}

	protected function fillArray($len)
	{
		$ret = "";
		if (strlen($this->input) >= $len)
		{
			$ret = substr($this->input, 0, $len);
			$this->input = substr($this->input, $len);
		}
		return $ret;
	}
}


class BinTreeNodeWriter
{
	private $output;
	private $key;

	public function resetKey()
	{
		$this->key = null;
	}

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function StartStream($domain, $resource)
	{
		$attributes = array();

		$attributes["to"]       = $domain;
		$attributes["resource"] = $resource;
		$this->writeListStart(count($attributes) * 2 + 1);

		$this->output .= "\x01";
		$this->writeAttributes($attributes);

		return "WA" . $this->writeInt8(1) . $this->writeInt8(5) . $this->flushBuffer();
	}

	/**
	 * @param protocol_node $node
	 * @param bool         $encrypt
	 *
	 * @return string
	 */
	public function write($node, $encrypt = true)
	{
		if ($node == null)
		{
			$this->output .= "\x00";
		}
		else
		{
			$this->writeInternal($node);
		}

		return $this->flushBuffer($encrypt);
	}

	/**
	 * @param protocol_node $node
	 */
	protected function writeInternal($node)
	{
		$len = 1;
		if ($node->getAttributes() != null)
		{
			$len += count($node->getAttributes()) * 2;
		}
		if (count($node->getChildren()) > 0)
		{
			$len += 1;
		}
		if (strlen($node->getData()) > 0)
		{
			$len += 1;
		}
		$this->writeListStart($len);
		$this->writeString($node->getTag());
		$this->writeAttributes($node->getAttributes());
		if (strlen($node->getData()) > 0)
		{
			$this->writeBytes($node->getData());
		}
		if ($node->getChildren())
		{
			$this->writeListStart(count($node->getChildren()));
			foreach ($node->getChildren() as $child)
			{
				$this->writeInternal($child);
			}
		}
	}

	protected function parseInt24($data)
	{
		$ret = ord(substr($data, 0, 1)) << 16;
		$ret |= ord(substr($data, 1, 1)) << 8;
		$ret |= ord(substr($data, 2, 1)) << 0;
		return $ret;
	}

	protected function flushBuffer($encrypt = true)
	{
		$size = strlen($this->output);
		$data = $this->output;
		if ($this->key != null && $encrypt)
		{
			$bsize = $this->getInt24($size);
			//encrypt
			$data = $this->key->EncodeMessage($data, $size, 0, $size);
			$len = strlen($data);
			$bsize[0] = chr((8 << 4) | (($len & 16711680) >> 16));
			$bsize[1] = chr(($len & 65280) >> 8);
			$bsize[2] = chr($len & 255);
			$size = $this->parseInt24($bsize);
		}
		$ret = $this->writeInt24($size) . $data;
		$this->output = '';
		return $ret;
	}

	protected function getInt24($length)
	{
		return chr((($length & 0xf0000) >> 16)) . chr((($length & 0xff00) >> 8)) . chr(($length & 0xff));
	}

	protected function writeToken($token)
	{
		if ($token < 0xf5)
		{
			$this->output .= chr($token);
		}
		else if ($token <= 0x1f4)
		{
			$this->output .= "\xfe" . chr($token - 0xf5);
		}
	}

	protected function writeJid($user, $server)
	{
		$this->output .= "\xfa";
		if (strlen($user) > 0)
		{
			$this->writeString($user);
		}
		else
		{
			$this->writeToken(0);
		}
		$this->writeString($server);
	}

	protected function writeInt8($v)
	{
		return chr($v & 0xff);
	}

	protected function writeInt16($v)
	{
		return chr(($v & 0xff00) >> 8) . chr(($v & 0x00ff) >> 0);
	}

	protected function writeInt24($v)
	{
		return chr(($v & 0xff0000) >> 16) . chr(($v & 0x00ff00) >> 8) . chr(($v & 0x0000ff) >> 0);
	}

	protected function writeBytes($bytes)
	{
		$len = strlen($bytes);
		$this->output .= ($len >= 0x100) ? "\xfd" . $this->writeInt24($len) : "\xfc" . $this->writeInt8($len);
		$this->output .= $bytes;
	}

	protected function writeString($tag)
	{
		$intVal  = -1;
		$subdict = false;
		if (TokenMap::TryGetToken($tag, $subdict, $intVal))
		{
			if ($subdict)
			{
				$this->writeToken(236);
			}
			$this->writeToken($intVal);
			return;
		}
		$index = strpos($tag, '@');
		if ($index)
		{
			$server = substr($tag, $index + 1);
			$user = substr($tag, 0, $index);
			$this->writeJid($user, $server);
		}
		else
		{
			$this->writeBytes($tag);
		}
	}

	protected function writeAttributes($attributes)
	{
		if ($attributes)
		{
			foreach ($attributes as $key => $value)
			{
				$this->writeString($key);
				$this->writeString($value);
			}
		}
	}

	protected function writeListStart($len)
	{
		if ($len == 0)
		{
			$this->output .= "\x00";
		}
		else if ($len < 256)
		{
			$this->output .= "\xf8" . chr($len);
		}
		else
		{
			$this->output .= "\xf9" . $this->writeInt16($len);
		}
	}
}
