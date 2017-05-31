<?PHP
/*
* Original name: "Irwin Associates image resizer"
* url: https://github.com/gandalf458/bulk-image-resizer
*
* Base author: «Irwin» (http://www.irwinassociates.eu/)
* Modding: «Heavenanvil» (heavenanvil@gmail.com / heavenanvil.ru)
* Help: «Sail» (http://forum.php.su/profile.php?action=show&member=17335)
*/
?>
<?php
ini_set("memory_limit", "512M");
set_time_limit(300);
error_reporting(0);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Image Resizer v1.0</title>
<style>
* {
        padding: 0;
        margin: 0;
}
 
body {
        font: 12px Verdana, Arial, Helvetica, sans-serif;
        margin-left: 40px;
        margin-top: 20px;
}
 
label {
        float: left;
        width: 200px;
        font: 12px Verdana, Arial, Helvetica, sans-serif;
}
 
input {
        display: block;
        font: inherit;
        margin-bottom: 6px;
}
 
input[type="text"],
input[type="number"] {
        border: solid 1px #a0a0a0;
        box-shadow: 2px 2px 2px #c2c2c2;
        padding: 2px 3px;
        background: #f0fdff;
        width: 170px;
}
 
input[type="number"] {
        width: 60px;
}
 
.my_table {
        border-collapse:collapse;
        border: 0;
        table-layout: fixed;
}
 
.my_table td {
        padding: 3px;
        border: 1px solid #ccc;
        vertical-align: middle;
}
 
.good {
        background: #ddffdd;
}
 
.bad {
        background: #ffdddd;
}
 
.skip {
        background: #ddddff;
}
 
.line {
        border: 0 !important;
        height: 5px !important;
        font-size: 4px;
}
       
.left {
        text-align: left;
}
 
.center {
        text-align: center;
}
 
.right {
        text-align: right;
}
 
.clip {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 500px;
        max-width: 500px;
}
 
.img {
        border: 1px solid #ccc;
        max-width: 70px;
}
</style>
</head>
<body>
        <h2>Image Resizer v1.0 - 29.05.2017</h2>
        <strong>Автор: <a href="http://www.irwinassociates.eu/">Irwin</a>. Доработал: <a href="http://heavenanvil.ru">Heavenanvil</a> (heavenanvil@gmail.com), помог: <a href="http://forum.php.su/profile.php?action=show&member=17335">Sail</a></strong>
<?PHP
 
$show_img = isset($_POST['checkbox3']) ? 1 : 0;
$searchinsubdir = isset($_POST['searchinsubdir']) ? true : false;
 
function makeFileListR($dir, $aext=[], $usesubdir = false) {
    $files = $subfiles = [];
    $handle = opendir($dir);
    while (false !== ($file = readdir($handle))) {  
        if ($file == "." || $file == "..") {
            continue;
        }
        $str = $dir.DIRECTORY_SEPARATOR.$file;
        $isDir = is_dir($str);
        if($isDir && $usesubdir) {
          $subfiles = makeFileListR($str, $aext, $usesubdir);
          $files = array_merge($files,$subfiles);
        } elseif(!$isDir && (empty($aext) || in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $aext))) {
          $files[] = $str;
        }
    }
    closedir($handle);
    return $files;
}
 
