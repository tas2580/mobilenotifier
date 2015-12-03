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
		//$this->wait_for_server($id);

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
