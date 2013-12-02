<?php

//异常

class DetuYunException extends Exception {
    public function __construct($message, $code, Exception $previous = null) {
        parent::__construct($message, $code);  
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class DetuYunAuthorizationException extends DetuYunException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 401, $previous);
    }
}

class DetuYunForbiddenException extends DetuYunException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 403, $previous);
    }
}

class DetuYunNotFoundException extends DetuYunException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 404, $previous);
    }
}

class DetuYunNotAcceptableException extends DetuYunException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 406, $previous);
    }
}

class DetuYunServiceUnavailable extends DetuYunException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 503, $previous);
    }
}

?>