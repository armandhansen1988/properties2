<?php

class Functions extends DBC
{
    public function mysql2json($sql)
    {
        $data = array();
        $sql = DBC::dbsql($sql);
        while ($getSql = DBC::dbfetch($sql)) {
            $data[] = $getSql;
        }
        
        return json_encode($data);
    }
    
    public function sanatize($var)
    {
        if (!empty($var)) {
            return DBC::dbescape(htmlentities(strip_tags(trim($var))));
        } else {
            return "";
        }
    }
    
    public function generateToken()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $token = '';
        for ($i = 0; $i < 8; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }
        $token .= "-";
        for ($i = 0; $i < 4; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }
        $token .= "-";
        for ($i = 0; $i < 4; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }
        $token .= "-";
        for ($i = 0; $i < 4; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }
        $token .= "-";
        for ($i = 0; $i < 12; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }
        return $token;
    }
    
    public function generateThumbnail($img, $thumbnail_url, $width, $height, $quality = 90)
    {
        $imagick = new Imagick(realpath($img));
        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality($quality);
        $imagick->thumbnailImage($width, $height, false, false);
        $file_ext = pathinfo($thumbnail_url, PATHINFO_EXTENSION);
        $filename_no_ext = str_replace(".".$file_ext, "", $thumbnail_url);
        file_put_contents($filename_no_ext.'.jpg', $imagick);
    }
}