function resizer($fileName, $maxWidth, $maxHeight, $fixedWidth, $fixedHeight, $oldDir, $newDir, $quality)
{
        $file = $oldDir.DIRECTORY_SEPARATOR.$fileName;
        $fileDest = $newDir.DIRECTORY_SEPARATOR.$fileName;
        list($width, $height) = getimagesize($file);
        $skip = false;
       
        $again = isset($_POST['checkbox1']) ? 1 : 0;
        $zoom = isset($_POST['checkbox2']) ? 1 : 0;
 //       $show_img = isset($_POST['checkbox3']) ? 1 : 0;
 
        if ( $fixedWidth )
        {
            $newWidth  = $fixedWidth;
            $newHeight = ($newWidth / $width) * $height;
        }
        elseif ( $fixedHeight )
        {
            $newHeight = $fixedHeight;
            $newWidth  = ($newHeight / $height) * $width;
        }
        elseif ( $width < $height )                                                     // Вертикальное изображение
        {
            $newHeight = $maxHeight;
            $newWidth  = ($newHeight / $height) * $width;
        }
        elseif ( $width > $height )                                                     // Горизонтальное изображение
        {
            $newWidth  = $maxWidth;
            $newHeight = ($newWidth / $width) * $height;
        }
        else                                                                                                    // Квадратное изображение
        {
            $newWidth  = $maxHeight;
            $newHeight = $maxHeight;
        }
 
        if ($zoom == 1)                                                                                 // Увеличивать маленькие изображения
        {
                if (( $width < $newWidth ) AND ( $height < $newHeight ))
                {
                        $newWidth = $width;
                        $newHeight = $height;
                        $skip = false;
                }
        }
        if (( $width < $newWidth ) AND ( $height < $newHeight ) AND ($zoom == 0))
        {
                $skip = true;
        }
       
        if ($again == 1)                                                                                        // Обрабатывать повторно (пересжимать)
        {
                if ((( $width == $newWidth ) AND ( $height < $newHeight )) OR
                        (( $width < $newWidth ) AND ( $height == $newHeight )))
                {
                        $skip = false;
                }
        }
       
        if ($again == 0)                                                                                        // Обрабатывать повторно (пересжимать)
        {
                if ((( $width == $newWidth ) AND ( $height <= $newHeight )) OR
                        (( $width <= $newWidth ) AND ( $height == $newHeight )))
                {
                        $skip = true;
                }
        }
 
        global $sWidth, $sHeight, $sNewWidth, $sNewHeight, $filesize, $newfilesize;
        $sWidth = $width;
        $sHeight = $height;
        $sNewWidth = $newWidth;
        $sNewHeight = $newHeight;
        $filesize1 = filesize($file);
        $filesize = round($filesize1/1000, 2);
        $extn = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $imageDest = imagecreatetruecolor($newWidth, $newHeight);
 
        // jpeg
        if ( $extn == 'jpg' or $extn == 'jpeg' )
        {
            if ($skip == false)
            {
                $imageSrc  = imagecreatefromjpeg($file);
                if ( imagecopyresampled($imageDest, $imageSrc, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height) )
                {
                    imagejpeg($imageDest, $fileDest, $quality);
                    imagedestroy($imageSrc);
                    imagedestroy($imageDest);
                    if($fileDest == $file) {
                        clearstatcache(TRUE, $fileDest);
                    }
                    $newfilesize1 = filesize($fileDest);
                    $newfilesize = round($newfilesize1/1000, 2);
                    return true;
                }
                return false;
            }
        }
 
        // png
        if ( $extn == 'png' )
        {
                if ($skip == false)
                {
                        imagealphablending($imageDest, false);
                        imagesavealpha($imageDest, true);
                        $imageSrc = imagecreatefrompng($file);
                        if ( imagecopyresampled($imageDest, $imageSrc, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height) )
                        {
                                imagepng($imageDest, $fileDest, ($quality / 10) - 1);
                                imagedestroy($imageSrc);
                                imagedestroy($imageDest);
                                if($fileDest == $file) {
                                    clearstatcache(TRUE, $fileDest);
                                }
                                $newfilesize1 = filesize($fileDest);
                                $newfilesize = round($newfilesize1/1000, 2);
                                return true;
                        }
                        return false;
                }
        }
}
  
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) :
    $maxWidth    = filter_input(INPUT_POST, 'maxWidth', FILTER_VALIDATE_INT);
    $maxHeight   = filter_input(INPUT_POST, 'maxHeight', FILTER_VALIDATE_INT);
    $fixedWidth  = filter_input(INPUT_POST, 'fixedWidth', FILTER_VALIDATE_INT);
    $fixedHeight = filter_input(INPUT_POST, 'fixedHeight', FILTER_VALIDATE_INT);
    $oldDir      = filter_input(INPUT_POST, 'oldDir', FILTER_SANITIZE_STRING);
    $newDir      = filter_input(INPUT_POST, 'newDir', FILTER_SANITIZE_STRING);
    $quality     = filter_input(INPUT_POST, 'quality', FILTER_VALIDATE_INT);
 
    $back = htmlspecialchars($_SERVER['PHP_SELF']);
 
    // Создание папки для сохранения, если её не существует.
    if ( !file_exists($newDir) )
    {mkdir($newDir);}
    elseif(!is_dir($newDir))
    {
        die('<br /><br />Папка сохранения существует, но не является каталогом.<br /><br /><p><a href="' . $back . '">« Вернуться назад</a></p>');
    }
 
    // Проверка, что исходная папка существует, открытие и получение всех файлов из неё.
    if ( !file_exists($oldDir) )
    {die('<br /><br />Исходная папка не существует.<br /><br /><p><a href="' . $back . '">« Вернуться назад</a></p>');}
 
    $files = makeFileListR($oldDir, [], $searchinsubdir);
    if(empty($files))
        {die('<br /><br />Исходная папка пуста.<br /><br /><p><a href="' . $back . '">« Вернуться назад</a></p>');}
    echo '<br />';
    echo '<br />';
    echo '<p><strong>Ваши параметры:</strong> макс. ширина: <strong>', $maxWidth,
        '</strong>, макс. высота <strong>', $maxHeight, '</strong>, фикс. ширина: <strong>',
        $fixedWidth, '</strong>, фикс. высота: <strong>', $fixedHeight,
        '</strong>, качество <strong>', $quality, '%</strong></p>', "\n";
    echo '<br />';
    echo '<TABLE class="my_table">', "\n";
 
    // Сортировка файлов по имени
    natcasesort($files);
 
    $total_files = $total_good_files = $total_old_size = $total_new_size = 0;
    // Обработка каждого файла.  
    foreach ($files as $key)
    {
            $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
            $filename = pathinfo($key, PATHINFO_FILENAME);
            $file = $filename . '.' . $ext;
            $path = ltrim(pathinfo($key, PATHINFO_DIRNAME), $oldDir);
            $srcDir = $oldDir.$path;
            $dstDir = $newDir.$path;
 
            $total_files = $total_files + 1;
           
            if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
            {
                if(!file_exists($dstDir)) {
                    mkdir($dstDir, 0777, true);
                }
                if (resizer($file, $maxWidth, $maxHeight, $fixedWidth, $fixedHeight, $srcDir, $dstDir, $quality))
                {
                    $compressed_kb = round(($filesize - $newfilesize), 2);
                    $compressed_percent1 = round(($newfilesize * 100 / $filesize), 2);
                    $compressed_percent = round(100 - $compressed_percent1,2);
 
                    $total_good_files = $total_good_files + 1;
                    $total_old_size = $total_old_size + $filesize;
                    $total_new_size = $total_new_size + $newfilesize;
 
                    if ($show_img == 1)
                    {              
                    $img = '<a href="'. $dstDir .'/'. $file .'"><img src="'. $dstDir .'/'. $file .'" height="50px" class="img" /></a>';
                    }
                    if ($show_img == 0)
                    {              
                    $img = '[<a href="'. $dstDir .'/'. $file .'">открыть</a>]';
                    }
                    echo '
                   <tr class="good">
                           <td rowspan="3" class="center">'. $total_files . '</td>
                           <td rowspan="3" class="center">' . $img .'</td>
                           <td colspan="4" class="left"><p class="clip">&nbsp;«<strong>', $key, '</strong>» успешно обработан.&nbsp;</p></td>
                   </tr>
 
                   <tr class="good">
                           <td class="left">&nbsp;Исходный размер:</td>
                           <td class="right">', $sWidth, 'x', $sHeight, 'px&nbsp;</td>
                           <td class="right">', $filesize,' Кб&nbsp;</td>
                           <td rowspan="2" class="center">Сжато на <br/>', $compressed_kb,' Кб<br/>(', $compressed_percent,'%)</td>
                   </tr>
 
                   <tr class="good">
                           <td class="left">&nbsp;Новый размер:</td>
                           <td class="right">', round($sNewWidth), 'x', round($sNewHeight), 'px&nbsp;</td>
                           <td class="right">', $newfilesize,' Кб&nbsp;</td>
                   </tr>
 
                   <tr>
                           <td class="line" colspan="6">&nbsp;</td>
                   </tr>
                   ';
                }
                else
                {
                    if ($show_img == 1)
                    {
                    $img = '<a href="'. $srcDir .'/'. $file .'"><img src="'. $srcDir .'/'. $file .'" height="50px" class="img" /></a>';
                    }
                    if ($show_img == 0)
                    {
                    $img = '[<a href="'. $srcDir .'/'. $file .'">открыть</a>]';
                    }
                echo '
                   <tr class="skip">
                           <td rowspan="3" class="center">'. $total_files . '</td>
                           <td rowspan="3" class="center">' . $img .'</td>
                           <td colspan="4" class="left"><p class="clip">&nbsp;«<strong>', $key, '</strong>» ошибка изменения размера. Файл пропущен.&nbsp;</p></td>
                   </tr>
 
                   <tr class="skip">
                           <td class="left">&nbsp;Исходный размер:</td>
                           <td class="right">', $sWidth, 'x', $sHeight, 'px&nbsp;</td>
                           <td class="right">', $filesize,' Кб&nbsp;</td>
                           <td class="center" rowspan="2">Пропущено</td>
                   </tr>
 
                   <tr class="skip">
                           <td class="left" colspan="3">&nbsp;Не соответствует заданным условиям, либо уже обработан.</td>
                   </tr>
 
                   <tr>
                           <td colspan="6" class="line">&nbsp;</td>
                   </tr>
                   ';
                }
            }
            else
            {
                $img = '[<a href="'. $srcDir .'/'. $file .'">открыть</a>]';
                echo '
               <tr class="bad">
                       <td rowspan="3" class="center">'. $total_files . '</td>
                       <td rowspan="3" class="center">' . $img .'</td>
                       <td colspan="4" class="left"><p class="clip">&nbsp;«<strong>', $key, '</strong>» ошибка изменения размера. Файл пропущен.&nbsp;</p></td>
               </tr>
 
               <tr class="bad">
                       <td class="left" colspan="3">&nbsp;</td>
                       <td class="center" rowspan="2">Пропущено</td>
               </tr>
 
               <tr class="bad">
                       <td class="left" colspan="3">&nbsp;Возможно файл не является форматом JPG или PNG.</td>
               </tr>
 
               <tr>
                       <td colspan="6" class="line">&nbsp;</td>
               </tr>
               ';
            }
    }
    if ($total_new_size > 0) {
        $total_percent1 = round(($total_new_size * 100 / $total_old_size),2);
    } else {
        $total_percent1 = 0;
    }
    $total_percent = 100 - $total_percent1;
    $total_plus = round($total_old_size - $total_new_size, 2);
