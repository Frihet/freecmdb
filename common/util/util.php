<?php

class util
{

	static $ci_html_title="";
	static $message_str="";
        static $redirect = false;
        static $path = "";
        
        function loadClass($name) 
        {
            
            if(class_exists($name)) {
                return;
            }
            
            if(strcasecmp(substr($name, strlen($name)-strlen("controller")),"controller" )==0) {
                include_once("controllers/{$name}.php");
                return;
            }
                        
            if(strcasecmp(substr($name, strlen($name)-strlen("plugin")),"plugin" )==0) {
                $dir_name = substr($name, 0, strlen($name)-strlen("plugin"));
                include_once("plugins/{$dir_name}/index.php");
                return;
            }
            
	}
        
        function array_to_set($arr)
        {
            $res = array();
            foreach($arr as $val) {
                $res[$val] = true;
            }
            return $res;
        }
        
        function date_format($date) 
        {
            if( !$date)
		return "";
            
            return date('Y-m-d', $date) . "&nbsp;" . date('H:i',$date);
        }

        function printGzippedPage() 
        {
            $accepted_encodings= $_SERVER['HTTP_ACCEPT_ENCODING'];
            if( headers_sent() ){
                $encoding = false;
            } else if( strpos($accepted_encodings, 'x-gzip') !== false ) {
                $encoding = 'x-gzip';
            } else if( strpos($accepted_encodings,'gzip') !== false ) {
                $encoding = 'gzip';
            } else{
                $encoding = false;
            }
            
            if( $encoding ) {
                $contents = ob_get_contents();
                ob_end_clean();
                header('Content-Encoding: '.$encoding);
                print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
                $size = strlen($contents);
                $contents = gzcompress($contents, 1);
                $contents = substr($contents, 0, $size);
                print($contents);
                exit();
            } else {
                ob_end_flush();
                exit();
            }
        }
        
        function setTitle($str)
        {
            util::$ci_html_title = $str;
        }
        
        function getTitle()
        {
            return util::$ci_html_title;
        }

        function rmdir($path) {
            $path= rtrim($path, '/').'/';
            $handle = opendir($path);
            for (;false !== ($file = readdir($handle));)
                if($file != "." and $file != ".." ) {
                    $fullpath= $path.$file;
                    if( is_dir($fullpath) ) {
                        rmdir_recurse($fullpath);
                    } else {
                        unlink($fullpath);
                    }
                }
            closedir($handle);
            rmdir($path);
        } 

        function makePager($msg_count, $page_var='page') 
        {
            
            $current_page = param($page_var, 1);
            $item_count = Property::get('pager.itemsPerPage', 20);
            
            $pages = floor(($msg_count-1)/$item_count)+1;
            
            if ($pages > 1) {
                
                if($current_page != '1') {
                    $pager .= "<a href='".makeUrl($page_var, null)."'>&#x226a;</a>&nbsp;&nbsp;";
                    $pager .= "<a href='".makeUrl(array($page_var=>$current_page-1))."'>&lt;</a>&nbsp;&nbsp;";
                }
                else {
                    $pager .= "&#x226a;&nbsp;&nbsp;&lt;&nbsp;&nbsp;";
                }
                
                for( $i=1; $i <= $pages; $i++) {
                    if($i == $current_page) {
                        $pager .= "$i&nbsp;&nbsp;";
                    }
                    else {
                        $pager .= "<a href='".makeUrl(array($page_var=>$i))."'>$i</a>&nbsp;&nbsp;";
                    }
                    
                }
                
                if($current_page != $pages) {
                    $pager .= "<a href='".makeUrl(array($page_var=>$current_page+1))."'>&gt;</a>&nbsp;&nbsp;";
                    $pager .= "<a href='".makeUrl(array($page_var=>$pages))."'>&#x226b;</a>&nbsp;&nbsp;";
                }
                else {
                    $pager .= "&gt;&nbsp;&nbsp;&#x226b;&nbsp;&nbsp;";
                }
            }
            return $pager;
        }

        
        function redirect($page=null) 
        {
            util::$redirect = $page;
        }
        
        
        function doRedirect() 
        {
            if(util::$redirect === false) {
                return;
            }
            $page = util::$redirect;
            
            global $start_time;
            if (!$page) {
        $page = "?";
        
            }
            unset($_REQUEST['message_str']);
            
            $stop_time = microtime(true);
            $page .= strchr($page, '?')!==false?'&':'?';
            $page .= "redirect_render_time=" . sprintf("%.4f",$stop_time-$start_time);
            
            if (messageGet()) {
                $page .= "&message_str=" . urlEncode(messageGet()) ;
            }
            
            $page .= "&redirect_query_time=" . sprintf("%.4f",db::$query_time);
            $page .= "&redirect_query_count=" . db::$query_count;
            
            header("Location: $page");
            exit(0);
            
        }

