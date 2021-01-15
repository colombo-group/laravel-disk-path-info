<?php
/**
 * Created by PhpStorm.
 * User: hocvt
 * Date: 2019-09-19
 * Time: 12:53
 */

namespace Colombo\Libs\DiskPathTools;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;

class DiskPathInfo {
    
    private static $disks_priority = [];
    private static $info_separate = ':';
    private static $array_separate = ',';
    
    protected $disks = [];
    
    protected $path;
    
    protected $size = 0;
    
    protected $other_info = [];
    
    /**
     * DiskPathInfo constructor.
     *
     * @param array|string $disks
     * @param string $path
     * @param int $size
     * @param array $other_info
     */
    public function __construct( $disks, string $path, int $size = 0, array $other_info = [] )
    {
        if(!is_array( $disks )) {
            $disks = explode( self::$info_separate, $disks);
        }
        $this->disks = $disks;
        $this->path = $path;
        $this->size = $size;
        
        $this->info($other_info);
    }
    
    /**
     * get or set other_info
     * @param null $key
     * @param null $value
     *
     * @return array|\ArrayAccess|mixed
     */
    public function info($key = null, $value = null)
    {
        if ($key == null) {
            return $this->other_info;
        } elseif(is_array($key)) {
            foreach ($key as $k => $v){
                $this->info($k, $v);
                return $this->other_info;
            }
        } elseif ($value !== null) {
            return Arr::get( $this->other_info, $key );
        } else {
            return Arr::set( $this->other_info, $key, $value);
        }
    }
    
    /**
     * @return array|string
     */
    public function getDisks()
    {
        return $this->disks;
    }
    
    /**
     * @return string get best disk name
     * @throws \Exception
     */
    public function bestDisk()
    {
        return self::getBestDisk( $this->disks );
    }
    
    /**
     * @param $disks
     *
     * @return mixed|string
     * @throws \Exception
     */
    public static function getBestDisk($disks)
    {
        if(!is_array( $disks )){
            $disks = explode( self::$array_separate, $disks);
        }
        if(count( $disks ) == 0){
            throw new \Exception("No disk");
        }
        return $disks[0];
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    public function getUrl()
    {
        return \Storage::disk($this->bestDisk())->url($this->path);
    }
    
    /**
     * @param null $expiration
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    public function getTempUrl($expiration = null, array $options = []){
        if(!$expiration) {
            $expiration = now()->addMinutes(10);
        }
        return \Storage::disk($this->bestDisk())->temporaryUrl($this->path, $expiration, $options);
    }
    
    /**
     * @param array $disks
     * @param false $check
     * @param false $replace
     */
    public function addDisks(array $disks, $check = false, $replace = false){
        if($replace) {
            $this->disks = [];
        }
        if($check) {
            foreach ($disks as $disk){
                if( in_array($disk, $this->disks) && \Storage::disk($disk)->has( $this->getPath() )) {
                    $this->disks[] = $disk;
                }
            }
        }else{
            $this->disks = array_merge( $this->disks, $disks );
        }
    }
    
    /**
     * @return mixed
     */
    public function getPath() {
        return $this->path;
    }
    
    /**
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }
    
    /**
     * @param int $size
     */
    public function setSize( int $size ): void {
        $this->size = $size;
    }
    
    public function __toString() {
        $infos['disks'] = implode( self::$array_separate, $this->disks );
        $infos['path'] = $this->path;
        $infos['size'] = $this->size;
        if($pages = $this->info('page')){
            $infos['pages'] = implode( self::$array_separate, $pages );
        }
        return implode( self::$info_separate, $infos);
    }
    
    /**
     * @param $app_name
     * @param $file_version
     * @param null $suffix
     * @param null $prefix
     *
     * @return string|string[]|null
     */
    public static function makePath($app_name, $file_version, $suffix = null, $prefix = null){
        if ( ! empty( $prefix ) ) {
            $path = $prefix . "/" . $app_name . "/" . $file_version;
        } else {
            $path = $app_name . "/" . $file_version . "/";
        }
        $path .= date( 'Y/m_d' );
        if ( ! empty( $suffix ) ) {
            $path .= "/" . $suffix;
        }
        $path = str_replace( '\\', '/', $path );
        $path = preg_replace( '/\/+/', "/", $path );
    
        return $path;
    }
    
    /**
     * @param $string
     *
     * @return string|null
     * @throws \Exception
     */
    public static function urlFromString($string){
        if($string){
            $instance = (self::parse( $string ));
            try{
                return $instance->getUrl();
            }catch (\Exception $ex){
                return route('streamer.pub_stream',
                    [
                        'disk' => $instance->bestDisk(),
                        'path' => $instance->getPath()
                    ]
                );
            }
        }
        return null;
    }
    
    /**
     * @param $string
     *
     * @return string|null
     * @throws \Exception
     */
    public static function tempUrlFromString($string){
        if($string){
            $instance = (self::parse( $string ));
            try{
                return $instance->getTempUrl();
            }catch (\Exception $ex){
                return URL::temporarySignedRoute('streamer.pub_stream',
                    600,
                    [
                        'disk' => $instance->bestDisk(),
                        'path' => $instance->getPath()
                    ]
                );
            }
        }
        return null;
    }
    
    /**
     * @param $string
     * @param string $file_name
     *
     * @return DiskPathInfo
     */
    public static function parse($string, $file_name = '') : DiskPathInfo
    {
        if($file_name){
            $options = [
                'name' => FilenameSanitizer::make_safe_file_name( $file_name, config('filesystems.name_length')),
            ];
        }else{
            $options = [];
        }
        $infos = explode( self::$info_separate, $string);
        // disk
        $disks = explode( self::$array_separate, array_shift( $infos ) );
        // path
        $path = array_shift( $infos );
        // path
        $size = intval( array_shift( $infos ) );
        // pages
        if($infos){
            $pages = explode( self::$array_separate, array_shift( $infos ) );
            $options['pages'] = $pages;
        }
        return new DiskPathInfo( $disks, $path, $size, $options);
    }
    
}