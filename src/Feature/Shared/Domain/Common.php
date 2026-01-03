<?php
namespace App\Feature\Shared\Domain;

use App\Feature\Helper\DateHelper;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Intl\Countries;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Yaml\Yaml;

class Common{
    public function __construct(private readonly string|null $appId= null){}

    public static function downloadPDF($file,$name = null): void
    {
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; '. ($name ? 'filename="'.self::slug($name).'.pdf"' : ''));
        readfile($file);
    }

	public static function generatePassword($n = 10): string
    {
		$r = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$r = str_shuffle($r);
		$r = uniqid($r,true);
		$r = substr($r,0,$n);
		return $r;
	}

	public static function generateToken($n = 30): string
    {
		return bin2hex(random_bytes($n));
	}

	public static function generateCode($n = 5): string
    {
		$r = "0123456789";
		$r = str_shuffle($r);
		$r = uniqid($r,true);
		$r = substr($r,0,$n);
		return $r;
	}

	public static function generateName(): string
    {
		$generateName = microtime();
		$generateNameTable = preg_split('/[\s,\.]/',$generateName);
		$generateName = $generateNameTable[0].$generateNameTable[1].$generateNameTable[2];
		return $generateName;
	}

    public static function transliterateString($txt): array|string
    {
        $transliterationTable = array('œ' => 'oe', 'á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
        return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
    }

    public static function slug($text){
        $t1 = "/\,|;|\?|!|:|\(|\)|\[|\]|\{|\}|'|’| |«|»|°|\*|\+|=|\\|\//";
        $t2 = "/-+((.{1,3}|pour)-+((.{1,3}|pour)-+)*)/";
        $slug = preg_replace(["/\s+/",$t1,$t2,"/-+/"],"-",strtolower(Common::transliterateString($text)));

        $slugger = new AsciiSlugger();
        return $slugger->slug($slug)->lower()->toString();
    }

    public static function isMobile(){
        $isMobile = $_SESSION['isMobile'] ?? null;

        if($isMobile !== null && !isset($_GET['force']))
            return $isMobile;

        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['isMobile'] = (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)
        ||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));