        function getPath()
        {
            return self::$path;
        }

        function removeMagicQuotes() 
        {
            if (get_magic_quotes_gpc()) {
                $_REQUEST = util_stripslashesDeep($_REQUEST);
                $_GET = util_stripslashesDeep($_GET);
                $_POST = util_stripslashesDeep($_POST);
            }
        }

}

function util_stripslashesDeep($value)
{
    return is_array($value) ? array_map('util_stripslashesDeep', $value) :stripslashes($value);
}

function sprint_r($var)
{
    ob_start();
    print_r($var);
    $res = ob_get_contents();
    ob_end_clean();
    return $res;
}




function htmlEncode($str,$qt=ENT_QUOTES) 
{
    return htmlEntities($str, $qt, 'UTF-8');
}

function param($name, $default=null) 
{
    if(array_key_exists($name, $_REQUEST)) {
        return $_REQUEST[$name];
    }
    return $default;
}

function error($str, $log=true) 
{
    if ($log)
        logMessage("Error: $str");
    
    $fmt = "<div class='error'>Error: ".htmlEncode($str)."</div>";
    util::$message_str .= $fmt;
}

function message($str, $log=true) 
{
    if ($log)
        logMessage($str);
    
    $fmt = "<div class='message'>".htmlEncode($str)."</div>";
    util::$message_str .= $fmt;
}

function messageGet()
{
    if (array_key_exists('message_str', $_REQUEST)) {
	    return $_REQUEST['message_str'] . util::$message_str;
    }
    return util::$message_str;
}



function makeUrl($v1=null, $v2=null) 
{
    if(is_array($v1)) {
        $res = $v1;
        
    }
    else {
        if($v1===null) {
            $res = array();
        }
        else {
            $res = array($v1=>$v2);
        }
    }

    $strip = false;
    
    if($res['controller'] != null) {
        $strip = true;
    }

    $filter = array( 'message_str'=>true, 'filter_column'=>true, 'filter_column_value'=>true, 'redirect_render_time'=>true, 'redirect_query_time'=>true, 'redirect_query_count'=>true);

    if (!$strip) {
        foreach($_GET as $key => $value) {
            if (array_key_exists($key, $filter)) {
                continue;
            }
            
            if (!array_key_exists($key, $res) ) {
                $res[$key] = $value;
            }
        }
    }
    
    $base = util::getPath();
    
    $controller = $res['controller'];
    $id = $res['id'];
    $task = $res['task'];
    if(false) {
        if( $controller !== null) {
            if ($id !== null) {
                if ($task !== null) {
                    
                    $res['task']=null;
                    $base .= urlEncode($controller)."/".urlEncode($id). "/".urlEncode($task);
                }
                else {
                    
                    $base .= urlEncode($controller)."/".urlEncode($id);
                }
                
                $res['id']=null;
            }
            else {
                $base .= urlEncode($controller);                
            }
            
            $res['controller']=null;
        }
    }
    

    $val = array();
    foreach($res as $key => $value) 
    {
        if ($value !== null) {
            $val[] = urlEncode($key) . "=" . urlEncode($value);
        }
    }
    $str = implode("&", $val);
    
    if (strlen($str)==0) {
        return $base;
    }
    
    return $base . "?" . $str;
}

function makeLink($arr, $txt, $class=null, $mouseover=null, $attribute=array()) 
{
    $mouseover_str = "";
    $onclick_str = "";
    
    if ($mouseover) {
        $class .= " mouseoverowner";
        $mouseover_str = "<div class='onmouseover'>\n$mouseover\n</div>";
        
    }
        
    $attribute_str = "";
    foreach($attribute as $key => $value) {
        $attribute_str .= htmlEncode($key)."=\"".htmlEncode($value,ENT_COMPAT)."\"";
    }
    

    $class_str = $class?"class='$class'":"";

    if (is_array($arr)) {
        $arr = makeUrl($arr);
    }
    
    
    
    return "<a $class_str href='$arr'  $attribute_str>$mouseover_str" . htmlEncode($txt) . "</a>\n";
}


function makePopup($title, $label, $content, $class= null, $onmouseover=null, $id=null) 
{
    if( $id == null ) {
        global $popup_id;
        $popup_id++;
        $id = "popup_$popup_id";
    }
    
    return makeLink("javascript:popupShow(\"$id\");", $label, $class, $onmouseover) ."
    <div class='anchor'>
    <div class='popup' id='$id'>
    <div class='popup_title'>
    $title
    <a href='javascript:popupHide(\"$id\")'>x</a>
    </div>
    <div class='popup_content'>
$content
    </div>
    </div>
    </div>
";
     
}

function logMessage()
{
    
}

util::removeMagicQuotes();

?>