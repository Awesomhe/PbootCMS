<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @license This is not a freeware, use is subject to license terms
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2018年8月14日
 *  
 */
namespace app\admin\controller\system;

use core\basic\Controller;
use core\basic\Model;

class UpgradeController extends Controller
{

    // 服务器地址
    public $server = 'https://www.pbootcms.com';

    // 发布目录
    public $release = '/release';

    // 文件列表
    public $files = array();

    public function __construct()
    {
        set_time_limit(0);
    }

    public function index()
    {
        switch (get('action')) {
            case 'local':
                $upfile = $this->local();
                break;
            default:
                $upfile = array();
        }
        $this->assign('upfile', $upfile);
        $this->display('system/upgrade.html');
    }

    // 检查更新
    public function check()
    {
        // 清理目录，检查下载目录及备份目录
        path_delete(RUN_PATH . '/upgrade', true);
        check_dir(RUN_PATH . '/upgrade', true);
        check_dir(DOC_PATH . STATIC_DIR . '/backup/upgrade', true);
        
        $files = $this->getServerList();
        if (! is_array($files)) {
            json(0, $files);
        } else {
            $db = get_db_type();
            foreach ($files as $key => $value) {
                $file = ROOT_PATH . $value->path;
                if (md5_file($file) != $value->md5) {
                    // 筛选数据库更新脚本
                    if (preg_match('/([\w]+)-([\w\.]+)-update\.sql/i', $file, $matches)) {
                        if ($matches[1] != $db || ! $this->compareVersion($matches[2], APP_VERSION . '.' . RELEASE_TIME)) {
                            continue;
                        }
                    }
                    if (file_exists($file)) {
                        $files[$key]->type = '<span style="color:Red">覆盖</span>';
                    } else {
                        $files[$key]->type = '新增';
                    }
                    $files[$key]->ctime = date('Y-m-d H:i:s', $files[$key]->ctime);
                    $upfile[] = $files[$key];
                }
            }
            if (! $upfile) {
                json(0, '您的系统无任何文件需要更新！');
            } else {
                json(1, $upfile);
            }
        }
    }

    // 执行下载
    public function down()
    {
        if ($_POST) {
            if (! ! $list = post('list')) {
                if (! is_array($list)) { // 单个文件转换为数组
                    $list = array(
                        $list
                    );
                }
                $len = count($list) ?: 0;
                foreach ($list as $value) {
                    $path = RUN_PATH . '/upgrade' . $value;
                    $types = '.gif|.jpeg|.png|.bmp|.jpg|'; // 定义执行下载的类型
                    $ext = end(explode(".", basename($path))); // 扩展
                    if (preg_match('/\.' . $ext . '\|/i', $types)) {
                        if (! $this->getServerDown($value, $path)) {
                            if ($len == 1) {
                                $this->log("更新文件 $value 下载失败!");
                                json(0, "更新文件 $value 下载失败!");
                            }
                        } else {
                            if ($len == 1) {
                                json(1, "更新文件 " . basename($value) . " 下载成功!");
                            }
                        }
                    } else {
                        $result = $this->getServerFile($value);
                    }
                    if ($result) {
                        check_dir(dirname($path), true);
                        if (! file_put_contents($path, $result)) {
                            if ($len == 1) {
                                $this->log("更新文件 $value 下载失败!");
                                json(0, "更新文件 $value 下载失败!");
                            }
                        } else {
                            if ($len == 1) {
                                json(1, "更新文件 " . basename($value) . " 下载成功!");
                            }
                        }
                    }
                }
                if ($len > 1) {
                    json(1, "更新文件 全部下载成功!");
                }
            } else {
                json(0, '请选择要下载的文件！');
            }
        } else {
            json(0, '请使用POST提交请求！');
        }
    }

    // 执行更新
    public function update()
    {
        if ($_POST) {
            if (! ! $list = post('list')) {
                $list = explode(',', $list);
                $backdir = date('YmdHis');
                
                // 更新文件
                foreach ($list as $value) {
                    if (stripos($value, '/script/') !== false) {
                        $sqls[] = $value;
                    } else {
                        $path = RUN_PATH . '/upgrade' . $value;
                        $des_path = ROOT_PATH . $value;
                        $back_path = DOC_PATH . STATIC_DIR . '/backup/upgrade/' . $backdir . $value;
                        check_dir(dirname($des_path), true);
                        check_dir(dirname($back_path), true);
                        if (file_exists($des_path)) { // 执行备份
                            copy($des_path, $back_path);
                        }
                        if (! copy($path, $des_path)) {
                            $this->log("文件 " . $value . " 更新失败!");
                            json(0, "文件 $value 更新失败,请重试!");
                        }
                    }
                }
                
                // 更新数据库
                if (isset($sqls)) {
                    sort($sqls);
                    foreach ($sqls as $value) {
                        $path = RUN_PATH . '/upgrade' . $value;
                        $des_path = ROOT_PATH . $value;
                        check_dir(dirname($des_path), true);
                        $sql = file_get_contents($path);
                        if (! $this->upsql($sql)) {
                            $this->log("数据库 $value 更新失败!");
                            json(0, "数据库 $value 更新失败！");
                        }
                    }
                }
                
                $this->log("系统更新成功!");
                path_delete(RUN_PATH . '/upgrade', true);
                json(1, '系统更新成功！');
            } else {
                json(0, '请选择要更新的文件！');
            }
        }
    }

