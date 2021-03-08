<?php  
/*Get values for fiters,sort dropdown and page number*/
$currentPage 	= (isset($_GET['page']))? $_GET['page'] : 1 ;
$sortFilter 	= (isset($_GET['sortFilter']))? $_GET['sortFilter'] : "" ;
$generFiler 	= (isset($_GET['generFiler']))? $_GET['generFiler'] : "" ;
$languageFilter = (isset($_GET['languageFilter']))? $_GET['languageFilter'] : "" ;
?>
<div class="container">
	<h1 class="text-center">Movie List</h1>
	<div class="row">
		<div class="col-sm-3 col-md-6 col-lg-4" >
			<div class="form-group">
			<label for="sel1">Filter by Language</label>
			<select class="form-control" id="languageFilter" onChange ="showPage();">
				<option value="">All</option>
				<?php 	
				/*Get the values for Language dropdown*/
				$stmt = $conn->prepare("SELECT id, value FROM category WHERE type='Language'");
				$stmt->execute();
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				while($row = $stmt->fetchAll()) 
				{
					foreach ($row as $k=>$v)
					{
						echo '<option '.(($languageFilter == $v['id'])? 'selected ' : '').' value="'.$v['id'].'">'.$v['value'].'</option>';
					}
				}
				?>
			</select>
			</div>
		</div>
		<div class="col-sm-3 col-md-6 col-lg-4" >
			<div class="form-group">
			<label for="sel1">Filter by Genre</label>
			<select class="form-control" id="generFiler" onChange ="showPage();">
				<option value="">All</option>
				<?php
				/*Get the values for Genre dropdown*/
				$stmt = $conn->prepare("SELECT id, value FROM category WHERE type='Genre'");
				$stmt->execute();
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				while($row = $stmt->fetchAll()) 
				{
					foreach ($row as $k=>$v)
					{
						echo '<option '.(($generFiler == $v['id'])? 'selected ' : '').' value="'.$v['id'].'">'.$v['value'].'</option>';
					}
				}
				?>
			</select>
			</div>
		</div>
		<div class="col-sm-3 col-md-6 col-lg-4" >
			<div class="form-group">
			  <label for="sel1">Sort by</label>
			  <select class="form-control" id="sortFilter" onChange ="showPage(<?php echo $currentPage; ?>);">
				<option <?php if($sortFilter == 'releaseDate') echo 'selected '; ?> value="releaseDate">Release Date</option>
				<option <?php if($sortFilter == 'length') echo 'selected '; ?> value="length">Length</option>
			  </select>
			</div>
		</div>
	</div>
<?php
$moviePerPage = 10;
/*Create sql to get total number of movies with selected filter*/
$sql= "SELECT m.id, m.title, m.description, m.length, DATE_FORMAT(m.releaseDate, '%M %d %Y') as releaseDate, m.featuredImage FROM movies m ";
if($languageFilter !="")
{
   $sql .=" INNER JOIN relationship r1 ON (r1.movieId = m.id AND r1.categoryId =:in_categoryId1) ";
}
if($generFiler !="")
{
  $sql .=" INNER JOIN relationship r2 ON (r2.movieId = m.id AND r2.categoryId =:in_categoryId2) ";
}

$stmt = $conn->prepare($sql);
if($languageFilter !="")
{
	$stmt->bindParam(':in_categoryId1', $languageFilter, PDO::PARAM_INT);
}
if($generFiler !="")
{
	$stmt->bindParam(':in_categoryId2', $generFiler, PDO::PARAM_INT);
}
$stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$row = $stmt->fetchAll();
$totalMovies = count($row);

