<?php
/**
 * Copyright (C) 2010  Hendro Wicaksono (hendrowicaksono@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * This program is for generating SWF cache from uploaded PDF files.
 * Only can be run from command line.
 * Go into lib/contents folder. then run:
 * "php update_swfcache.php"
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

$server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
if ($server_addr == '') {

    include ('../../sysconfig.inc.php');
    $sql = "SELECT files.file_id,files.file_dir,files.file_name FROM files,biblio_attachment WHERE mime_type='application/pdf' AND biblio_attachment.file_id=files.file_id";
    $query = $dbs->query($sql);

    while ($data = $query->fetch_assoc()) {
        $sha1_name = sha1($data['file_name']);
        $swf = $sha1_name.'.swf';
        $file_loc = REPOBS.str_ireplace('/', DS, $data['file_dir']).DS.$data['file_name'];
        $file_loc = preg_replace("/\/\//i", "/", $file_loc);

        echo 'Processing ...'."\n\n";
        echo $data['file_id'].'-'.$data['file_name'].' - '.$sha1_name."\n";
        echo $file_loc."\n";

        if (!file_exists('../../files/swfs/'.$swf.'')) {
            if (stripos(PHP_OS, 'Darwin') !== false) {
                @exec('../swftools/bin/darwin/pdf2swf -o ../../files/swfs/'.$swf.' '.$file_loc.'');
            } else if (stripos(PHP_OS, 'Linux') !== false) {
                if (PHP_INT_SIZE === 8) { // 64-bit mode
                  @exec('../swftools/bin/linux/pdf2swf64 -o files/swfs/'.$swf.' "'.$file_loc.'"');
                } else {
                  @exec('../swftools/bin/linux/pdf2swf -o ../../files/swfs/'.$swf.' '.$file_loc.'');
                }
            } else {
                @exec('..\swftools\bin\windows\pdf2swf.exe -o ../../files/swfs/'.$swf.' '.$file_loc.'');
            }
        } else {
            echo "\n".'SWF File is already exist. Skipped.'."\n";
        }

        echo "\n".'---------------------------------------'."\n";
    }

} else {
    header ("location:index.php");
}
