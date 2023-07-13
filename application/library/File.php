<?php

class File
{
    public static function getDirFiles($path, $child = false)
    {
        $files = [];
        if (!$child) {
            if (is_dir($path)) {
                $dp = dir($path);
            } else {
                return null;
            }
            while ($file = $dp->read()) {
                if ('.' != $file && '..' != $file && is_file($path.$file)) {
                    $files[] = $file;
                }
            }
            $dp->close();
        } else {
            self::scanDirFiles($files, $path);
        }

        return $files;
    }

    public static function scanDirFiles(&$files, $path, $childDir = false)
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ('.' != $file && '..' != $file) {
                    if (is_file($path.$file)) {
                        $files[] = $file;
                    } else {
                        self::scanDirFiles($files[$file], $path.$file.DS, $file);
                    }
                }
            }
            $dp->close();
        }
    }

    public static function readDirFiles($path, $thumbPath = '', $dir = '')
    {
        $return = [];
        if (is_dir($path)) {
            $handle = opendir($path);
            while (false !== $file = readdir($handle)) {
                $ignore = ['.', '..', '.gitignore', '.DS_Store', 'Thumbs.db', '.gitkeep'];
                if (in_array($file, $ignore)) {
                    continue;
                }
                $fileinfo = [];
                $fileinfo['name'] = $file;
                if (is_dir($path.DS.$file)) {
                    $fileinfo['type'] = 'dir';
                    if (!$dir) {
                        $dirPath = $file;
                    } else {
                        $dirPath = $dir.DS.$file;
                    }
                    $fileinfo['path'] = $dirPath;
                    if ('' != $thumbPath) {
                        $fileinfo['children'] = self::readDirFiles($path.DS.$file, $thumbPath.DS.$file, $dirPath);
                    } else {
                        $fileinfo['children'] = self::readDirFiles($path.DS.$file, '', $dirPath);
                    }
                } else {
                    $fileinfo['type'] = 'file';
                    if ('' != $thumbPath) {
                        $fileinfo['thumb'] = $thumbPath.DS.$file;
                    }
                }
                $return[] = $fileinfo;
            }
            closedir($handle);
            sort($return);
        }

        return $return;
    }

    public static function createDir($dirName, $auth = 0755)
    {
        $dirPath = self::getDirPath($dirName);
        if (is_dir($dirPath)) {
            return true;
        }
        if (is_file($dirPath)) {
            return false;
        }

        return mkdir($dirPath, $auth, true);
    }

    public static function checkDir($dirName)
    {
        $dirPath = self::getDirPath($dirName);
        if (is_dir($dirPath)) {
            return true;
        }

        return false;
    }

    public static function delDir($dirName)
    {
        if (is_file($dirName)) {
            unlink($dirName);

            return true;
        }
        $dirPath = self::getDirPath($dirName);
        if (!is_dir($dirPath)) {
            return true;
        }
        foreach (glob($dirPath.'*') as $eFile) {
            is_dir($eFile) ? self::delDir($eFile) : unlink($eFile);
        }

        return @rmdir($dirName);
    }

    public static function getDirPath($dirName)
    {
        $formatName = preg_replace([
            '/\s+/',
            '/\/{2,}/',
        ], [
            '',
            '/',
        ], $dirName);

        return '/' == substr($formatName, -1) ? $formatName : $formatName.'/';
    }

    public static function down($file)
    {
        ob_start();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.filesize($file));
        ob_clean();
        flush();

        return readfile($file);
    }

    // 文件写入
    public static function write($url, $content)
    {
        $TxtFileName = $url;
        if (($TxtRes = fopen($TxtFileName, 'w+')) === false) {
            return [
                'status' => 0,
                'msg' => '路径出错',
            ];
        }
        $StrConents = $content;
        if (!fwrite($TxtRes, $StrConents)) {
            return [
                'status' => 0,
                'msg' => '写入失败',
            ];
        }
        fclose($TxtRes);

        return [
            'status' => 1,
            'msg' => '写入成功',
        ];
    }
}
