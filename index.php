<?php
include("header.inc.php");
include("navigation.inc.php");
?>

<h1>What is the VFO project?</h1>
<p>In short, the Virtual Fireball Observatory is a web-based input system for fireball data. One of the greatest difficulties when gathering fireball observation data is the fact that this phenomenon is often observed by amateurs. In order to reach a target audience as broad as possible, a distributed and simplified <a href="form.php">report form</a> will be used which should be included on a wide range of scientific and astronomical organizations.</p>
<p>A large point of focus in this project is making sure the data supplied by the users is as error-free as possible. This is achieved by providing directions to each form field, and more importantly, by automatically reviewing the inputs. When filling in a field incorrectly, the user will be alerted with a message explaining what went wrong and how to fix it. Also, efforts were made to make sure reports remain free of any spam.</p>
<p>The data gathered will then be kept in a publicly accessible database, which can be queried through a <a href="queries.php">web-interface</a> supplied by this site. It is possible to review individual observations, or one can ask for the database to group observations of what it thinks is the same event.</p>
<p>The VFO was developed as a Bachelor project (2007-2008) at the <a href="http://www.ua.ac.be/main.aspx?c=.ENGLISH">University of Antwerp</a> by Nastassia Smeets. It is still possible to view <a href="http://www.esp.win.ua.ac.be/projects/show/372">the original assignment</a> and all <a href="documents.php">documents related to it</a> (in Dutch).</p>

<?php
include("sidebar.inc.php");
include("footer.inc.php");
?>