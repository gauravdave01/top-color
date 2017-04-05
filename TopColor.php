<?php

/***
 * Class TopColor
 * - Deals with Image specific customization
 */
class TopColor{

    /***
     * [Step I] Method to resize image
     *
     * @param $imgResource
     * @return bool
     */
    public function resizeImage($imgResource){
        // Default Variable
        $imageName = 'img_0.png';
        $shrinkPercent = 0.65;

        // Get new dimensions
        list($oldWidth, $oldHeight) = getimagesize($imgResource);
        $newWidth = $oldWidth * $shrinkPercent;
        $newHeight = $oldHeight * $shrinkPercent;

        // Resample
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $orgImage = imagecreatefromstring(file_get_contents($imgResource));
        imagecopyresampled($newImage, $orgImage, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

        imagepng($newImage, $imageName);

        return $imageName;
    }

    /***
     * [Step II] Method to apply filters on image
     *
     * @param $resizedImage
     * @return bool
     */
    public function applyFilter($resizedImage){
        // Default Variable
        $imageName = 'img_1.png';
        $brightnessLevel = -210;

        $imageResource = imagecreatefromstring(file_get_contents($resizedImage));

        imagefilter($imageResource, IMG_FILTER_EDGEDETECT);
        imagefilter($imageResource, IMG_FILTER_BRIGHTNESS, $brightnessLevel);
        imagefilter($imageResource, IMG_FILTER_GRAYSCALE);
        imagefilter($imageResource, IMG_FILTER_MEAN_REMOVAL);

        imagepng($imageResource, $imageName);
        imagedestroy($imageResource);

        return $imageName;
    }

    /***
     * [Step III] Method to replace White with Green Color
     *
     * (Note: This step is an additional step, this could be skipped and Step IV will be dependant on Step II)
     * @param $filterImage
     * @return string
     */
    public function setColor($filterImage){
        // Default Variable
        $imageName = 'img_2.png';

        $imageResource = imagecreatefrompng($filterImage);

        imagetruecolortopalette($imageResource, false, 255);

        $index = imagecolorclosest ($imageResource, 255, 255, 255); // Get White Color
        imagecolorset($imageResource, $index, 0, 255, 0); // Set Green Color

        imagepng($imageResource, $imageName);
        imagedestroy($imageResource);

        return $imageName;
    }

    /***
     * [Step IV] Method to Crop Image
     *
     * @param $coloredImage
     * @param $resizedImage
     * @return string
     */
    public function cropImage($coloredImage, $resizedImage){
        // Default Variable
        $fileName = 'img_3.png';
        
        $imageResource = imagecreatefrompng($coloredImage);
        $size = getimagesize($coloredImage);
        
        $height = $size[1];
        $width = $size[0];
        $half = $width/2;
        $xCord_R = $yCord_R = $xCord_L = $yCord_L = [];

        //******************************
        // Getting Right Co-ordinates
        //******************************

        for($y = 0; $y < $height; $y++){
            for($x = $half; $x < $width; $x++){
                $thisColor = imagecolorat($imageResource, $x, $y); //9475598
                $rgb = imagecolorsforindex($imageResource, $thisColor); //RGBA array
                $red = round(round(($rgb['red'] / 0x33)) * 0x33); // 153
                $green = round(round(($rgb['green'] / 0x33)) * 0x33); // 153
                $blue = round(round(($rgb['blue'] / 0x33)) * 0x33); // 0

                $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue); // FF9900

                if($thisRGB == '00FF00'){
                    $xCord_R[] = $x;
                    $yCord_R[] = $y;
                }
            }
        }

        rsort($xCord_R);
        sort($yCord_R);

        if (empty($xCord_R)) {
            $endPoint_R = $half;
        } else {
            $endPoint_R = $xCord_R[0]; // Point where green pixel last occurred.
        }

        if (empty($yCord_R)) {
            $topPoint_R = 1;
            $lastPoint_R = 1;
        } else {
            $lastPixel_R = count($yCord_R) - 1;

            $topPoint_R = $yCord_R[0]; // Point where green pixel starts.
            $lastPoint_R = $yCord_R[$lastPixel_R]; // No more green pixel after this.
        }

        //******************************
        // Getting Left Co-ordinates
        //******************************
        for($y = 0; $y < $height; $y++){
            for($x = 0; $x < $half; $x++){
                $thisColor = imagecolorat($imageResource, $x, $y); //9475598
                $rgb = imagecolorsforindex($imageResource, $thisColor); //RGBA array
                $red = round(round(($rgb['red'] / 0x33)) * 0x33); // 153
                $green = round(round(($rgb['green'] / 0x33)) * 0x33); // 153
                $blue = round(round(($rgb['blue'] / 0x33)) * 0x33); // 0

                $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue); // FF9900

                if($thisRGB == '00FF00'){
                    $xCord_L[] = $x;
                    $yCord_L[] = $y;
                }
            }
        }

