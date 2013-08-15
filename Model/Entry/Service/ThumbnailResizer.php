<?php

namespace LwListtool\Model\Entry\Service;

class ThumbnailResizer
{

    public function __construct($imagepath)
    {
        $this->imagepath = str_replace(" ", "_", $imagepath);
        $this->filename = basename($imagepath);
        $this->setImageParams();
        $this->image = $this->createImage();
    }

    public function getFilename()
    {
        return $this->filename;
    }

    function setImageParams()
    {
        list($this->width, $this->height, $type) = @getimagesize($this->imagepath);
        switch ($type) {
            case 1:
                $this->type = "gif";
                break;

            case 2:
                $this->type = "jpg";
                break;

            case 3:
                $this->type = "png";
                break;

            default:
                $this->type = "unknown";
                break;
        }
        $ext = strtolower(\lw_io::getFileExtension($this->imagepath));
        if ($ext != $this->type && $ext != "jpeg")
            throw new Exception("Image type not available");
    }

    function createImage()
    {
        switch ($this->type) {
            case "jpg":
                return imagecreatefromjpeg($this->imagepath);
                break;

            case "png":
                return imagecreatefrompng($this->imagepath);
                break;

            case "gif":
                return imagecreatefromgif($this->imagepath);
                break;

            default:
                die("no image");
                break;
        }
    }

    function saveImage()
    {
        $image = $this->image;
        $destination = $this->imagepath;
        ;

        switch ($this->type) {
            case "jpg":
                return imagejpeg($image, $destination);
                break;

            case "png":
                return imagepng($image, $destination);
                break;

            case "gif":
                return imagegif($image, $destination);
                break;

            default:
                die("no image");
                break;
        }
    }

    function setParams($width, $height)
    {
        $this->params['center'] = true;
        $this->params['middle'] = true;
        $this->params['height'] = $height;
        $this->params['width'] = $width;
        $this->params['resize'] = "max";
    }

    public function resize()
    {
        if ($this->width > $this->params['width']) {
            $ow = $this->width;
            $oh = $this->height;
            $tw = $this->params['width'];
            $th = $this->params['height'];

            if ($this->params['resize']) {
                $w_ratio = $tw / $ow;
                $tc_width = $ow * $w_ratio;
                $tc_height = floor($oh * $w_ratio);
                if (($this->params['resize'] == "max" && $tc_height < $th) || ($this->params['resize'] == "min" && $tc_height > $th)) {
                    $h_ratio = $th / $oh;
                    $tc_width = floor($ow * $h_ratio);
                    $tc_height = $oh * $h_ratio;
                }
            }
            elseif ($tw > 0 && !$th) {
                $w_ratio = $tw / $ow;
                $tc_width = $ow * $w_ratio;
                $tc_height = floor($oh * $w_ratio);
            }
            elseif ($th > 0 && !$tw) {
                $h_ratio = $th / $oh;
                $tc_width = floor($ow * $h_ratio);
                $tc_height = $oh * $h_ratio;
            }
            else {
                $tc_width = $ow;
                $tc_height = $oh;
            }
            // Resample
            if ($this->type == "jpg")
                $image_p = imagecreatetruecolor($tc_width, $tc_height);
            if (!$image_p)
                $image_p = imagecreate($tc_width, $tc_height);

            $ok = imagecopyresampled($image_p, $this->image, 0, 0, 0, 0, $tc_width, $tc_height, $ow, $oh);
            if ($ok) {
                unset($this->image);
                $this->image = $image_p;
                $this->width = $tc_width;
                $this->height = $tc_height;
            }
        }
        //exit();
    }

    public function crop()
    {
        $ow = $this->width;
        $oh = $this->height;
        $tw = $this->params['width'];
        $th = $this->params['height'];

        // Resample
        if ($this->type == "jpg")
            $image_p = @imagecreatetruecolor($tw, $th);
        if (!$image_p)
            $image_p = imagecreate($tw, $th);

        $wstart = $hstart = 0;
        if ($this->params['right'])
            $wstart = $ow - $tw;
        if ($this->params['left'])
            $wstart = 0;
        if ($this->params['center'])
            $wstart = ceil(($ow - $tw) / 2);
        if ($this->params['top'])
            $hstart = 0;
        if ($this->params['bottom'])
            $hstart = $oh - $th;
        if ($this->params['middle'])
            $hstart = ceil(($oh - $th) / 2);

        $ok = imagecopy($image_p, $this->image, 0, 0, $wstart, $hstart, $tw, $th);
        if ($ok) {
            unset($this->image);
            $this->image = $image_p;
            $this->width = $tw;
            $this->height = $th;
        }
    }

}