//    Ни к чему. Оно ранее инициализировано.
//    if (!isset($total_good_files)) { $total_good_files = 0; }
//    if (!isset($total_old_size)) { $total_old_size = 0; }
//    if (!isset($total_new_size)) { $total_new_size = 0; }
//    if (!isset($total_plus)) { $total_plus = 0; }
//    if (!isset($total_percent)) { $total_percent = 0; }
    echo '</TABLE>', "\n";
    echo 'Итого в каталоге: <strong>'. $total_files.'</strong> файлов.<br>
	Успешно обработано: <strong>'. $total_good_files.'</strong>  изображений.<br>
	Объем всех изображений до обработки: <strong>'. $total_old_size .'</strong> кб.<br>
	Объём изображений после сжатия: <strong>'. $total_new_size .'</strong> кб.<br>
	Итого объем уменьшился на <strong>'. $total_plus . '</strong> кб. ('. $total_percent .'%)<br />
	<p><strong>Выполнено</strong></p><br /><p><a href="'. $back .'">« Вернуться назад</a></p><br /><br /><br />';
else :
?>
<br /><ul>
        <li>Этот скрипт изменит размер всех JPEG и PNG изображений в указанной папке в соответствии с заданными ниже параметрами.</li>
        <li>Большие изображения уменьшаются до указанного максимального значения ширины или высоты, с соотношением сторон, если не указаны фиксированные значения.</li>
        <li>Для файлов PNG элементы прозрачности сохраняются.</li>
        <li>Папка для сохранения будет создана автоматически, если она не существует.</li>
        <li>Если имя исходной папки и папки сохранения будет указано одинаковое, то исходные изображения будут заменены на обработанные.</li>
        <li>Вы можете указать не только название каталога, но и путь до него.</li>
        <li>Параметр «Обрабатывать повторно» обрабатывает изображения, которые уже возможно было обработаны, но соответствуют критериям. (Пересжимает ещё раз)</li>
        <li>Параметр «Увеличивать» обрабатывает изображения, которые меньше заданных размеров и увеличивает их до выставленных размеров. Будьте осторожны!</li>
        <li>Параметр «Показать эскизы» включает отображение миниатюр файлов. Не рекомендуется включать если файлов в папке очень много. (Большой расход трафика)</li>
        <li>После нажатия кнопки «Resize» процесс может занять несколько минут.</li>
        <li style="color: red;">Все действия вы совершаете на свой страх и риск. И чаще делайте бэкапы!</li>
