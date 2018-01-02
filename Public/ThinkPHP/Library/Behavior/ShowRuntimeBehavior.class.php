<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
/**
 * 系统行为扩展：运行时间信息显示
 */
class ShowRuntimeBehavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        if(C('SHOW_RUN_TIME')){
            if(false !== strpos($content,'{__NORUNTIME__}')) {
                $content   =  str_replace('{__NORUNTIME__}','',$content);
            }else{
                $runtime = $this->showTime();
                 if(strpos($content,'{__RUNTIME__}'))
                     $content   =  str_replace('{__RUNTIME__}',$runtime,$content);
                 else
                     $content   .=  '';
            }
        }else{
            $content   =  str_replace(array('{__NORUNTIME__}','{__RUNTIME__}'),'',$content);
        }
    }

    /**
     * 显示运行时间、数据库操作、缓存次数、内存使用信息
     * @access private
     * @return string
     */
    private function showTime() {
        // 显示运行时间
        G('beginTime',$GLOBALS['_beginTime']);
        G('viewEndTime');
        $showTime   =   '整体执行时间: '.G('beginTime','viewEndTime').'s ';
        if(C('SHOW_ADV_TIME')) {
            // 显示详细运行时间
            $showTime .= '( 加载:'.G('beginTime','loadTime').'s 初始化:'.G('loadTime','initTime').'s 执行:'.G('initTime','viewStartTime').'s 模板:'.G('viewStartTime','viewEndTime').'s )';
        }
        if(C('SHOW_DB_TIMES')) {
            // 显示数据库操作次数
            $showTime .= ' | [数据库:'.N('db_query').'次读操作 , '.N('db_write').' 次写操作] ';
        }
        if(C('SHOW_CACHE_TIMES')) {
            // 显示缓存读写次数
            $showTime .= ' | [缓存:'.N('cache_read').'次读取 , '.N('cache_write').' 次写入] ';
        }
        if(MEMORY_LIMIT_ON && C('SHOW_USE_MEM')) {
            // 显示内存开销
            $showTime .= ' | 使用内存:'. number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024).' kb';
        }
        if(C('SHOW_LOAD_FILE')) {
            $showTime .= ' | 加载文件:'.count(get_included_files());
        }
        if(C('SHOW_FUN_TIMES')) {
            $fun  =  get_defined_functions();
            $showTime .= ' | 函数调用:'.count($fun['user']).','.count($fun['internal']);
        }
        return $showTime;

    }
}
