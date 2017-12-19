<?php
class Block {
    private $ts;
    private $nonce = 0;
    private $data;
    private $prev_hash;
    protected $hash;
    private $hash_algorithm;
    private $hash_prefix;

    protected function __construct($hash_algorithm, $hash_prefix, $prev_hash, $data) {
        $this->ts = microtime();
        $this->data = $data;
        $this->prev_hash = $prev_hash;
        $this->hash_algorithm = $hash_algorithm;
        $this->hash_prefix = $hash_prefix;
        $payload = $this->ts . $this->data . $this->prev_hash . $this->nonce;
        $this->hash = hash($this->hash_algorithm, $payload);
        if(!empty($this->prev_hash)) {
            while(!$this->is_valid_hash($this->hash)) {
                $this->nonce++;
                $payload = $this->ts . $this->data . $this->prev_hash . $this->nonce;
                $this->hash = hash($this->hash_algorithm, $payload);
            }
        }
        return $this;
    }

    private function is_valid_hash($hash) {
        if(strlen($this->hash_prefix) == 0) {
            return true;
        }
        else if(substr($hash, 0, strlen($this->hash_prefix)) === $this->hash_prefix) {
            return true;
        }
        return false;
    }

}


class Blockchain extends Block {
    private $hash_algorithm;
    private $hash_prefix;
    private $chain;

    public function __construct($hash_algorithm = '', $hash_prefix = '') {
        $this->is_valid_hash_algorithm($hash_algorithm) or die('Invalid hash algorithm: "' . $hash_algorithm . '" is not supported on this system.' . "\n");
        $this->hash_algorithm = $hash_algorithm;
        $this->is_valid_hash_prefix($hash_prefix) or die('Invalid hash prefix: "' . $hash_prefix . '" is not a hexadecimal string, or blank.' . "\n");
        $this->hash_prefix = $hash_prefix;
        $this->chain = array();
        $this->genesis();
        return $this;
    }

    private function genesis() {
        $this->chain[] = new Block($this->hash_algorithm, $this->hash_prefix, false, '**Genesis**');
        return $this->last();
    }

    public function grow($data) {
        $this->chain[] = new Block($this->hash_algorithm, $this->hash_prefix, $this->last()->hash, $data);
        return $this->last();
    }

    private function last() {
        return $this->chain[count($this->chain)-1];
    }

    private function is_valid_hash_algorithm($algorithm) {
        if(in_array($algorithm, hash_algos())) {
            return true;
        }
        return false;
    }

    private function is_valid_hash_prefix($prefix) {
        if(empty($prefix) or preg_match('/^[a-f0-9]*$/i', $prefix)) {
            return true;
        }
        return false;
    }

    public function dumpraw() {
        print_r($this->chain);
    }

}


// Now, let's build a tiny blockchain...
$tinyblockchain = new Blockchain('sha256', '999');
$tinyblockchain->grow("one potatoe");
$tinyblockchain->grow("Two potatoes");
$tinyblockchain->grow("Three Potatoes");
$tinyblockchain->grow("FOUR!");
$tinyblockchain->dumpraw();
?>