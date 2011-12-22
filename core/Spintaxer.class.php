<?php

require_once dirname(__FILE__) . '/Spintax.class.php';

class mpcoSpintaxer {
    protected $_text;
    protected $_spintaxes;
    protected $_links_limit;
    protected $_result;
    protected $_map;
    protected $_skip_itself;

    public function __construct($spintaxes, $text, $links_limit = -1, $skip_itself = false) {
        
        $this->_spintaxes = array_filter(array_map('trim', explode("\n", 
                $spintaxes)), 'strlen');
        $this->_text = $text;
        $this->_links_limit = $links_limit;
        
        $this->_skip_itself = $skip_itself;
        
        $this->_result = null;
        
    }
    
    public function getResult() {
        if($this->_result) {
            return $this->_result;
        }
        
        $this->spintaxing();
        
        $this->_result = $this->_text;
        return $this->_result;
    }


    /**
     *  process content with all spintaxes defined in rule
     * @param string $text - source text
     * @param bool $useLinks - if true words will be wrapped by links if urls are presented in spintax
     * 
     * @return string processed text
     */
    protected function spintaxing() {
        $this->_prepareSpintaxes();
        $this->_prepareMap();

        foreach($this->_spintaxes as $key => $spintax) {
            $this->_text = $spintax->process($this->_text, $this->_map[$key]);
        }
    }

    protected function _prepareMap() {
        $this->_map = array();
        
        foreach ($this->_spintaxes as $key => $spintax) {
            $this->_map = array_merge($this->_map, $spintax->prepareMap($key, $this->_text));
        }
        
        shuffle($this->_map);
        
        $linksLimit = $this->_links_limit;
        
        if($linksLimit == 0) {
            return;
        }
        
        foreach($this->_map as &$item) {
            if(!$item['is_url']) {
                continue;
            }
            
            $item['make_link'] = true;
            
            if($linksLimit > 0) {
                $linksLimit--;
            }

            if($linksLimit == 0) {
                break;
            }
        }
        
        $this->_unpackMap();
    }
    
    protected function _unpackMap() {
        $result = array();
        
        foreach ($this->_map as $item) {
            if(!isset ($result[$item['spin_id']])) {
                $result[$item['spin_id']] = array();
            }
            
            $result[$item['spin_id']][$item['word_id']] = isset($item['make_link']) ? $item['make_link'] : false;
        }
        
        $this->_map = $result;
        
    }

    protected function _prepareSpintaxes() {
        if(sizeof($this->_spintaxes) == 0) {
            return;
        } 
        
        $this->_spintaxes = array_values($this->_spintaxes);
        if(is_object($this->_spintaxes[0])) {
            return;
        }
        
        $strings = $this->_spintaxes;
        
        $useLinks = $this->_links_limit != 0 ? true : false;
        
        $this->_spintaxes = array();
        foreach($strings as $spintax) {
            $spin = new mpcoSpintax($spintax, $this->_skip_itself);
            if($spin->isValid()) {
                $this->_spintaxes[] = $spin;
            }
        }
    }
}
