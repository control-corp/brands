<?php

namespace Micro\Http\Response;

use Micro\Http\Response;

class FileResponse extends Response
{
    public function send()
    {
        $this->download($this->getBody());
    }

    public function download($file)
    {
        ob_implicit_flush(true);
        ob_get_flush();
        @ob_get_clean();

        if (file_exists($file) && !is_dir($file)) {

            $fd = fopen($file, 'rb');

            $default = 'application/octet-stream';

            $types = array(

                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',

                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',

                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',

                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',

                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',

                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );

            if ($fd) {

                if (ini_get('zlib.output_compression'))  {
                    ini_set('zlib.output_compression', 'Off');
                }

                // fix for IE7/8, ticket #183
                if (function_exists('apache_setenv')) {
                    $func = 'apache_setenv';
                    $func('no-gzip', '1');
                }

                $fsize      = filesize($file);
                $path_parts = pathinfo($file);
                $ext        = strtolower($path_parts['extension']);

                header('Pragma: public');
                header('Expires: -1');
                header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
                header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
                header('Content-Type: ' . (isset($types[$ext]) ? $types[$ext] : $default));
                header('Content-length: ' . $fsize);
                header('Accept-Ranges: bytes');
                header("Content-Encoding:");

                error_reporting(E_ALL);

                set_time_limit(0);

                while(!feof($fd)) {
                    echo fread($fd, 1 * (1024 * 1024));
                    @ob_flush();
                    flush();
                    if (connection_status() != 0) {
                        @fclose($fd);
                        exit(0);
                    }
                }
                @fclose($fd);
                exit(0);
            } else  {
                // file couldn't be opened
                header('HTTP/1.0 500 Internal Server Error');
                exit(0);
            }
        } else {
            // file does not exist
            header('HTTP/1.0 404 Not Found');
            exit(0);
        }
    }
}