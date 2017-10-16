#!/usr/bin/php
<?php
/*
script used to prepare rqcsite for deployment
*/

//replace the google anayltics -2 code with -1 for the base domain
$customFooterContent = file_get_contents("layouts/partials/custom-footer.html");
$originalCustomFooterContent = $customFooterContent;
$customFooterContent = str_replace("1-2","1-1",$customFooterContent);
file_put_contents("layouts/partials/custom-footer.html",$customFooterContent);

//run a build to be sure
passthru("./build.php");

passthru("rm -rf production;cp -r public production;");

file_put_contents("layouts/partials/custom-footer.html",$originalCustomFooterContent);

passthru("./build.php");