/*Calculate total pages and offset for sql query for cuurent page*/
$pages = ceil($totalMovies / $moviePerPage);
$offset = ($currentPage - 1)  * $moviePerPage;   
$orederBy = ($sortFilter =='length') ? 'm.length ASC' : 'm.releaseDate DESC';
$sql .=" ORDER BY ".$orederBy;
$sql .=" LIMIT :in_offset, :in_moviePerPage";
$stmt = $conn->prepare($sql);  
$stmt->bindParam(':in_offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':in_moviePerPage', $moviePerPage, PDO::PARAM_INT);
if($languageFilter !="")
{
	$stmt->bindParam(':in_categoryId1', $languageFilter, PDO::PARAM_INT);
}
if($generFiler !="")
{
	$stmt->bindParam(':in_categoryId2', $generFiler, PDO::PARAM_INT);
}
$stmt->execute();

$stmt->setFetchMode(PDO::FETCH_ASSOC);

if($totalMovies < 1 || $currentPage > $pages)
{
	echo '<div><p class="text-center"> No movies found</p><div>';
	exit;
}
/*Loop thoogh output of sql and create final array with all movie details*/
while($row = $stmt->fetchAll()) 
{
	foreach ($row as $k => $v)
	{
		$id = $v['id'];
		$movieDeatil[$id]['title'] = $v['title'];
		$movieDeatil[$id]['description'] = $v['description'];
		$movieDeatil[$id]['length'] = $v['length'];
		$movieDeatil[$id]['releaseDate'] = $v['releaseDate'];
		$movieDeatil[$id]['featuredImage'] = $v['featuredImage'];

		$sql= "SELECT c.type,c.value  
		FROM  relationship r 
		INNER JOIN category c ON ( c.id = r.categoryId )
		WHERE r.movieId = :in_movieId";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':in_movieId', $id, PDO::PARAM_INT);	
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$category = $stmt->fetchAll();

		foreach ($category as $ck => $cv)
		{
			$movieDeatil[$id][$cv['type']] = $cv['value'];
		}
	}
}

foreach ($movieDeatil as $id => $v)
{
?>
	<div class="row shadow p-1 mb-5 bg-white rounded">
		<div class="col-sm-3 col-md-6 col-lg-3" >
			<img src="/images/<?php echo $v['featuredImage']; ?>" class="img-thumbnail" alt="" width ="100%">
		</div>		  
		<div class="col-sm-3 col-md-6 col-lg-9" >
			<p><b>Title:</b> <?php echo $v['title']; ?></p>
			<p><b>Description:</b> <?php echo $v['description']; ?></p>
			<p><b>Movie length:</b> <?php echo $v['length']; ?> minutes</p>
			<p><b>Release date:</b> <?php echo $v['releaseDate']; ?></p>
			<p><b>Language:</b> <?php echo $v['Language']; ?></p>
			<p><b>Genre:</b> <?php echo $v['Genre']; ?></p>
		</div>
	</div>	
<?php
}
/*Show pagination if there are mutiple pages*/
if ($pages > 1)
{
?>	
	<div class="row">
		<div class="col-sm-3 col-md-6 col-lg-4"></div>
		<div class="col-sm-3 col-md-6 col-lg-4">
			<nav aria-label="...">
				<ul class="pagination">
					<?php 
					/*Show previous page buttons only if current page is greater than 1 */
					if($currentPage > 1)
					{
						?>
						<li class="page-item">
						<a class="page-link" onclick ="showPage(1);" ><<</a>
						</li>		
						<li class="page-item">
						<a class="page-link" onclick ="showPage(<?php echo ($currentPage -1); ?>);" ><</a>
						</li>
						<?php
					}
					
					/*Show Max 5 pages buttons */
					$pagesShowUpTo = ($currentPage < 4) ? (($pages < 4 )? $pages : 4) : $currentPage;
					$startFrom  = ($pagesShowUpTo -3) > 0 ? ($pagesShowUpTo -3) : 1 ;
					for($i= $startFrom; $i <= $pagesShowUpTo; $i++)
					{
						?> 
						<li class="page-item <?php if($startFrom == $currentPage)  echo 'active'; ?>">
							<a class="page-link" onclick ="showPage(<?php echo $startFrom; ?>);"><?php echo $startFrom; ?></a>
						</li>
						<?php
						$startFrom ++;
					}
					?>
					<?php 
					/*Show next page buttons only if current page is not last page */
					if($currentPage < $pages)
					{
						?>
						<li class="page-item">
							<a class="page-link" onclick ="showPage(<?php echo ($currentPage + 1); ?>);">></a>
						</li>
						<li class="page-item">
							<a class="page-link" onclick ="showPage(<?php echo ($pages); ?>);">>></a>
						</li>
						<?php
					}
					?>
				</ul>
			</nav>
		</div>
		<div class="col-sm-3 col-md-6 col-lg-4" ></div>
	</div>
<?php
}
?>
	<div><p class="text-center"> page <?php echo $currentPage; ?> of <?php echo $pages; ?></p><div>
</div>

