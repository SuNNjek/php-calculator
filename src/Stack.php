<?php

class Stack
{
    private $top = NULL;

    public function __construct() { }

    public function IsEmpty()
    {
        return is_null($this->top);
    }

    public function Top()
    {
        if($this->IsEmpty())
            throw new Exception("The stack is empty");

        return $this->top->value;
    }

    public function Push($value)
    {
        $this->top = new StackNode($value, $this->top);
    }

    public function Pop()
    {
        if($this->IsEmpty())
            throw new Exception("The stack is empty");

        $result = $this->top->value;
        $this->top = $this->top->next;
        return $result;
    }
}

class StackNode
{
    public $next;
    public $value;

    public function __construct($value, StackNode $next = NULL){
        $this->value = $value;
        $this->next = $next;
    }
}

?>