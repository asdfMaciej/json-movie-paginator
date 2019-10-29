<?php
function contains($haystack, $needle) {
	$haystack = strtolower($haystack);
	$needle = strtolower($needle);
    return strpos($haystack, $needle) !== false || $needle == $haystack;
}


class MovieFilter {
	protected $paginateFrom;
	protected $filter;
	protected $amount;
	protected $sortKey;
	protected $sortReverse;

	public function __construct() {
		$this->paginateFrom = 0;
		$this->filter = [];
		$this->amount = 0;
		$this->sortKey = null;
		$this->sortReverse = false;
	}

	public function setPaginationStart($from) {
		$this->paginateFrom = $from;
		return $this;
	}

	public function addFilter($key, $value) {
		$this->filter[$key] = $value;
		return $this;
	}

	public function setMaxAmount($amount) {
		$this->amount = $amount;
		return $this;
	}

	public function setSort($key, $reverse=False) {
		$this->sortKey = $key;
		$this->sortReverse = $reverse;
		return $this;
	}

	public function apply($data) {
		if ($this->filter) {
			foreach ($data as $index => $movie) {
				foreach ($this->filter as $key => $desiredValue) {
					if (!array_key_exists($key, $movie) || !contains($movie[$key], $desiredValue)) {
						unset($data[$index]);
					}
				}
			}
		}

		if ($this->sortKey)
			usort($data, function($a, $b) {
				return $a[$this->sortKey] <=> $b[$this->sortKey];
			});
		
		if ($this->sortReverse)
			$data = array_reverse($data);

		if (!$this->amount)
			$this->amount = count($data);
		
		$data = array_slice($data, $this->paginateFrom, $this->amount);

		return $data;
	}
}

class Database {
	protected $data;
	protected $path =  __DIR__ . "/../data/movies.json";

	public function __construct() {
		$this->data = $this->openJson($this->path);
	}

	public function getMovies($filter = null) {
		if (!is_null($filter))
			return $filter->apply($this->data);

		return $this->data;
	}

	protected function openJson($filename) {
		return json_decode(file_get_contents($filename), true);
	}
}
?>