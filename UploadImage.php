<!DOCTYPE html>
<html>
<head>
    <title>Top Color</title>
</head>
<body>
<style>
    * {
        font-family: Arial, Helvetica, sans-serif;
    }

    table, td, th {
        border: 1px solid #ddd;
        text-align: left;
    }

    table {
        border-collapse: collapse;
    }

    th, td {
        text-align: left;
        padding: 8px;
    }

    tr:nth-child(even){background-color: #f2f2f2}

    th {
        background-color: #33b5e5;
        color: white;
    }
</style>

<?php

// Integrating Top Color Class
require('TopColor.php');

$topClr = new TopColor();
$imageUploaded = $_FILES['img']['tmp_name'];
$whiteFlag = ($_POST['whiteBG'] === 'on') ? true : false;

/* [START] Validation */
$validateImage = getimagesize($imageUploaded);
if($validateImage === FALSE){
    die('Invalid File Uploaded!');
}
/* [END] Validation */

/* [START] Image Processing */
$resizedImage = $topClr->resizeImage($imageUploaded); // Image: Resize
$filteredImage = $topClr->applyFilter($resizedImage); // Image: Apply Filter
$coloredImage = $topClr->setColor($filteredImage); // Image: Set Green Color
$croppedImage = $topClr->cropImage($coloredImage, $resizedImage); // Image: Crop
$colorPallets = $topClr->extractColor($croppedImage, 5, 1, $whiteFlag); // Image: Top 5 Color
/* [END] Image Processing */

/***
 * Method to display top 5 dominating color
 *
 * @param $colorPallets
 */
function showColorPallet($colorPallets){
    echo "<u>Top 5 Color Extracted:</u><br><br>";

    echo "<table>";
    echo "<tr><th>Color</th><th>Hexcode</th>";
    foreach($colorPallets as $color){
        echo "<tr><td style='background-color:#$color;width:2em;'>&nbsp;</td><td>#$color</td></tr>";
    }
    echo "</table>";
}

/***
 * Method to delete dummy files
 *
 * @param $deleteFiles
 */
function cleanUp($deleteFiles){
    foreach($deleteFiles as $file){
        unlink($file);
    }
}

// Show Color Pallet
showColorPallet($colorPallets);

// Clean Up! [Comment these line to get information on how image is processed]
$deleteFiles = [$resizedImage, $filteredImage, $coloredImage, $croppedImage];
cleanUp($deleteFiles);

?>

</body>
</html>