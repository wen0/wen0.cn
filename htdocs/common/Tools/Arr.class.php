<?php
/**
 *  数组处理工具类
 *
 *  @author zsg <xxx@qq.com>
 */
namespace Tools;
/**
 *  数组处理工具类
 *
 *  @author zsg <xxx@qq.com>
 */
class Arr
{
    /**
     * 从数据集合中，获取某个字段的集合
     * 如：比如将用户集合数据中的uid提取出来
     * 
     * @param array  $data        数据集合
     * @param string $field       字段名
     * @param bool   $unique      是否去重
     * @param bool   $removeEmpty 是否去空（包括empty,null,0)
     * 
     * @return void
     */
    static public function getFieldInList($data, $field="uid", $unique=true, $removeEmpty=true)
    {
        $rs = array();
        foreach ($data as $v) {
            $val = $v[$field];
            if ($removeEmpty && !$val) {
                continue;
            }
            $rs[] = $v[$field];
        }
        
        if ($unique) {
            return array_values(array_unique($rs));
        } else {
            return $rs;
        }
    }
    
    /**
     * 将数据集合的键值变成集合中的某个字段
     * 
     * @param array  $data  集合
     * @param string $field 字段
     * 
     * @return type
     */
    static public function setDataAsKey($data, $field='id')
    {
        $rs = array();
        foreach ($data as $v) {
            $key = $v[$field];
            $rs[$key] = $v;
        }
        
        return $rs;
    }
    
    /**
     * 排序
     * 
     * @param array  $list   未知 
     * @param int    $field  未知
     * @param string $sortby 未知
     * 
     * @return array
     */
    static public function sortBy($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = array();
            foreach ($list as $i => $data) {
                $refer[$i] =& $data[$field];
            }    
            switch ($sortby) {
            case 'asc':
                asort($refer);
                break;
            case 'desc':
                arsort($refer);
                break;
            case 'nat':
                natcasesort($refer);
                break;
            }
            foreach ($refer as $key => $val) {
                $resultSet[] =& $list[$key];
                unset($val);
            }      
            return $resultSet;
        }
        return false;
    }
    
    /**
     * 按字符串长度排序
     * 
     * @param array  $list   数据列表
     * @param string $sortby 排序字符
     * 
     * @return void
     */
    static public function sortByLen($list, $sortby = 'asc')
    { 
        $F = create_function('$a, $b', 'return(strLen($a) '.($sortby == 'asc' ? '>' : '<').' strLen($b));');  
        usort($list, $F); 
        return $list;
    }
    
    /**
     * 过滤数组，只保留指定的字段
     * 
     * @param array        $array 数组
     * @param array|string $field 需要保留的字段，可以是数组，也可以是字符串，用逗号隔开
     * @param int          $level 数据维数，默认为一维数组，只支持1，2维
     * 
     * @return void
     */
    static public function filter($array, $field, $level = 1)
    {
        if ($level > 1) {
            foreach ($array as $k=>$v) {
                $array[$k] = self::filter($v, $field, 1);
            }
        } else {
            if (!is_array($field)) {
                $field = explode(',', $field);
            }
            $keys = array_diff(array_keys($array), $field);
            foreach ($keys as $keys) {
                unset($array[$keys]);
            }
        }
        return $array;
    }
    
    /**
     * 获取图片实际显示宽高
     * 
     * @param int $width  实际宽度
     * @param int $height 实际高度
     * @param int $maxW   最大宽度
     * @param int $maxH   最大高度
     * 
     * @return void
     */
    static public function getImageShowSize($width, $height, $maxW, $maxH)
    {
        $w = $width;
        $h = $height;
        
        if ($width > $maxW) {
            $w = $maxW;
            $h = $w * $height / $width;
            if ($h > $maxH) {
                return self::getImageShowSize($w, $h, $maxW, $maxH);
            }
        } else if ($height > $maxH) {
            $h = $maxH;
            $w = $h * $width / $height;
        }
        return array($w, $h);
    }
    
    /**
     * 解释属性值为数组
     * 
     * @param string $string 字符串
     * 
     * @return array
     */
    static public function parsePara($string)
    {
        $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
        if (strpos($string, ':')) {
            $value = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
    
    /**
     * 反转URL属性
     * 
     * @param string $url URL
     * 
     * @return void
     */
    static public function parseNav($url = "")
    {
        $static = array(0=>"", 1=>'info', 3=>'contact', 2=>'service', 4=>"case", 6=>'exp', 5=>'news', 7=>'ask');
        $index  = array_search($url, $static);
        $rs = null;
        if (false !== $index) {
            return array("first"=>1, "second"=>$index);
        } else if (preg_match("/^([a-z]+)_(\d+)/i", $url, $rs)) {
            $module = array(
                "article" => 2,
                "album"   => 4,
                "team"    => 5
            );
            return array("first"=>$module[$rs[1]], "second"=>$rs[2]);
        } else if (preg_match("/^p(\d+)/i", $url, $rs)) {  //单页
            return array("first"=>3, "second"=>$rs[1]);
        } else {
            return array("first"=>3, "second"=>$url);
        }
    }
    
    /**
     * 解释字段配置值为数组
     * 
     * @param string $string 字符串
     * 
     * @return array
     */
    static public function parseField($string)
    {
        $string = explode(",", $string);
        $field = array();
        foreach ($string as $string) {
            $field[] = explode("|", $string);
        }
        return $field;
    }
}