<?php
namespace gizmola\NaviBridge;

/**
 * Navibridge
 * A helper library for generating Denso Navibridge links from Address Information
 * @since 1.0
 * @author David Rolston
 */

/*
 * The MIT License (MIT)
*
* Copyright (c) 2014 - David Rolston
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
*/

class Navibridge 
{
    const NAVI_SCHEME_VERSION = '1.4';
    const NAVI_SCHEME = 'navicon://';
    const NAVI_ENDPOINT_SINGLE = 'setPOI?';
    const NAVI_ENDPOINT_MULTI = 'setMultiPOI?';
    
    const MAX_POI = 5;
    const MAX_TITLE_LEN = 26;
    
    protected $entryCount = 0;
    /*
     * Pass 1-5 waypoints
     */
    protected $waypoints = array();
    
    protected $required = array('ver', 'appName');
    
    protected $state = false;
    
    /*
     * Parameters have to be returned in order, or NaviBridge will error
     * 
     */
    protected $order = array('ver', 'll', 'addr', 'appName', 'title', 'radKM', 'radML', 'tel', 'text', 'callURL');
    
    protected $configKeys = array('ver', 'appName', 'callURL');
    
    protected $ver;
    
    protected $appName;
    
    protected $callURL;
    
    /*
     * $config = array(
     *     'appName' => 'your registered NaviBridge Appname',
     *     'callURL' => '://your_appname/return/url'
     * )     
     */
    public function __construct($config)
    {
        $this->ver = self::NAVI_SCHEME_VERSION;
        foreach($config as $key => $value) {
            if (in_array($key, $this->configKeys)) {
                $this->$key = $value;
            }
        }
        
        if (!$this->checkConfig()) {
            throw new \Exception('A Required initialization parameter is missing.');
        }
    }
    
    protected function checkConfig()
    {
        $pass = true;
        foreach ($this->required as $keyname) {
            
            $pass = (isset($this->$keyname));
            if (!$pass) break;
        }
        return $pass;
    }
    
    /*
     * $waypoint = array(array('title' => 'Some Name', 'll' => '32.002, 22.004')); 
     */
    public function addWaypoint($waypoint)
    {
        $this->waypoints[] = $waypoint;
        $this->entryCount++;
    }

    protected function cleanTel($tel)
    {
        return preg_replace('/[^0-9+*#]/', '', $tel);
    }
    
    public function getTarget()
    {
        
        if (1 >= $this->entryCount) {
            $multiWaypoints = (1 <= $this->entryCount);
            // Set Scheme
            $target = ($multiWaypoints) ? self::NAVI_SCHEME . self::NAVI_ENDPOINT_SINGLE : self::NAVI_SCHEME . self::NAVI_ENDPOINT_MULTI;
            //Set version
            $target .= 'ver=' . urlencode($this->ver) . '&';
            
            foreach($this->waypoints as $waypoint) {
                // Inject 'appName'
                $waypoint['appName'] = $this->appName;
                foreach ($this->order as $keyname) {
                    
                    if (array_key_exists($keyname, $waypoint)) {
                        // Attribute constraint handling
                        switch ($keyname) {
                            case 'title':
                                $waypoint['title'] = mb_strimwidth($waypoint['title'], 0, self::MAX_TITLE_LEN);
                            case 'tel':
                                $waypoint['tel'] = $this->cleanTel($waypoint['tel']);
                        }
                        $target .= $keyname . '=' . urlencode($waypoint[$keyname]) . '&';
                    }
                }
            }
            
            // Add return url if it exists
            if (isset($this->callURL))
                $target .= 'callURL=' . urlencode($this->callURL);
            
            // Remove the trailing ampersand
            if ('&' == substr($target, -1)) {
                $target = rtrim($target, '&');
            }
            
            return $target;
            
        } else {
            // If a link can't be generated, just return empty string
            return '';
        }
    }
}