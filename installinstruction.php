<?php

define('CONFIG_FILE_PATH', 'config.php');

class checks{
    function configFileExists(){
        return file_exists(CONFIG_FILE_PATH);
    }
    
}


class FreeCMDBInstallDoc
{

    function head() 
    {
          header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
                <link rel="stylesheet" href="FreeCMDB.css" type="text/css" media="screen,projection" />
                <script type="text/javascript" src="prototype.js"></script>
                <script type="text/javascript" src="FreeCMDB.js"></script>
                <title>FreeCMDB Install Manual</title>
        </head>
        <body>
<div class="content_install">
<div class="content_inner">

<?php      

    }
    
    
    function view()
    {
        $this->head();
        

?>
<table class='striped' width="99%">
	<thead>
		<tr>
			<th>What Is FreeCMDB?</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td align="left">
			 	Product description goes here
			</td>
		</tr>
	</tbody>
</table>  

<table class='striped' width="99%">
	<thead>
		<tr>
			<th>FreeCMDB Requirements</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td align="left">
				<strong>Hardware</strong><br />
				<ul>
					<li>Apache Server  with PHP 5.x</li>
					<li>PostgreSQL</li>
				</ul>
				<strong>Software</strong><br />				
				<ul>
					<li>Apache Server  with PHP 5.x</li>
					<li>PostgreSQL</li>
					<li>Web Browser - Firefox Preferably</li>
				</ul>

			</td>
		</tr>
	</tbody>
</table>
<table class='striped' width="99%">
	<thead>
		<tr>
			<th>FreeCMDB Installation</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td align="left">
			<ol>
				<li><a href="">Download</a> the FreeCMDB</li>
				<li>Uncompress (with zip/unzip) and extract (with tar/untar) the setup file using the following command</li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
			</ol>
			</td>
		</tr>
	</tbody>
</table>    
<script>
stripe();
</script>
<?php
        $this->tail();
        
    }


    function tail()
    {
        ?>
</div>
</div>

        </body>
</html>
<?php
}
}

$cmdb = new FreeCMDBInstallDoc();
$cmdb->view();

?>
