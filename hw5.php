<?php

include_once('/var/www/DBcredential.php'); //$dbms $host $dbName $user $pass $table $dsn

$hw5test = new IoTwebService();
$hw5test->dbOperation();

class IoTwebService
{
	private $method;
	private $operation;
	private $parameters;
	private $dbh = null;

	public function __construct()
	{
		// Input type
		$this->method = $_SERVER['REQUEST_METHOD']; // GET or POST
		echo "HTTP method: ".$this->method."<br>\n<br>\n";

		// Database operation type
		$this->operation = 's'; //default: select
		if ($this->method == "GET")
		{
			if (array_key_exists('file',$_GET))
			{
				$this->operation = 'f';
			} else if (array_key_exists('operation',$_GET) && ($_GET['operation']=='i' || $_GET['operation']=='s' || $_GET['operation']=='u' || $_GET['operation']=='d'))
			{
				$this->operation = $_GET['operation'];
			}
		} else if ($this->method == "POST")
		{
			if (array_key_exists('file',$_POST))
			{
				$this->operation = 'f';
			} else if (array_key_exists('operation',$_POST) && ($_POST['operation']=='i' || $_POST['operation']=='s' || $_POST['operation']=='u' || $_POST['operation']=='d'))
			{
				$this->operation = $_POST['operation'];
			}
		}

		// Parameters
		$this->parseParameters();

		// connect database
		global $user, $pass, $dsn;
		try
		{
		    $this->dbh = new PDO($dsn, $user, $pass);
		    echo "mysql connect successfully<br>\n<br>\n";
		} catch (PDOException $e)
		{
		    die ("Error!: " . $e->getMessage() . "<br>\n<br>\n");
		}
	}

	private function parseParameters()
	{
		if ($this->operation != 'f')
		{
			if ($this->method == "GET")
			{
				// $parameters[0] - studentID
				if (!array_key_exists('id',$_GET))
				{
					$this->parameters[0] = 'null';
				} else
				{
					$this->parameters[0] = $_GET['id'];
				}
				// $parameters[1] - studentName
				if (!array_key_exists('name',$_GET))
				{
					$this->parameters[1] = 'null';
				} else
				{
					$this->parameters[1] = $_GET['name'];
				}
				// $parameters[2] - major
				if (!array_key_exists('major',$_GET))
				{
					$this->parameters[2] = 'null';
				} else
				{
					$this->parameters[2] = $_GET['major'];
				}
				// parameters[3] - note
				if (!array_key_exists('note',$_GET))
				{
					$this->parameters[3] = 'null';
				} else
				{
					$this->parameters[3] = $_GET['note'];
				}
			} else if ($this->method == "POST")
			{
				// $parameters[0] - studentID
				if (!array_key_exists('id',$_POST))
				{
					$this->parameters[0] = 'null';
				} else
				{
					$this->parameters[0] = $_POST['id'];
				}
				// $parameters[1] - studentName
				if (!array_key_exists('name',$_POST))
				{
					$this->parameters[1] = 'null';
				} else
				{
					$this->parameters[1] = $_POST['name'];
				}
				// $parameters[2] - major
				if (!array_key_exists('major',$_POST))
				{
					$this->parameters[2] = 'null';
				} else
				{
					$this->parameters[2] = $_POST['major'];
				}
				// parameters[3] - note
				if (!array_key_exists('note',$_POST))
				{
					$this->parameters[3] = 'null';
				} else
				{
					$this->parameters[3] = $_POST['note'];
				}
			}
		} else // operation = 'f'
		{
			$data = array();
			// open and read file
			if ($this->method == "GET")
			{
				if (file_exists($_GET['file']))
				{
					parse_str(file_get_contents($_GET['file'],'r'), $data);
				} else
				{
					die("File <b>".$_GET['file']."</b> doesn't exist!<br>\n");
				}
			} else if ($this->method == "POST")
			{
				if (file_exists($_POST['file']))
				{
					parse_str(file_get_contents($_POST['file'],'r'), $data);
				} else
				{
					die("File <b>".$_POST['file']."</b> doesn't exist!<br>\n");
				}
			}
			// initialize $parameters[]
			if ($data!=null)
			{
				// $parameters[0] - studentID
				if (!array_key_exists('id',$data))
				{
					$this->parameters[0] = 'null';
				} else
				{
					$this->parameters[0] = $data['id'];
				}
				// $parameters[1] - studentName
				if (!array_key_exists('name',$data))
				{
					$this->parameters[1] = 'null';
				} else
				{
					$this->parameters[1] = $data['name'];
				}
				// $parameters[2] - major
				if (!array_key_exists('major',$data))
				{
					$this->parameters[2] = 'null';
				} else
				{
					$this->parameters[2] = $data['major'];
				}
				// parameters[3] - note
				if (!array_key_exists('note',$data))
				{
					$this->parameters[3] = 'null';
				} else
				{
					$this->parameters[3] = $data['note'];
				}
				// change $operation
				$this->operation = 's'; // default
				if (array_key_exists('operation',$data) && ($data['operation']=='i' || $data['operation']=='s' || $data['operation']=='u' || $data['operation']=='d'))
				{
					$this->operation = $data['operation'];
				}
			}
		}
		$this->parameters[4] = date('Y-m-d H:i:s');
	}

	public function dbOperation()
	{	
		global $table;
		$sqlcmd = "";
		// quoting
		for ($i=1; $i<count($this->parameters) ;$i++)
		{ 
			 if ($this->parameters[$i]!="null")
			 {
			 	$this->parameters[$i] = "'".$this->parameters[$i]."'";
			 }
		}
		// generate SQL command
		switch ($this->operation)
		{
			case 'i':
				$sqlcmd .= "INSERT INTO ".$table." (studentName, major, note, updateDate) VALUES (".$this->parameters[1].",".$this->parameters[2].",".$this->parameters[3].",".$this->parameters[4].");";
				break;
			case 'u':
				$sqlcmd .= "UPDATE ".$table." SET studentName=".$this->parameters[1].",major=".$this->parameters[2].",note=".$this->parameters[3].",updateDate=".$this->parameters[4]." WHERE studentID=".$this->parameters[0].";";
				break;
			case 'd':
				$sqlcmd .= "DELETE FROM ".$table." WHERE studentID=".$this->parameters[0].";";
				break;
			case 's':
			default:
				$sqlcmd .= "SELECT * FROM ".$table;
				if ($this->parameters[0]!='null')
				{
					$sqlcmd .= " WHERE studentID=".$this->parameters[0];
				}
				$sqlcmd .= ";";
				break;
		}
		echo "SQL Command: ".$sqlcmd."<br>\n<br>\n";
		
		$result = $this->dbh->query($sqlcmd);

		// output results
		echo "<table border=1>\n<tr><td>studentID</td><td>studentName</td><td>major</td><td>note</td><td>updateDate</td></tr>\n";
		while ($row = $result->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr><td>".$row['studentID']."</td><td>".$row['studentName']."</td><td>".$row['major']."</td><td>".$row['note']."</td><td>".$row['updateDate']."</td></tr>\n";
		}
		echo "</table><br>\n";
	}

	public function __destruct()
	{
		// close database connection
		if ($this->dbh!=null)
		{
			$this->dbh = null;
			echo "mysql connection closed<br>\n<br>\n";
		}
	}
}
?>