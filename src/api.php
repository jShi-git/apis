<?php
//header("Content-Type:text/html; charset=utf-8");
require_once ('workflows.php');

class App {
	private static $_instance;
	private $_workflows;

	function __construct() {
		$this->_workflows = new workflows();
	}
	public function getInstance() {
		if (!self::$_instance instanceof self) {
			self::$_instance = new App();
		}
		return self::$_instance;
	}

	public function request($url) {
		return $this->_workflows->request($url);
	}

	public function arrSearch($keyword, $serach_array) {
		$result = array();
		foreach ($serach_array as $key => $s) {
			if (strpos($key, $keyword) !== false) {
				$result[$key] = $serach_array[$key];
			}
		}
		return $result;
	}

	public function getData($keyword) {
		$jsonfile = "apis.json";

		if (!file_exists($jsonfile)) {
			$file = $this->updatejson($jsonfile);
		} else {
			$file = true;
		}

		if ($file) {
			$apidata = json_decode(strtolower(file_get_contents($jsonfile)), true);

			$results = $this->arrSearch(strtolower($keyword), $apidata);

			foreach ($results as $title => $item) {
				$this->_workflows->result(time(), $item, $title, $item, 'icon.png');
			}
			return $this->_workflows->toxml();
		} else {
			return array();
		}

	}

	public function run($query) {
		return $this->getData($query);
	}

	public function showOptions() {
		$this->_workflows->result(time(), $item, "更新数据", "", 'icon.png');

		return $this->_workflows->toxml();
	}

	public function updatejson($jsonfile = "apis.json") {
		$content = file_get_contents("http://tool.oschina.net/apidocs");

		preg_match_all('/<a href="\/(.*)" class="doc_href">(.*)<\/a>/iUs', $content, $api_links);

		$apis = array();

		foreach ($api_links[1] as $k => $v) {
			$apis[$api_links[2][$k]] = "http://tool.oschina.net/" . $v;
		}
		$str = json_encode($apis);

		$file_pointer = fopen($jsonfile, "wb");

		fwrite($file_pointer, urldecode($str));
		fclose($file_pointer);

		return true;
	}
}
?>