        sort($yCord_L);
        sort($xCord_L);

        if (empty($xCord_L)) {
            $startPoint_L = $half;
        } else {
            $startPoint_L = $xCord_L[0]; // Point where green pixel last occurred.
        }

        if (empty($yCord_L)) {
            $topPoint_L = 1;
            $lastPoint_L = 1;
        } else {
            $lastPixel_L = count($yCord_L) - 1;

            $topPoint_L = $yCord_L[0]; // Point where green pixel starts.
            $lastPoint_L = $yCord_L[$lastPixel_L]; // No more green pixel after this.
        }

        $gap = $endPoint_R - $startPoint_L;
        $length = (($lastPoint_R - $topPoint_R) + ($lastPoint_L - $topPoint_L)) / 2;
        $topPoint = ($topPoint_R >= $topPoint_L)? $topPoint_R : $topPoint_L;

        $cropImg = imagecreatefromstring(file_get_contents($resizedImage));
        $to_crop_array = array('x' =>$startPoint_L , 'y' => $topPoint, 'width' => $gap, 'height'=> $length);
        $croppedImage = imagecrop($cropImg, $to_crop_array);

        imagepng($croppedImage, $fileName);
        imagedestroy($croppedImage);

        return $fileName;
    }

    /***
     * [Step V] Method to fetch top 5 dominating color hexcodes
     *
     * @param $imageFile
     * @param $numColors
     * @param $granularity
     * @param $isWhiteFlag
     * @return array|bool
     */
    function extractColor($imageFile, $numColors, $granularity, $isWhiteFlag=false){
        $granularity = max(1, abs((int)$granularity)); // No. of pixel to skip
        $colors = []; // Stores hex and occurrence
        $size = getimagesize($imageFile);

        if($size === false){
            user_error("Unable to get image size data");
            return false;
        }

        // To load any file type you can use this:
        $img = imagecreatefromstring(file_get_contents($imageFile));

        if(!$img) {
            user_error("Unable to open image file");
            return false;
        }

        for($x = 0; $x < $size[0]; $x += $granularity)
        {
            for($y = 0; $y < $size[1]; $y += $granularity)
            {
                $thisColor = imagecolorat($img, $x, $y); //9475598
                $rgb = imagecolorsforindex($img, $thisColor); //RGBA array
                $red = round(round(($rgb['red'] / 0x33)) * 0x33); // 153
                $green = round(round(($rgb['green'] / 0x33)) * 0x33); // 153
                $blue = round(round(($rgb['blue'] / 0x33)) * 0x33); // 0

                $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue); // FF9900

                if(array_key_exists($thisRGB, $colors)) {
                    $colors[$thisRGB]++; // If hex exist then occurrence increase
                } else {
                    $colors[$thisRGB] = 1; // If hex not found, then occurrence is init to 1
                }
            }
        }

        arsort($colors); // Sort Array of color:key & occurrence:value in asc order

        if($isWhiteFlag){
            unset($colors['FFFFFF']);
        }

        return array_slice(array_keys($colors), 0, $numColors);
    }
}