</ul>
<form id="form1" name="form1" method="post">
        <label for="maxWidth">Максимальная ширина</label>
        <input type="number" name="maxWidth" id="maxWidth" value="1200">
        <label for="maxHeight">Максимальная высота</label>
        <input type="number" name="maxHeight" id="maxHeight" value="800" size="6">
        <label for="fixedWidth">Фиксированная ширина</label>
        <input type="number" name="fixedWidth" id="fixedWidth" value="0" size="6">
        <label for="fixedHeight">Фиксированная высота</label>
        <input type="number" name="fixedHeight" id="fixedHeight" value="0" size="6">
        <label for="oldDir">Исходная папка</label>
        <input type="text" name="oldDir" id="oldDir" value="uploads">
        <label for="newDir">Папка сохранения</label>
        <input type="text" name="newDir" id="newDir" value="uploads">
        <label for="quality">Качество в % (от 10 до 100)  </label>
        <input type="number" name="quality" id="quality" min="10" max="100" value="70" size="6">
        <label>Обрабатывать повторно</label>
        <input type="checkbox" name="checkbox1" value="yes" checked>
        <label>Увеличивать мелкие изобр.</label>
        <input type="checkbox" name="checkbox2" value="yes">
        <label>Показать эскизы </label>
        <input type="checkbox" name="checkbox3" value="yes">
        <label for="searchinsubdir">Искать в подкаталогах</label>
        <input type="checkbox" name="searchinsubdir" id="searchinsubdir">
        <input type="submit" name="submit" id="submit" value="Resize" style="width: 100px; height: 30px;">
</form>
<?PHP
endif;
?>
</body>
</html>