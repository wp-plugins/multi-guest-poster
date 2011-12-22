<?php

class mpcoSpintax {
    protected $_words = array();
    protected $_urls = array();
    protected $_isValid = false;
    protected $_isUrl = false;
    protected $_map = array();
    protected $_index = 0;
    protected $_skip_itself = false;

    public function __construct($spintax, $skip_itself) {
        
        $this->_prepareSpintax($spintax);
        
        $this->_skip_itself = $skip_itself;
        
    }

    public function isValid() {
        return $this->_isValid;
    }
    
    public function isUrl() {
        return $this->_isUrl;
    }

    public function getWords() {
        return $this->_words;
    }
    
    public function getUrls() {
        return $this->_urls;
    }
    
    public function prepareMap($id, $text) {
        $words = $this->getWords();

        $isUrl = $this->isUrl();
        
        $map = array();
        foreach($words as $key => $word) {
            $regexp = '/\b(' . $word . ')\b(?!(?:(?!<\/?[a].*?>).)*<\/[a].*?>)(?![^<>]*>)/si';
            
            if(!preg_match_all($regexp, $text, $matches)) {
                continue;
            }
            
            foreach($matches[0] as $word_id => $match) {
                $map[] = array(
                    'spin_id' => $id,
                    'word_id' => $word_id,
                    'is_url'  => $isUrl,
                );
            }
            
        }
        
        return $map;
    }

    protected function _prepareSpintax($spintax) {
        if(!(preg_match('/^\{([^}]+)\}$/usi', $spintax, $matches))) {
            return false;
        }

        $spintax = $matches[1];

        $words = '';
        $urls  = '';
        if(strpos($spintax, '||') !== false) {
            list($words, $urls) = explode('||', $spintax);
        } else {
            $words = $spintax;
        }

        $words = explode('|', $words);
        $urls  = explode('|', $urls);

        $this->_words = array_map('strtolower', array_filter(array_map('trim', $words), 'strlen'));
        $this->_urls  = array_filter(array_map('trim', $urls), 'strlen');

        $this->_isUrl = sizeof($this->_urls) > 0 ? true : false;
        
        $this->_isValid = true;
        
        return true;
    }
    
    /**
     * process content with one spintax
     * 
     * @param string $spintax
     * @param bool $useLinks - if true words will be wrapped by links if urls are presented in spintax
     * @return BOOL 
     */
    public function process($content, $map) {
        $words = $this->getWords();
        
        $this->_map = $map;
        $this->_index = 0;

        $words_regexp = array();
        foreach($words as $key => $word) {
            $words_regexp[] = '/\b(' . $word . ')\b(?!(?:(?!<\/?[a].*?>).)*<\/[a].*?>)(?![^<>]*>)/si';
        }

        $content = preg_replace($words_regexp, 'spintax{$1}', $content);

        $words_regexp = array();
        foreach($words as $key => $word) {
            $words_regexp[] = '/spintax\{(' . $word . ')\}/si';
        }

        return preg_replace_callback($words_regexp, array(&$this, 'replaceCallback'), $content);
    }

    public function replaceCallback($matches) {
        $make_link = isset ($this->_map[$this->_index]) ?
                $this->_map[$this->_index] : false;
        $this->_index++;
        
        $original_word = $matches[1];

        if(sizeof($this->_words) <= 1 && !$this->isUrl()) {
            return $original_word;
        }

        $words = $this->_words;
        
        if($this->_skip_itself) {
            $i = array_search(strtolower($original_word), $words);
            unset($words[$i]);
        }
        
        $word = $words[array_rand($words)];

        if(strtoupper($original_word) == $original_word) { // word in CAPS
            $word = strtoupper($word);
        } elseif(ucfirst($original_word) == $original_word) { // First char is in uppder case
            $word = ucfirst($word);
        }

        if(!$this->isUrl() || !$make_link) {
           return $word;
        }

        $url = $this->_urls[array_rand($this->_urls)];

        return '<a href="' . $url . '">' . $word . '</a>';
    }
}