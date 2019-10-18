<?php

class mymysql extends mysqli
{
    public function __construct() {
       parent::__construct("localhost", "fit-miramal", "nemocnice","fit-miramal");
		  if (mysqli_connect_error()) 
		  {
			  echo '<p style="color:red;">Error connection';
		  }
	}
	
	public function dbClose()
	{
		$this->close();
	}
	
	public function dbQuery($q)
	{
		$result=$this->query($q);
		if (!$result)
		{

			return false;
		}
		return $result;
	}
	
	public function dbSelect($q)
	{
		$result=$this->query($q);
		if (!$result)
		{
			
			return false;
		}
		$n = -1;
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC))
		{
			$n+=1;
			$results[$n] = $myrow; 
		}
		return $results;
	}
	
	public function dbSelectNumber($q)
	{
		$result=$this->query($q);
		if (!$result)
		{
			return false;
		}
		else
		{
			$myrow = $result->fetch_array(MYSQLI_NUM);
			return intval($myrow[0]);
		}
		
	}




 
}
?>