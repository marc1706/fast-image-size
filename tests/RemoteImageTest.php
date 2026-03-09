<?php

/**
 * fast-image-size remote image test class
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\tests;

use PHPUnit\Framework\TestCase;

class RemoteImageTest extends TestCase
{
	private static $server_process;
	private static $server_url;
	private static $router_file = 'test_router.php';

	/** @var \FastImageSize\FastImageSize */
	protected $imageSize;

	public static function setUpBeforeClass(): void
	{
		$code = '<?php 
			$path = $_SERVER["REQUEST_URI"];
			if ($path === "/200") { header("HTTP/1.1 200 OK"); echo "data"; }
			if ($path === "/403") { header("HTTP/1.1 403 Forbidden"); echo "error"; }
			if ($path === "/redirect") { header("Location: /200", true, 301); }
		';
		file_put_contents(self::$router_file, $code);

		$descriptors = [
			0 => ["pipe", "r"], // stdin
			1 => ["pipe", "w"], // stdout
			2 => ["pipe", "w"]  // stderr
		];

		// Find a free port manually
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_bind($socket, '127.0.0.1', 0);
		socket_getsockname($socket, $addr, $port);
		socket_close($socket);

		self::$server_process = proc_open(
			'php -S 127.0.0.1:' . (int) $port . ' ' . self::$router_file,
			$descriptors,
			$pipes
		);

		// Read the first line of stderr to find the assigned port
		self::$server_url = "http://127.0.0.1:" . $port;

		// Close pipes we don't need to prevent hanging
		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);

		usleep(100000);
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$server_process)
		{
			proc_terminate(self::$server_process);
			@unlink(self::$router_file);
		}
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->imageSize = new \FastImageSize\FastImageSize();
	}

	private function get_final_content($path)
	{
		$context = stream_context_create([
			'http' => [
				//'ignore_errors' => true,
				'timeout' => 2,
				'follow_location' => 1
			]
		]);

		$content = @file_get_contents($path, false, $context);

		if (!isset($http_response_header))
		{
			return $content;
		}

		$status_line = '';
		foreach (array_reverse($http_response_header) as $header)
		{
			if (strpos($header, 'HTTP/') === 0)
			{
				$status_line = $header;
				break;
			}
		}

		if (strpos($status_line, ' 200 ') !== false)
		{
			return $content;
		}

		return false;
	}

	public function test_returns_content_on_200_ok()
	{
		$result = $this->imageSize->getImage(self::$server_url . '/200', 0, 4, false);
		$this->assertEquals("data", $result);
	}

	public function test_returns_false_on_403_forbidden()
	{
		$result = $this->imageSize->getImage(self::$server_url . '/403', 0, 5, false);
		$this->assertFalse($result);
	}

	public function test_returns_content_after_successful_redirect()
	{
		$result = $this->imageSize->getImage(self::$server_url . '/redirect', 0, 4, false);
		$this->assertEquals("data", $result);
	}

	public function test_returns_false_for_missing_local_file()
	{
		// Testing local file failure (Local "403/404")
		$result = $this->get_final_content("/tmp/this_file_does_not_exist_123.txt");
		$result = $this->imageSize->getImage('/tmp/this_file_does_not_exist_123.txt', 0, 4, false);
		$this->assertFalse($result);
	}
}
