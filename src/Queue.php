<?php

	class Queue
	{
		private $first = NULL;
		private $last = NULL;
		
		public function __construct() { }
		
		public function Enqueue($value)
		{
			$tmp = new QueueNode($value);
			
			$tmp->previous = $this->last;
			
			if(is_null($this->first))
				$this->first = $tmp;
			else
				$this->last->next = $tmp;
			
			$this->last = $tmp;
		}
		
		public function Dequeue()
		{
			if($this->IsEmpty())
				throw new Exception("The queue is empty");
			
			$tmp = $this->first;
			
			$this->first = $tmp->next;
			return $tmp->value;
		}
		
		public function IsEmpty()
		{
			return is_null($this->first);
		}
	}
	
	class QueueNode
	{
		public $previous;
		public $next;
		public $value;
		
		public function __construct($value)
		{
			$this->value = $value;
		}
	}

?>