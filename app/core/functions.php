<?php

/** Path to this file (index.php file) **/
defined('ROOTPATH') OR exit('Access Denied');

/** Check which PHP extensions are required **/
check_extensions();

function check_extensions() {

    $required_extensions = [
        'gd',
        'mysqli',
        'pdo_mysql',
        'pdo_sqlite',
        'curl',
        'fileinfo',
        'intl',
        'mbstring',
        'exif',
    ];

    $not_loaded = [];

    foreach($required_extensions as $ext) {

        if(!extension_loaded($ext)) {
            $not_loaded[] = $ext;
        }
    }

    if(!empty($not_loaded)) {
        
        dd("Please load the following extensions in your php.ini file: <br>".implode("<br>", $not_loaded));
        die;
    }
}

function show($param) {

    echo "<pre>";
    print_r($param);    
    echo "</pre>";

}

function esc($str) {
    return htmlspecialchars($str);
}

function redirect($path) {
    header("Location: " . ROOT . "/" . $path);
    die;
}

function dd($param) {
    echo "<pre>";
    var_dump($param);
    echo "</pre>";

    die();
}

function abort($code = 404) {
    http_response_code($code);

    require ROOT."/app/views/{$code}.php";

    die();
}

// load if image if it exists otherwise load placeholders
function get_image(mixed $file = '', string $type = 'post'):string {
    
    $file = $file ?? '';
    if(file_exists($file)) {
        return ROOT . '/' . $file;
    }

    if($type == 'user') {
        return ROOT . '/assets/images/user.webp';
    } else {
        return ROOT . '/assets/images/no_image.jpg';
    }
}

// returns pagination links
function get_pagination_vars():array {

    $vars = [];
    $vars['page'] = $_GET['page'] ?? 1;
    $vars['page'] = (int)$vars['page'];
    $vars['prev_page'] = $vars['page'] <= 1 ? 1 : $vars['page'] - 1;
    $vars['next_page'] = $vars['page'] + 1;

    return $vars;
}

// saves or displays a saved message to the user
function message(string $msg = null, bool $clear = false) {

    $ses = new Core\Session();

    if(!empty($msg)) {
        $ses->set('message', $msg);
    } else 
    if(!empty($ses->get('message'))) {

        $msg = $ses->get('message');

        if($clear) {
            $ses->pop('message');
        }
        return $msg;
    }

    return false;
}

/** returns URL Variables **/
function URL($key) {

    $URL = $_GET['url'] ?? 'home';
    $URL = explode("/", trim($URL,"/"));

    switch ($key) {
        case 'page':
        case 0:
            return $URL[0] ?? null;
            break;
        case 'section':
        case 'slug':
        case 1:
            return $URL[1] ?? null;
            break;
        case 'action':
        case 2:
            return $URL[2] ?? null;
            break;
        case 'id':
        case 3:
            return $URL[3] ?? null;
            break;
        default:
            return null;
            break;
    }
}

/** old values. Displays old input values after a page refresh **/
// for checkboxes 
function old_checked(string $key, string $value, string $default = ""):string {

    if(isset($_POST[$key])) {

        if($_POST[$key] == $value) {
            return ' checked ';
        }
    } else {

        if($_SERVER['REQUEST METHOD'] == "GET" && $default == $value) {
            return ' checked ';
        }
    }

    return '';
}

//for input boxes
function old_value(string $key, mixed $default = "", string $mode = "post"):mixed {

    $POST = ($mode == 'post') ? $_POST : $_GET;
    if(isset($POST[$key])) {
        return $POST[$key];
    }
    
    return $default;
}

// for selection boxes
function old_select(string $key, mixed $value, mixed $default = "", string $mode = 'post'):mixed {

    $POST = ($mode == 'post') ? $_POST : $_GET;
    if(isset($POST[$key])) {
        if($POST[$key] == $value) {
            return " selected ";
        }
    } else 
    
    if($default == $value) {
        return " selected ";
    }

    return "";
}

/** returns a user-readable date  
 * 2023 02 19 --> 19th Feb, 2023**/
function get_date($date) {

    return date("jS M Y", strtotime($date));
}


/**
 * Image functions
 */
/** converts image paths from relative to absolute path **/
function add_root_to_images($contents) {

    preg_match_all('/<img[^>]+>/', $contents, $matches);
    if(is_array($matches) && count($matches) > 0) {

        foreach ($matches[0] as $match) {

            preg_match('/src="[^"]+/', $match, $matches2);
            if(!strstr($matches2[0], 'http')) {

                $contents = str_replace($matches2[0], 'src="' . ROOT . '/' . str_replace('src="', "", $matches2[0]), $contents);
            }
        }
    }

    return $contents;
}

/** Converts images from text editor content to actual files */
function remove_images_from_content($content, $folder = "uploads/") {

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
        file_put_contents($folder."index.php", "Access Denied!");
    }

    // remove images from content
    preg_match_all('/<img[^>]+>/', $content, $matches);
    $new_content = $content;

    if(is_array($matches) && count($matches) > 0) {

        $image_class = new \Model\Image();
        foreach ($matches[0] as $match) {

            if(strstr($match, "http")) {

                // ignore images with links already
                continue;
            }

            // get the src
            preg_match('/src="[^"]+/', $match, $matches2);

            // get the filename
            preg_match('/data-filename="[^\"]+/', $match, $matches3);

            if(strstr($matches2[0], 'data:')) {

                $parts = explode(",", $matches2[0]);
                $basename = $matches3[0] ?? 'basename.jpg';
                $basename = str_replace('data-filename="', "", $basename);

                $filename = $folder . "img_" . sha1(rand(0,9999999999)) . $basename;

                $new_content = str_replace($parts[0] . "," . $parts[1], 'src="' . $filename, $new_content);

                //resize image
                $image_class->resize($filename, 1000);
            }
        }
    }

    return $new_content;
}

/** delete images from text editor content */
function delete_images_from_content(string $content, string $content_new = ''):void {

    // delete images from content
    if(empty($content_new)) {
        
        preg_match_all('/<img[^>]+>/', $content, $matches);

        if(is_array($matches) && count($matches) > 0) {
            foreach ($matches[0] as $match) {

                preg_match('/src="[^"]+/', $match, $matches2);
                $matches2[0] = str_replace('src="', "", $matches2[0]);

                if(file_exists($matches2[0])) {
                    unlink($matches2[0]);
                }
            }
        }
    } else {

        // compare old to new and delete from the old what is not in the new
        preg_match_all('/<img[^>]+>/', $content, $matches);
        preg_match_all('/<img[^>]+>/', $content_new, $matches_new);

        $old_images = [];
        $new_images = [];

        /** collect old images **/
        if(is_array($matches) && count($matches) > 0) {
            foreach ($matches[0] as $match) {

                preg_match('/src="[^"]+/', $match, $matches2);
                $matches2[0] = str_replace('src="', "", $matches2[0]);

                if(file_exists($matches2[0])) {
                    $old_images[] = $matches2[0];
                }
            }
        }

        /** collect new images **/
        if(is_array($matches_new) && count($matches_new) > 0) {
            foreach ($matches_new[0] as $match) {

                preg_match('/src="[^"]+/', $match, $matches2);
                $matches2[0] = str_replace('src="', "", $matches2[0]);

                if(file_exists($matches2[0])) {
                    $new_images[] = $matches2[0];
                }
            }
        }

        /** compare and delete all that don't appear in the new array **/
        foreach ($old_images as $img) {

            if(!in_array($img, $new_images)) {

                if(file_exists($img)) {
                    unlink($img);
                }
            }
        }
    }
}