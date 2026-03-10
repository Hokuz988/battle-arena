<?php
// exceptions/GameException.php
class GameException extends Exception {
    private $errorCode;
    
    public function __construct($message, $errorCode = 0) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }
    
    public function getErrorCode() {
        return $this->errorCode;
    }
}
?>