    // 缓存文件
    private function local()
    {
        $files = $this->getLoaclList(RUN_PATH . '/upgrade');
        $files = json_decode(json_encode($files));
        foreach ($files as $key => $value) {
            $file = ROOT_PATH . $value->path;
            if (file_exists($file)) {
                $files[$key]->type = '<span style="color:Red">覆盖</span>';
            } else {
                $files[$key]->type = '新增';
            }
            $files[$key]->ctime = date('Y-m-d H:i:s', $files[$key]->ctime);
            $upfile[] = $files[$key];
        }
        return $upfile;
    }

    // 执行更新数据库
    private function upsql($sql)
    {
        $sql = explode(';', $sql);
        $model = new Model();
        foreach ($sql as $value) {
            $value = trim($value);
            $model->amd($value);
        }
        return true;
    }

    // 获取列表
    private function getServerList()
    {
        $url = $this->server . '/index.php/upgrate/getlist/version/' . APP_VERSION . '.' . RELEASE_TIME;
        if (! ! $rs = get_url($url, '', '', true)) {
            $rs = json_decode($rs);
            if ($rs) {
                return $rs->data;
            } else {
                $this->log('连接更新服务器错误，请稍后再试！');
                return '连接更新服务器错误，请稍后再试！';
            }
        } else {
            return '连接更新服务器错误，请稍后再试！';
        }
    }

    // 获取文件
    private function getServerFile($path)
    {
        $url = $this->server . '/index.php/upgrate/getFile';
        $data['path'] = $path;
        if (! ! $rs = get_url($url, $data, '', true)) {
            $rs = json_decode($rs);
            if ($rs->code) {
                return base64_decode($rs->data);
            } else {
                alert_back($rs->data);
            }
        } else {
            $this->log('获取更新文件' . $path . '时发生服务器错误!');
            error('获取更新文件' . $path . '时发生服务器错误!');
        }
    }

    // 获取非文本文件
    private function getServerDown($source, $des)
    {
        $url = $this->server . $this->release . $source;
        if (! ! $sfile = fopen($url, "rb")) {
            if (! ! $cfile = fopen($des, "wb")) {
                while (! feof($sfile)) {
                    $fwrite = fwrite($cfile, fread($sfile, 1024 * 8), 1024 * 8);
                    if ($fwrite === false) {
                        return false;
                    }
                }
                return true;
            }
        }
        if ($sfile) {
            fclose($sfile);
        }
        if ($cfile) {
            fclose($cfile);
        }
        return false;
    }

    // 获取文件列表
    private function getLoaclList($path)
    {
        $files = scandir($path);
        foreach ($files as $value) {
            if ($value != '.' && $value != '..') {
                if (is_dir($path . '/' . $value)) {
                    $this->getLoaclList($path . '/' . $value);
                } else {
                    $file = $path . '/' . $value;
                    
                    // 避免中文乱码
                    if (! mb_check_encoding($file, 'utf-8')) {
                        $out_path = mb_convert_encoding($file, 'UTF-8', 'GBK');
                    } else {
                        $out_path = $file;
                    }
                    
                    $out_path = str_replace(RUN_PATH . '/upgrade', '', $out_path);
                    
                    $this->files[] = array(
                        'path' => $out_path,
                        'md5' => md5_file($file),
                        'ctime' => filectime($file)
                    );
                }
            }
        }
        return $this->files;
    }

    // 比较程序本号
    private function compareVersion($sv, $cv)
    {
        if (empty($sv) || $sv == $cv) {
            return 0;
        }
        $sv = explode('.', $sv);
        $cv = explode('.', $cv);
        $len = count($sv) > count($cv) ? count($sv) : count($cv);
        for ($i = 0; $i < $len; $i ++) {
            $n1 = $sv[$i] or 0;
            $n2 = $cv[$i] or 0;
            if ($n1 > $n2) {
                return 1;
            } elseif ($n1 < $n2) {
                return 0;
            }
        }
        return 0;
    }
}