        return $_SESSION['isMobile'];
    }

    public static function isDesktop(){
        return !self::isMobile();
    }

    public static function ellipse($value, $length= 200)
    {
        if (strlen($value) > $length) {
            $value = substr($value, 0, $length) . '...';
        }

        return $value ?? "";
    }

    public  function mobile($s){
        return self::isMobile() ? $s : "";
    }

    public  function desktop($s){
        return self::isMobile() ? "" : $s;
    }

    public static function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";
		$ub = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }
        elseif (preg_match('/android/i', $u_agent)) {
            $platform = 'android';
        }
        elseif (preg_match('/ios/i', $u_agent)) {
            $platform = 'ios';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                if(count( $matches['version'])>1)
					$version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }

    public static function FrDate($date, $isLong = false){
        if($date instanceof \DateTimeInterface){
            return DateHelper::formatDate($date);
        }

        return $date;
    }

    public static function FrDateTime($date, $isLong = false){
        if($date instanceof \DateTimeInterface){
            return DateHelper::format($date);
        }

        return $date;
    }

    public static function strftime(\DateTimeInterface $date, $format){
        setlocale(LC_TIME, "fr_FR.utf8");
        return strftime($format, $date->getTimestamp());
    }

    public static function FrMonth(\DateTimeInterface $date, $isLong = false){
        setlocale(LC_TIME, "fr_FR.utf8");
        return strftime($isLong ? "%B" : "%b", $date->getTimestamp());
    }

    public static function FrDay(\DateTimeInterface $date, $isLong = false){
        setlocale(LC_TIME, "fr_FR.utf8");
        return strftime($isLong ? "%A" : "%a", $date->getTimestamp());
    }

    public function prettyDate(\DateTimeInterface $date, $isLongDate = false){
        $now = new \DateTime;
        $now->setTime(0,0,0);
        $i = ($now > $date ? -1 : 1) * $date->diff($now)->days;
        return $i >= -7
            ? $i >= -6
            ? $i >= -2
            ? $i >= -1
            ? $i >= 0
            ? $i >= 1
            ? $i >= 2
            ? $i >= 3
            ? $i >= 5
            ? self::FrDate($date,$isLongDate)
            : 'Dans 3 jours'
            : 'Après-demain'
            : 'Demain'
            : 'Aujourd\'hui'
            : 'Hier'
            : 'Avant-hier'
            : 'Il y a '.-$i.' jours'
            : 'Il y a une semaine'
            :  self::FrDate($date,$isLongDate)
        ;
    }

    public function prettyDateTime(\DateTime $date, $isLongDate = false){
        $now = new \DateTime;
        return $date >= $now->modify('-1 day')
            ? $date >= $now->modify('-1 day')
            ? $date = $now
            ? 'Aujourd\'hui à '.$date->format('H:i')
            : 'Hier à '.$date->format('H:i')
            : 'Avant-hier à '.$date->format('H:i')
            : self::FrDateTime($date,$isLongDate)
        ;
    }

    public static function resizeImage($file, $width = 1520, $height = 1520){
        $file = urldecode($file);
        if(is_file($file)) {
            $size = ['width'=>0,'height'=>0];
            if(!class_exists('Imagick')){
                return $size;
            }

            list($size['width'], $size['height']) = getimagesize(realpath($file));
            if($size['width'] > 600 || $size['height'] > 600){
                $imagick = new \Imagick(realpath($file));
                $imagick->setImageCompressionQuality(75);
                $imagick->thumbnailImage($width, $width, 1, false);
                $imagick->writeimage(realpath($file));
                $size = [$imagick->getImageWidth(), $imagick->getImageHeight()];
                $imagick->clear();
                $imagick->destroy();
            }
            return $size;
        }
        else
            throw new Exception("Veuillez choisir une image valide et réessayer => ".$file);
    }

    public static function thumbnailImage($file, $target, $quality = 60){
        $file = urldecode($file);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $filename = pathinfo($file, PATHINFO_FILENAME);
        if (is_file($file)) {
            if(!class_exists('Imagick')){
                return false;
            }

            if($extension == 'svg') return true;

            $imagick = new \Imagick(($file));
            if($imagick->getImageWidth() > 600 || $imagick->getImageHeight() > 600){
                $imagick->setImageFormat(strtolower($extension));
                $imagick->setImageCompressionQuality($quality);
                $imagick->thumbnailImage(600, 600, 1, false);
            }
            $imagick->writeimage(realpath($target).'/'.$filename.'.'.$extension);
            $imagick->clear();
            $imagick->destroy();
            return true;
        }
        else
            throw new Exception("Veuillez choisir une image valide et réessayer.");
    }

    public static function thumbnailImage2($file, $target){
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $filename = strtolower(pathinfo($file, PATHINFO_FILENAME));

        $realSize = getimagesize($file);
        $image = null;

        if($extension == 'jpg' || $extension == 'jpeg') $image = imagecreatefromjpeg($file);
        else if($extension == 'png') $image = imagecreatefrompng($file);
        else if ($extension == 'gif') $image = imagecreatefromgif($file);
        else return false;

        $target = $target ? $target : './upload/images/articles/thumbnails/';

        $width = 600;
        $height = ($width / $realSize[0]) * $realSize[1];
        $top = $left = $coef = 0;

        $newImage = imagecreatetruecolor($width,$height);
        $coef = min($realSize[0]/$width,$realSize[1]/$height);
        $deltax = $realSize[0]-($coef * $width);
        $deltay = $realSize[1]-($coef * $height);

        imagecopyresampled($newImage,$image,0,0,$deltax/2,$deltay/2,$width,$height,$realSize[0]-$deltax,$realSize[1]-$deltay);
        $fullpath = $target.$filename.'.'.$extension;

        if($extension == 'jpg' || $extension == 'jpeg') imagejpeg($newImage,$fullpath);
        else if($extension == 'png') imagepng($newImage,$fullpath);
        else if ($extension == 'gif') imagegif($newImage,$fullpath);

        $imagevariable = ob_get_contents();
        imagedestroy($image);
        imagedestroy($newImage);
        return $fullpath;
    }

	public static function prettySize($size){
		$bytes = null;
		if ($size >= 1073741824)
			$bytes = round($size / 1073741824) . ' Go';
		elseif ($size >= 1048576)
			$bytes = round($size / 1048576) . ' Mo';
		elseif ($size >= 1000)
			$bytes = round($size / 1024) . ' Ko';
		else
			$bytes = $size . ' O';
		return $bytes;
	}

    public static function idToTweet($username,$id){
		return "https://twitter.com/".$username."/status/".$id;
	}

	public static function idToPost($username,$id){
		return "https://www.facebook.com/".$username."/posts/".$id;
	}

	public static function postToId($post){
		$post = explode('/',$post);
		if(preg_match('/^\d+$/i',$post[count($post)-1]))
			return $post[count($post)-1];
		else
			return $post[count($post)-2];
	}

    // Code from https://stackoverflow.com/a/6121972
    public static function getYoutubeId($link){
        preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=embed/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $link, $id);
        return $id[0];
    }

    public static function getYoutubeEmbed($id){
        return 'https://youtube.com/embed/'.$id;
    }

    public static function getYoutubeThumbnail($link, $quality = 'l') {
        $video_id = getYoutubeId($link);

        if (!isset($video_id[1]))
            $video_id = explode("/embed/", $link);

        if (!isset($video_id[1]) || empty($video_id[1]))
            $video_id = explode("/v/", $link);

        if (!isset($video_id[1]) || empty($video_id[1]))
            return false;

        $video_id = explode("&", $video_id[1]);
        $id = $video_id[0];

        if ($id) {
            switch (strtolower(substr($quality, 0, 1)))
            {
                case 'l': //low
                return 'https://img.youtube.com/vi/'.$id.'/sddefault.jpg';

                case 'h': //high
                return 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg';

                case 'm': //maximum
                return 'https://img.youtube.com/vi/'.$id.'/maxresdefault.jpg';

                default:
                return 'https://img.youtube.com/vi/'.$id.'/mqdefault.jpg';
            }
        }
        return false;
    }

    public function imagesize($file){
        $size = ['width'=>0,'height'=>0];
        if($file){
            try{
                list($size['width'], $size['height']) = getimagesize($file);
            }catch (\Exception $e){}
        }
        return $size ;
    }

    public function filesize($file): bool|int
    {
        return file_exists($file) ? filesize($file) : false ;
    }

    public static function country($countryCode, $locale = "fr"): string
    {
        return Countries::getName($countryCode, $locale);
    }

    public function filterArray($list, $property, $s, $subProperty = null): array
    {
        if (is_array($list)){
            $m = "get".ucfirst($property);
            if($subProperty !== null){
                $subM = "get".ucfirst($subProperty);
                return array_filter($list,function($o) use(&$property, &$s, &$m, &$subProperty, &$subM){
                    $sub = is_object($o) && property_exists($o, $property) ? $o->$m() : null;
                    return is_object($sub) && property_exists($sub, $subProperty) ? $sub->$subM() == $s : false;
                });
            }
            else
                return array_filter($list,function($o) use(&$property, &$s, &$m){
                    if(is_object($o))
                        return property_exists($o, $property) ? $o->$m() == $s : false;
                    elseif(is_array($o))
                        return array_key_exists($property,$o) ? $o[$property] == $s : false;
                });

        }
        return [];
    }

    public function imageColor($file, $default="rgb(255, 255, 255)"){
        $explode = explode('.', $file);
        $extension = end($explode);

        if($extension){
            $img=null;
            switch (strtolower($extension)){
                case 'png':
                    $img = imagecreatefrompng($file);
                    break;
                case 'jpg':
                case 'jpeg':
                    $img = imagecreatefromjpeg($file);
                    break;
                case 'gif':
                    $img = imagecreatefromgif($file);
                    break;
                default:
                    break;
            }
            if($img){
                $rgb = imagecolorat($img, 1, 1);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                return "rgb(".$r.", ".$g.", ".$b.")";
            }
        }
        return $default;

    }

    public function getParameter($prop){
        if($this->appId){
            $params = Yaml::parseFile("../config/packages/app_config.".$this->appId.".yaml")["parameters"];
        }else{
            $params = Yaml::parseFile("../config/packages/app_config.yaml")["parameters"];
        }
        if(array_key_exists($prop, $params)){
            return $params[$prop];
        }

        return null;
	}

    // Code from https://stackoverflow.com/questions/13076480/
    // Returns a file size limit in bytes based on the PHP upload_max_filesize
    // and post_max_size
    function getUploadMaxFileSize() {
        static $max_size = -1;
        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = self::parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
              $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    function parseSize($size): float
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }

    public static function numberFormat(float $number): string
    {
        return number_format($number, 0, ',', ' ');
    }
}
