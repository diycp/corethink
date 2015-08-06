<?php
// +----------------------------------------------------------------------
// | CoreThink [ Simple Efficient Excellent ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.corethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: jry <598821125@qq.com> <http://www.corethink.cn>
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
use Think\Storage;
/**
 * 主题模型
 * @author jry <598821125@qq.com>
 */
class SystemThemeModel extends Model{
    /**
     * 自动验证规则
     * @author jry <598821125@qq.com>
     */
    protected $_validate = array(
        array('name', 'require', '主题名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '该主题已存在', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '主题标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('description', 'require', '主题描述不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('developer', 'require', '主题开发者不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('version', 'require', '主题版本不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * 自动完成规则
     * @author jry <598821125@qq.com>
     */
    protected $_auto = array(
        array('current', '0', self::MODEL_INSERT),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
        array('utime', NOW_TIME, self::MODEL_BOTH),
        array('sort', '0', self::MODEL_INSERT),
        array('status', '1', self::MODEL_INSERT),
    );

    /**
     * 获取主题列表
     * @param string $addon_dir
     * @author jry <598821125@qq.com>
     */
    public function getAll(){
        //获取Home主题所有主题（文件夹下必须有corethink.php）
        $path = APP_PATH.'Home/View/';
        $dirs = array_map('basename', glob($path.'*', GLOB_ONLYDIR));
        foreach($dirs as $dir){
            $config_file = realpath($path.$dir).'/corethink.php';
            if(Storage::has($config_file)){
                $theme_dir_list[] = $dir;
                $temp_arr = include $config_file;
                $temp_arr['info']['status'] = -1; //未安装
                $theme_list[$temp_arr['info']['name']] = $temp_arr['info'];
            }
        }

        //获取系统已经安装的主题信息
        if($theme_dir_list){
            $map['name'] = array('in', $theme_dir_list);
        }else{
            return false;
        }
        $installed_theme_list = $this->where($map)->field(true)->order('sort asc,id desc')->select();
        if($installed_theme_list){
            foreach($installed_theme_list as $theme){
                $theme_list[$theme['name']] = $theme;
            }
            //系统已经安装的主题信息与文件夹下主题信息合并
            $theme_list = array_merge($theme_list, $theme_list);
        }

        foreach($theme_list as &$val){
            switch($val['status']){
                case '-1': //未安装
                    $val['status'] = '<i class="fa fa-download-alt" style="color:green"></i>';
                    $val['right_button']  = '<a class="label label-success ajax-get" href="'.U('install?name='.$val['name']).'">安装</a>';
                    break;
                default :
                    $val['status'] = '<i class="fa fa-check" style="color:green"></i>';
                    if($val['current']){
                        $val['right_button'] .= '<span class="label label-success" href="#">我是当前主题</span> ';
                    }else{
                        $val['right_button'] .= '<a class="label label-danger ajax-get" href="'.U('setStatus', array('status' => 'current', 'ids' => $val['id'])).'">设为当前主题</a> ';
                    }
                    $val['right_button'] .= '<a class="label label-info ajax-get" href="'.U('updateInfo?id='.$val['id']).'">更新信息</a> ';
                    $val['right_button'] .= '<a class="label label-danger ajax-get" href="'.U('setStatus', array('status' => 'uninstall', 'ids' => $val['id'])).'">卸载</a> ';
                    break;
            }
        }
        return $theme_list;
    }
}
