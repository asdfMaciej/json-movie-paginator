<html>
	<head>
		<meta charset="UTF-8">
		<title>Filmy</title>
		<script>
		window.addEventListener('load', function () {
			let filter = document.querySelector("#submit");
			let collection = ["title", "genre", "year", "author"];
			for (let key of collection) {
				let sort = document.querySelector("#sort_"+key);
				sort.addEventListener("click", ev => {
					var url = new URL(window.location.href);
					if (url.searchParams.get('sort') == key) {
						if (url.searchParams.has('reverse')) {
							url.searchParams.delete('reverse');
						} else {
							url.searchParams.set('reverse', 'true');
						}	
					}
					url.searchParams.set('sort', key);
					url.searchParams.set('page', 0);
					window.open(url.toString(), '_self');
				});
			}

			filter.addEventListener("click", ev => {
				let filter = {};

				for (let key of collection) {
					let input = document.querySelector("#"+key);
					let value = input.value;
					if (value)
						filter[key] = value;
				}

				filter["page"] = 0;
				let postfix = new URLSearchParams(filter).toString();
				window.open('?'+postfix,'_self');
			});
		});
		</script>
	</head>
	
	<body>
		<div>
			<input type="text" id="title" placeholder="Tytuł">
			<input type="text" id="genre" placeholder="Gatunek">
			<input type="text" id="year" placeholder="Rok">
			<input type="text" id="author" placeholder="Autor">
			<button id="submit">Filtruj</button>
		</div>
		<div>
			<button id="sort_title">Sortuj po tytule</button>
			<button id="sort_genre">Sortuj po gatunku</button>
			<button id="sort_year">Sortuj po roku</button>
			<button id="sort_author">Sortuj po autorze</button>
		</div>
		<?php
		require __DIR__ . "/internals/database.php";
		const moviesPerPage = 10;

		$filter = new MovieFilter();
		$d = new Database();

		$currentPage = intval($_GET["page"]) ?? 0;
		$sortKey = $_GET["sort"] ?? "title";
		$sortReverse = $_GET["reverse"] ?? false;

		$filterKeys = ["genre", "author", "year", "title"]; 
		foreach ($filterKeys as $key)
			if ($value = $_GET[$key] ?? "")
				$filter->addFilter($key, $value);


		$unpaginated = $d->getMovies($filter);
		$movieCount = count($unpaginated);
		$pages = ceil($movieCount / moviesPerPage);


		$paginationStart = $currentPage * moviesPerPage;

		$filter->setPaginationStart($paginationStart);
		$filter->setMaxAmount(moviesPerPage);
		$filter->setSort($sortKey, $sortReverse);
		$movies = $d->getMovies($filter);

		foreach ($movies as $n => $movie) {
			extract($movie);
			echo "
			<b>$n</b>, <u>$title</u> ($genre), <i>$year</i>, autor: $author<br>
			";
		}

		for ($n = 0; $n < $pages; $n++) {
			$_GET["page"] = $n;
			$getPostfix = http_build_query($_GET);
			if ($n == $currentPage) echo "<b>";
			echo "
			<a href='?$getPostfix'>$n</a>
			";
			if ($n == $currentPage) echo "</b>";
		}
		echo "<br>";
		?>
	</body>
</html>