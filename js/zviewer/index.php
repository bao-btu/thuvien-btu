<?php
/**
 * Copyright (C) 2009  Hendro Wicaksono (hendrowicaksono@yahoo.com)
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
 */

// key to authenticate
define('INDEX_AUTH', '1');

/* Zviewer */

require '../../sysconfig.inc.php';
LIB.'member_session.inc.php';
session_start();

if (!isset($_GET['swf'])) {
    $swf = '';
} else {
    $swf = $_GET['swf'];
}
// get file ID
$fileID = isset($_GET['fid'])?(integer)$_GET['fid']:0;
// get biblioID
$biblioID = isset($_GET['bid'])?(integer)$_GET['bid']:0;

// query file to database
$sql_q = 'SELECT att.*, f.* FROM biblio_attachment AS att
    LEFT JOIN files AS f ON att.file_id=f.file_id
    WHERE att.file_id='.$fileID.' AND att.biblio_id='.$biblioID.' AND att.access_type=\'public\'';
$file_q = $dbs->query($sql_q);
$file_d = $file_q->fetch_assoc();

if ($file_q->num_rows > 0) {
    $file_loc = REPOBS.str_ireplace('/', DS, $file_d['file_dir']).DS.$file_d['file_name'];
    if (file_exists($file_loc)) {
        if ($file_d['access_limit']) {
            if (utility::isMemberLogin()) {
                $allowed_mem_types = @unserialize($file_d['access_limit']);
                if (!in_array($_SESSION['m_member_type_id'], $allowed_mem_types)) {
                    # Access to file restricted
                    # Member logged in but doesnt have privilege to download
                    header("location:index.php");
                    exit();
                } else {
                    # Access to file restricted
                    # Member logged in but doesnt have privilege to download
                    header("location:index.php");
                    exit();
		}
            }
        }

        if ($file_d['mime_type'] == 'application/pdf') {
            $swf = basename($file_loc);
            $swf = sha1($swf);
            $swf = $swf.'.swf';

?>
		<!DOCTYPE html>
		<html><head>
		  <title>Document flash player -  made in flash 9 actionscript 2.0  with xml suport|Full screen enabled</title>
		  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<style type="text/css">
			#wrapper {margin: 0 auto; width: 800px;}
			</style>
			<script type="text/javascript" src="swfobject/swfobject.js"></script>
			<script type="text/javascript">
			var flashvars = { doc_url: "../../files/swfs/<?php echo $swf; ?>", };
			var params = { menu: "false", bgcolor: '#efefef', allowFullScreen: 'true' };
			var attributes = { id: 'website' };
			swfobject.embedSWF('zviewer.swf', 'website', '800', '800', '9.0.45', 'swfobject/expressinstall.swf', flashvars, params, attributes);
			</script>
			<link rel="stylesheet" type="text/css" href="../../template/default/css/bootstrap.min.css" />
		  </head>
		  <body>
			<div id="wrapper">
			<?php if ($sysconf['allow_pdf_download']) { ?>
			<div><a class="btn btn-danger" href="../../index.php?p=fstream-pdf&fid=<?php echo $fileID; ?>&bid=<?php echo $biblioID; ?>"><i class="icon-download"></i> <?php echo __('Download this file'); ?></a></div>
			<?php } ?>

			<div id="website">
			<p align="center" class="style1">In order to view this page you need Flash Player 9+ support!</p>
			<p align="center">
			<a href="http://www.adobe.com/go/getflashplayer">
			<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
			</div>
			</div>
		    </body>
		</html>
		<?php
		exit();
        } else {
            header('Content-Disposition: inline; filename="'.basename($file_loc).'"');
            header('Content-Type: '.$file_d['mime_type']);
            readfile($file_loc);
            exit();
        }

    } else {
        die('<div class="errorBox">File Not Found!</div>');
    }
} else {
    header('location:../../index.php');
}
