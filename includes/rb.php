<?php
//Written by Gabor de Mooij and the RedBeanPHP Community, copyright 2009-2011
//Licensed New BSD/GPLV2 - see license.txt
 
interface RedBean_Driver {
	public static function getInstance( $host, $user, $pass, $dbname );
	public function GetAll( $sql, $aValues=array() );
	public function GetCol( $sql, $aValues=array() );
	public function GetCell( $sql, $aValues=array() );
	public function GetRow( $sql, $aValues=array() );
	public function ErrorNo();
	public function Errormsg();
	public function Execute( $sql, $aValues=array() );
	public function Escape( $str );
	public function GetInsertID();
	public function Affected_Rows();
	public function setDebugMode( $tf );
	public function GetRaw();
	public function CommitTrans();
	public function StartTrans();
	public function FailTrans();
}
class RedBean_Driver_PDO implements RedBean_Driver {
	private $dsn;
	private static $instance;
	private $debug = false;
	private $pdo;
	private $affected_rows;
	private $rs;
	private $exc =0;
	private $connectInfo = array();
	public $flagUseStringOnlyBinding = false;
	private $isConnected = false;
	public static function getInstance($dsn, $user, $pass, $dbname) {
		if(is_null(self::$instance)) {
			self::$instance = new RedBean_Driver_PDO($dsn, $user, $pass);
		}
		return self::$instance;
	}
	public function __construct($dsn, $user = NULL, $pass = NULL) {
		if ($dsn instanceof PDO) {
			$this->pdo = $dsn;
			$this->isConnected = true;
			$this->pdo->setAttribute(1002, 'SET NAMES utf8');
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$this->dsn = $this->getDatabaseType();
		} else {
			$this->dsn = $dsn;
			$this->connectInfo = array( "pass"=>$pass, "user"=>$user );
		}
	}
	public function connect() {
		if ($this->isConnected) return;
		$user = $this->connectInfo["user"];
		$pass = $this->connectInfo["pass"];
		$this->pdo = new PDO(
				  $this->dsn,
				  $user,
				  $pass,
				  array(1002 => 'SET NAMES utf8',
							 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
							 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				  )
		);
		$this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, TRUE);
		$this->isConnected = true;
	}
	protected function bindParams($s,$aValues) {
		foreach($aValues as $key=>&$value) {
			if (is_integer($key)) {
				if (is_null($value)){
					$s->bindValue($key+1,null,PDO::PARAM_NULL);
				}elseif (!$this->flagUseStringOnlyBinding && RedBean_QueryWriter_AQueryWriter::canBeTreatedAsInt($value) && $value < 2147483648) {
					$s->bindParam($key+1,$value,PDO::PARAM_INT);
				}
				else {
					$s->bindParam($key+1,$value,PDO::PARAM_STR);
				}
			}
			else {
				if (is_null($value)){
					$s->bindValue($key,null,PDO::PARAM_NULL);
				}
				elseif (!$this->flagUseStringOnlyBinding && RedBean_QueryWriter_AQueryWriter::canBeTreatedAsInt($value) &&  $value < 2147483648) {
					$s->bindParam($key,$value,PDO::PARAM_INT);
				}
				else { 
					$s->bindParam($key,$value,PDO::PARAM_STR);
				}
			}
		}
	}
	public function GetAll( $sql, $aValues=array() ) {
		$this->connect();
		$this->exc = 0;
		if ($this->debug) {
			echo "<HR>" . $sql.print_r($aValues,1);
		}
		try {
			if (strpos("pgsql",$this->dsn)===0) {
				$s = $this->pdo->prepare($sql, array(PDO::PGSQL_ATTR_DISABLE_NATIVE_PREPARED_STATEMENT => true));
			}
			else {
				$s = $this->pdo->prepare($sql);
			}
			$this->bindParams( $s, $aValues );
			$s->execute();
		  	if ($s->columnCount()) {
		    	$this->rs = $s->fetchAll();
	    	}
		  	else {
		    	$this->rs = null;
		  	}
			$rows = $this->rs;
		}catch(PDOException $e) {
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				$x = new RedBean_Exception_SQL( $e->getMessage(), 0);
			}
			else {
				$x = new RedBean_Exception_SQL( $e->getMessage(), 0, $e );
			}
			$x->setSQLState( $e->getCode() );
			throw $x;
		}
		if(!$rows) {
			$rows = array();
		}
		if ($this->debug) {
			if (count($rows) > 0) {
				echo "<br><b style='color:green'>resultset: " . count($rows) . " rows</b>";
			}
		}
		return $rows;
	}
	public function GetCol($sql, $aValues=array()) {
		$this->connect();
		$this->exc = 0;
		$rows = $this->GetAll($sql,$aValues);
		$cols = array();
		if ($rows && is_array($rows) && count($rows)>0) {
			foreach ($rows as $row) {
				$cols[] = array_shift($row);
			}
		}
		return $cols;
	}
	public function GetCell($sql, $aValues=array()) {
		$this->connect();
		$this->exc = 0;
		$arr = $this->GetAll($sql,$aValues);
		$row1 = array_shift($arr);
		$col1 = array_shift($row1);
		return $col1;
	}
	public function GetRow($sql, $aValues=array()) {
		$this->connect();
		$this->exc = 0;
		$arr = $this->GetAll($sql, $aValues);
		return array_shift($arr);
	}
	public function ErrorNo() {
		$this->connect();
		if (!$this->exc) return 0;
		$infos = $this->pdo->errorInfo();
		return $infos[1];
	}
	public function Errormsg() {
		$this->connect();
		if (!$this->exc) return "";
		$infos = $this->pdo->errorInfo();
		return $infos[2];
	}
	public function Execute( $sql, $aValues=array() ) {
		$this->connect();
		$this->exc = 0;
		if ($this->debug) {
			echo "<HR>" . $sql.print_r($aValues,1);
		}
		try {
			if (strpos("pgsql",$this->dsn)===0) {
				$s = $this->pdo->prepare($sql, array(PDO::PGSQL_ATTR_DISABLE_NATIVE_PREPARED_STATEMENT => true));
			}
			else {
				$s = $this->pdo->prepare($sql);
			}
			$this->bindParams( $s, $aValues );
			$s->execute();
			$this->affected_rows=$s->rowCount();
			return $this->affected_rows;
		}
		catch(PDOException $e) {
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				$x = new RedBean_Exception_SQL( $e->getMessage(), 0);
			}
			else {
				$x = new RedBean_Exception_SQL( $e->getMessage()." SQL:".$sql, 0, $e );
			}
			$x->setSQLState( $e->getCode() );
			throw $x;
		}
	}
	public function Escape( $str ) {
		$this->connect();
		return substr(substr($this->pdo->quote($str), 1), 0, -1);
	}
	public function GetInsertID() {
		$this->connect();
		return (int) $this->pdo->lastInsertId();
	}
	public function Affected_Rows() {
		$this->connect();
		return (int) $this->affected_rows;
	}
	public function setDebugMode( $tf ) {
		$this->connect();
		$this->debug = (bool)$tf;
	}
	public function GetRaw() {
		$this->connect();
		return $this->rs;
	}
	public function StartTrans() {
		$this->connect();
		$this->pdo->beginTransaction();
	}
	public function CommitTrans() {
		$this->connect();
		$this->pdo->commit();
	}
	public function FailTrans() {
		$this->connect();
		$this->pdo->rollback();
	}
	public function getDatabaseType() {
		$this->connect();
		return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	}
	public function getDatabaseVersion() {
		$this->connect();
		return $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}
	public function getPDO() {
		$this->connect();
		return $this->pdo;
	}
}
class RedBean_OODBBean implements IteratorAggregate, ArrayAccess {
	private $properties = array();
	private $__info = NULL;
	private $beanHelper = NULL;
	public static $fetchType = NULL;
	public function setBeanHelper(RedBean_IBeanHelper $helper) {
		$this->beanHelper = $helper;
	}
	public function getIterator() {
		return new ArrayIterator($this->properties);
	}
	public function import( $arr, $selection=false, $notrim=false ) {
		if (is_string($selection)) $selection = explode(",",$selection);
		if (!$notrim && is_array($selection)) foreach($selection as $k=>$s){ $selection[$k]=trim($s); }
		foreach($arr as $k=>$v) {
			if ($k != "__info") {
				if (!$selection || ($selection && in_array($k,$selection))) {
					$this->$k = $v;
				}
			}
		}
		return $this;
	}
	public function export($meta = false) {
		$arr = $this->properties;
		foreach($arr as $k=>$v) {
			if (is_array($v) || is_object($v)) unset($arr[$k]);
		}
		if ($meta) $arr["__info"] = $this->__info;
		return $arr;
	}
	public function __isset( $property ) {
		return (isset($this->properties[$property]));
	}
	public function getID() {
		$idfield = $this->getMeta("sys.idfield");
		return (string) $this->$idfield;
	}
	public function __unset($property) {
		$this->__get($property);
		$fieldLink = $property."_id";
		if (isset($this->$fieldLink)) {
			$this->$fieldLink = null;
			return;
		}
		if ((isset($this->properties[$property]))) {
			unset($this->properties[$property]);
		}
	}
	public function removeProperty( $property ) {
		unset($this->properties[$property]);
	}
	public function &__get( $property ) {
		if ($this->beanHelper)
		$toolbox = $this->beanHelper->getToolbox();
		if (!isset($this->properties[$property])) {
			$fieldLink = $property."_id";
			if (isset($this->$fieldLink) && $fieldLink != $this->getMeta('sys.idfield')) {
				$this->setMeta("tainted",true);
				$type =  $toolbox->getWriter()->getAlias($property);
				$targetType = $this->properties[$fieldLink];
				$bean =  $toolbox->getRedBean()->load($type,$targetType);
				return $bean;
			}
			if (strpos($property,'own')===0) {
				$firstCharCode = ord(substr($property,3,1));
				if ($firstCharCode>=65 && $firstCharCode<=90) {
					$type = (strtolower(str_replace('own','',$property)));
					$myFieldLink = $this->getMeta('type')."_id";
					$beans = $toolbox->getRedBean()->find($type,array(),array(" $myFieldLink = ? ",array($this->getID())));
					$this->properties[$property] = $beans;
					$this->setMeta("sys.shadow.".$property,$beans);
					$this->setMeta("tainted",true);
					return $this->properties[$property];
				}
			}
			if (strpos($property,'shared')===0) {
				$firstCharCode = ord(substr($property,6,1));
				if ($firstCharCode>=65 && $firstCharCode<=90) {
					$type = (strtolower(str_replace('shared','',$property)));
					$keys = $toolbox->getRedBean()->getAssociationManager()->related($this,$type);
					if (!count($keys)) $beans = array(); else
					$beans = $toolbox->getRedBean()->batch($type,$keys);
					$this->properties[$property] = $beans;
					$this->setMeta("sys.shadow.".$property,$beans);
					$this->setMeta("tainted",true);
					return $this->properties[$property];
				}
			}
			$this->properties[$property] = null;
		}
		return $this->properties[$property];
	}
	public function __set( $property, $value ) {
		$this->__get($property);
		$this->setMeta("tainted",true);
		if ($value===false) {
			$value = "0";
		}
		if ($value===true) {
			$value = "1";
		}
		$this->properties[$property] = $value;
	}
	public function getMeta( $path, $default = NULL) {
		return (isset($this->__info[$path])) ? $this->__info[$path] : $default;
	}
	public function setMeta( $path, $value ) {
		$this->__info[$path] = $value;
	}
	public function copyMetaFrom( RedBean_OODBBean $bean ) {
		$this->__info = $bean->__info;
		return $this;
	}
	public function __sleep() {
		$this->setMeta("sys.oodb",null);
		return array('properties','__info');
	}
	public function __call($method, $args) {
		if (!isset($this->__info["model"])) {
			$modelName = RedBean_ModelHelper::getModelName( $this->getMeta("type") );
			if (!class_exists($modelName)) return null;
			$obj = new $modelName();
			$obj->loadBean($this);
			$this->__info["model"] = $obj;
		}
		if (!method_exists($this->__info["model"],$method)) return null;
		return call_user_func_array(array($this->__info["model"],$method), $args);
	}
	public function __toString() {
		$string = $this->__call('__toString',array());
		if ($string === null) {
			return json_encode($this->properties);
		}
		else {
			return $string;
		}
	}
	public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }
    public function offsetExists($offset) {
        return isset($this->properties[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->properties[$offset]);
    }
    public function offsetGet($offset) {
        return $this->__get($offset);
    }
	public function fetchAs($type) {
		self::$fetchType = $type;
		return $this;
	}
}
abstract class RedBean_Observable {
	private $observers = array();
	public function addEventListener( $eventname, RedBean_Observer $observer ) {
		if (!isset($this->observers[ $eventname ])) {
			$this->observers[ $eventname ] = array();
		}
		foreach($this->observers[$eventname] as $o) if ($o==$observer) return;
		$this->observers[ $eventname ][] = $observer;
	}
	public function signal( $eventname, $info ) {
		if (!isset($this->observers[ $eventname ])) {
			$this->observers[ $eventname ] = array();
		}
		foreach($this->observers[$eventname] as $observer) {
			$observer->onEvent( $eventname, $info );
		}
	}
}
interface RedBean_Observer {
	public function onEvent( $eventname, $bean );
}
interface RedBean_Adapter {
	public function getSQL();
	public function escape( $sqlvalue );
	public function exec( $sql , $aValues=array(), $noevent=false);
	public function get( $sql, $aValues = array() );
	public function getRow( $sql, $aValues = array() );
	public function getCol( $sql, $aValues = array() );
	public function getCell( $sql, $aValues = array() );
	public function getAssoc( $sql, $values = array() );
	public function getInsertID();
	public function getAffectedRows();
	public function getDatabase();
	public function getErrorMsg();
	public function startTransaction();
	public function commit();
	public function rollback();
}
class RedBean_Adapter_DBAdapter extends RedBean_Observable implements RedBean_Adapter {
	private $db = null;
	private $sql = "";
	public function __construct($database) {
		$this->db = $database;
	}
	public function getSQL() {
		return $this->sql;
	}
	public function escape( $sqlvalue ) {
		return $this->db->Escape($sqlvalue);
	}
	public function exec( $sql , $aValues=array(), $noevent=false) {
		if (!$noevent) {
			$this->sql = $sql;
			$this->signal("sql_exec", $this);
		}
		return $this->db->Execute( $sql, $aValues );
	}
	public function get( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		return $this->db->GetAll( $sql,$aValues );
	}
	public function getRow( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		return $this->db->GetRow( $sql,$aValues );
	}
	public function getCol( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		return $this->db->GetCol( $sql,$aValues );
	}
	public function getAssoc( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		$rows = $this->db->GetAll( $sql, $aValues );
		$assoc = array();
		if ($rows) {
			foreach($rows as $row) {
				if (count($row)>0) {
					$key = array_shift($row);
				}
				if (count($row)>0) {
					$value = array_shift($row);
				}
				else {
					$value = $key;
				}
				$assoc[ $key ] = $value;
			}
		}
		return $assoc;
	}
	public function getCell( $sql, $aValues = array(), $noSignal = null ) {
		$this->sql = $sql;
		if (!$noSignal) $this->signal("sql_exec", $this);
		$arr = $this->db->getCol( $sql, $aValues );
		if ($arr && is_array($arr))	return ($arr[0]); else return false;
	}
	public function getInsertID() {
		return $this->db->getInsertID();
	}
	public function getAffectedRows() {
		return $this->db->Affected_Rows();
	}
	public function getDatabase() {
		return $this->db;
	}
	public function getErrorMsg() {
		return $this->db->Errormsg();
	}
	public function startTransaction() {
		return $this->db->StartTrans();
	}
	public function commit() {
		return $this->db->CommitTrans();
	}
	public function rollback() {
		return $this->db->FailTrans();
	}
}
interface RedBean_QueryWriter {
	const C_SQLSTATE_NO_SUCH_TABLE = 1;
	const C_SQLSTATE_NO_SUCH_COLUMN = 2;
	const C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION = 3;
	public function getFormattedTableName($type);
	public function getTables();
	public function createTable($type);
	public function getColumns($type);
	public function scanType($value);
	public function addColumn($type, $column, $field);
	public function code($typedescription);
	public function widenColumn($type, $column, $datatype);
	public function updateRecord($type, $updatevalues, $id=null);
	public function selectRecord($type, $conditions, $addSql = null, $delete = false, $inverse = false);
	public function addUniqueIndex($type,$columns);
	public function getIDField($type);
	public function sqlStateIn( $state, $list );
	public function wipe($type);
	public function count($type);
	public function setBeanFormatter(RedBean_IBeanFormatter $beanFormatter);
	public function createView($referenceType, $types, $viewID);
	public function getFieldType($type = "");
	public function safeColumn($name, $noQuotes = false);
	public function safeTable($name, $noQuotes = false);
	public function addConstraint( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $dontCache = false );
	public function getAssocTableFormat($types);
	public function addFK( $type, $targetType, $field, $targetField);
	public function addIndex($type, $name, $column);
	public function getAlias($type);
	public function getTypeForID();
}
abstract class RedBean_QueryWriter_AQueryWriter {
	protected $fcache = array();
	public $tableFormatter;
	public $typeno_sqltype = array();
	protected $adapter;
	protected $idfield = "id";
	protected $defaultValue = 'NULL';
	protected $quoteCharacter = '';
	public function __construct() {
		$this->tableFormatter = new RedBean_DefaultBeanFormatter();
	}
	public function safeTable($name, $noQuotes = false) {
		$name = $this->getFormattedTableName($name);
		$name = $this->check($name);
		if (!$noQuotes) $name = $this->noKW($name);
		return $name;
	}
	public function safeColumn($name, $noQuotes = false) {
		$name = $this->check($name);
		if (!$noQuotes) $name = $this->noKW($name);
		return $name;
	}
  	protected function getInsertSuffix ($table) {
    	return "";
  	}
	public function getFormattedTableName($type) {
		return $this->tableFormatter->formatBeanTable($type);
	}
	public function getAlias($type) {
		return $this->tableFormatter->getAlias($type);
	}
	public function setBeanFormatter( RedBean_IBeanFormatter $beanFormatter ) {
		$this->tableFormatter = $beanFormatter;
	}
	public function getFieldType( $type = "" ) {
		return array_key_exists($type, $this->typeno_sqltype) ? $this->typeno_sqltype[$type] : "";
	}
	public function getIDField( $type ) {
		$nArgs = func_num_args();
		if ($nArgs>1) throw new Exception("Deprecated parameter SAFE, use safeColumn() instead.");
		return $this->tableFormatter->formatBeanID($type);
	}
	protected function check($table) {
		if ($this->quoteCharacter && strpos($table, $this->quoteCharacter)!==false) {
		  throw new Redbean_Exception_Security("Illegal chars in table name");
	    }
		return $this->adapter->escape($table);
	}
	protected function noKW($str) {
		$q = $this->quoteCharacter;
		return $q.$str.$q;
	}
	public function addColumn( $type, $column, $field ) {
		$table = $type;
		$type = $field;
		$table = $this->safeTable($table);
		$column = $this->safeColumn($column);
		$type = $this->getFieldType($type);
		$sql = "ALTER TABLE $table ADD $column $type ";
		$this->adapter->exec( $sql );
	}
	public function updateRecord( $type, $updatevalues, $id=null) {
		$table = $type;
		if (!$id) {
			$insertcolumns =  $insertvalues = array();
			foreach($updatevalues as $pair) {
				$insertcolumns[] = $pair["property"];
				$insertvalues[] = $pair["value"];
			}
			return $this->insertRecord($table,$insertcolumns,array($insertvalues));
		}
		if ($id && !count($updatevalues)) return $id;
		$idfield = $this->safeColumn($this->getIDField($table));
		$table = $this->safeTable($table);
		$sql = "UPDATE $table SET ";
		$p = $v = array();
		foreach($updatevalues as $uv) {
			$p[] = " {$this->safeColumn($uv["property"])} = ? ";
			$v[]=$uv["value"];
		}
		$sql .= implode(",", $p ) ." WHERE $idfield = ".intval($id);
		$this->adapter->exec( $sql, $v );
		return $id;
	}
	protected function insertRecord( $table, $insertcolumns, $insertvalues ) {
		$default = $this->defaultValue;
		$idfield = $this->safeColumn($this->getIDField($table));
		$suffix = $this->getInsertSuffix($table);
		$table = $this->safeTable($table);
		if (count($insertvalues)>0 && is_array($insertvalues[0]) && count($insertvalues[0])>0) {
			foreach($insertcolumns as $k=>$v) {
				$insertcolumns[$k] = $this->safeColumn($v);
			}
			$insertSQL = "INSERT INTO $table ( $idfield, ".implode(",",$insertcolumns)." ) VALUES ";
			$insertSQL .= "( $default, ". implode(",",array_fill(0,count($insertcolumns)," ? "))." ) $suffix";
			foreach($insertvalues as $i=>$insertvalue) {
				$ids[] = $this->adapter->getCell( $insertSQL, $insertvalue, $i );
			}
			$result = count($ids)===1 ? array_pop($ids) : $ids;
		}
		else {
			$result = $this->adapter->getCell( "INSERT INTO $table ($idfield) VALUES($default) $suffix");
		}
		if ($suffix) return $result;
	   $last_id = $this->adapter->getInsertID();
		return ($this->adapter->getErrorMsg()=="" ?  $last_id : 0);
	}
	public function selectRecord( $type, $conditions, $addSql=null, $delete=null, $inverse=false ) {
		if (!is_array($conditions)) throw new Exception("Conditions must be an array");
		$table = $this->safeTable($type);
		$sqlConditions = array();
		$bindings=array();
		foreach($conditions as $column=>$values) {
			$sql = $this->safeColumn($column);
			$sql .= " ".($inverse ? " NOT ":"")." IN ( ";
			$sql .= implode(",",array_fill(0,count($values),"?")).") ";
			$sqlConditions[] = $sql;
			if (!is_array($values)) $values = array($values);
			foreach($values as $k=>$v) {
				$values[$k]=strval($v);
			}
			$bindings = array_merge($bindings,$values);
		}
		if (is_array($addSql)) {
			if (count($addSql)>1) {
				$bindings = array_merge($bindings,$addSql[1]);
			}
			else {
				$bindings = array();
			}
			$addSql = $addSql[0];
		}
		$sql="";
		if (count($sqlConditions)>0) {
			$sql = implode(" AND ",$sqlConditions);
			$sql = " WHERE ( $sql ) ";
			if ($addSql) $sql .= " AND $addSql ";
		}
		elseif ($addSql) {
			$sql = " WHERE ".$addSql;
		}
		$sql = (($delete) ? "DELETE FROM " : "SELECT * FROM ").$table.$sql;
		$rows = $this->adapter->get($sql,$bindings);
		return $rows;
	}
	public function createView($referenceType, $constraints, $viewID) {
		$referenceTable = $referenceType;
		$viewID = $this->safeTable($viewID,true);
		$safeReferenceTable = $this->safeTable($referenceTable);
		try{ $this->adapter->exec("DROP VIEW $viewID"); }catch(Exception $e){}
		$columns = array_keys( $this->getColumns( $referenceTable ) );
		$referenceTable = ($referenceTable);
		$joins = array();
		foreach($constraints as $table=>$constraint) {
			$safeTable = $this->safeTable($table);
			$addedColumns = array_keys($this->getColumns($table));
			foreach($addedColumns as $addedColumn) {
				$newColName = $addedColumn."_of_".$table;
				$newcolumns[] = $this->safeTable($table).".".$this->safeColumn($addedColumn) . " AS ".$this->safeColumn($newColName);
			}
			if (count($constraint)!==2) throw Exception("Invalid VIEW CONSTRAINT");
			$referenceColumn = $constraint[0];
			$compareColumn = $constraint[1];
			$join = $referenceColumn." = ".$compareColumn;
			$joins[] = " LEFT JOIN $safeTable ON $join ";
		}
		$joins = implode(" ", $joins);
		foreach($columns as $k=>$column) {
			$columns[$k]=$safeReferenceTable.".".$this->safeColumn($column);
		}
		$columns = implode("\n,",array_merge($newcolumns,$columns));
		$sql = "CREATE VIEW $viewID AS SELECT $columns FROM $safeReferenceTable $joins ";
		$this->adapter->exec($sql);
		return true;
	}
	public function wipe($type) {
		$table = $type;
		$table = $this->safeTable($table);
		$sql = "TRUNCATE $table ";
		$this->adapter->exec($sql);
	}
	public function count($beanType) {
		$table = $this->safeTable($beanType);
		$sql = "SELECT count(*) FROM $table ";
		return (int) $this->adapter->getCell($sql);
	}
	public function addIndex($type, $name, $column) {
		$table = $type;
		$table = $this->safeTable($table);
		$name = preg_replace("/\W/","",$name);
		$column = $this->safeColumn($column);
		try{ $this->adapter->exec("CREATE INDEX $name ON $table ($column) "); }catch(Exception $e){}
	}
	public static function canBeTreatedAsInt( $value ) {
		return (boolean) (ctype_digit(strval($value)) && strval($value)===strval(intval($value)));
	}
	public function addFK( $type, $targetType, $field, $targetField) {
		$table = $this->safeTable($type);
		$tableNoQ = $this->safeTable($type,true);
		$targetTable = $this->safeTable($targetType);
		$column = $this->safeColumn($field);
		$targetColumn  = $this->safeColumn($targetField);
		$db = $this->adapter->getCell("select database()");
		$fks =  $this->adapter->getCell("
			SELECT count(*)
			FROM information_schema.KEY_COLUMN_USAGE
			WHERE TABLE_SCHEMA ='$db' AND TABLE_NAME = '$tableNoQ'  AND
			CONSTRAINT_NAME <>'PRIMARY' AND REFERENCED_TABLE_NAME is not null
		");
		if ($fks==0) {
			try{
				$this->adapter->exec("ALTER TABLE  $table
				ADD FOREIGN KEY (  $column ) REFERENCES  $targetTable (
				$targetColumn) ON DELETE NO ACTION ON UPDATE NO ACTION ;");
			}
			catch(Exception $e) {
			}
		}
	}
	public function getAssocTableFormat($types) {
		sort($types);
		return ( implode("_", $types) );
	}
	public function addConstraint( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $dontCache = false ) {
		$table1 = $bean1->getMeta("type");
		$table2 = $bean2->getMeta("type");
		$writer = $this;
		$adapter = $this->adapter;
		$table = $this->getAssocTableFormat( array( $table1,$table2) );
		$idfield1 = $writer->getIDField($bean1->getMeta("type"));
		$idfield2 = $writer->getIDField($bean2->getMeta("type"));
		$property1 = $bean1->getMeta("type") . "_id";
		$property2 = $bean2->getMeta("type") . "_id";
		if ($property1==$property2) $property2 = $bean2->getMeta("type")."2_id";
		$table = $adapter->escape($table);
		$table1 = $adapter->escape($table1);
		$table2 = $adapter->escape($table2);
		$property1 = $adapter->escape($property1);
		$property2 = $adapter->escape($property2);
		$fkCode = "fk".md5($table.$property1.$property2);
		if (isset($this->fkcache[$fkCode])) return false;
		try {
			return $this->constrain($table, $table1, $table2, $property1, $property2, $dontCache);
		}
		catch(RedBean_Exception_SQL $e) {
			if (!$writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return false;
	}
	abstract protected function constrain($table, $table1, $table2, $p1, $p2, $cache);
}
class RedBean_QueryWriter_MySQL extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter {
	const C_DATATYPE_BOOL = 0;
	const C_DATATYPE_UINT8 = 1;
	const C_DATATYPE_UINT32 = 2;
	const C_DATATYPE_DOUBLE = 3;
	const C_DATATYPE_TEXT8 = 4;
	const C_DATATYPE_TEXT16 = 5;
	const C_DATATYPE_TEXT32 = 6;
	const C_DATATYPE_SPECIFIED = 99;
	public $typeno_sqltype = array(
			  RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL=>"  SET('1')  ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_UINT8=>" TINYINT(3) UNSIGNED ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32=>" INT(11) UNSIGNED ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_DOUBLE=>" DOUBLE ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT8=>" VARCHAR(255) ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT16=>" TEXT ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT32=>" LONGTEXT "
	);
	public $sqltype_typeno = array(
			  "set('1')"=>RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL,
			  "tinyint(3) unsigned"=>RedBean_QueryWriter_MySQL::C_DATATYPE_UINT8,
			  "int(11) unsigned"=>RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32,
			  "double" => RedBean_QueryWriter_MySQL::C_DATATYPE_DOUBLE,
			  "varchar(255)"=>RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT8,
			  "text"=>RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT16,
			  "longtext"=>RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT32
	);
	protected $adapter;
  	protected $quoteCharacter = '`';
	public function __construct( RedBean_Adapter $adapter ) {
		$this->adapter = $adapter;
		parent::__construct();
	}
	public function getTypeForID() {
		return self::C_DATATYPE_UINT32;
	}
	public function getTables() {
		return $this->adapter->getCol( "show tables" );
	}
	public function createTable( $table ) {
		$idfield = $this->safeColumn($this->getIDfield($table));
		$table = $this->safeTable($table);
		$sql = "
                     CREATE TABLE $table (
                    $idfield INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
                     PRIMARY KEY ( $idfield )
                     ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
				  ";
		$this->adapter->exec( $sql );
	}
	public function getColumns( $table ) {
		$table = $this->safeTable($table);
		$columnsRaw = $this->adapter->get("DESCRIBE $table");
		foreach($columnsRaw as $r) {
			$columns[$r["Field"]]=$r["Type"];
		}
		return $columns;
	}
	public function scanType( $value ) {
		if (is_null($value)) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL;
		}
		$value = strval($value);
		if ($value=="1" || $value=="") {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL;
		}
		if (is_numeric($value) && (floor($value)==$value) && $value >= 0 && $value <= 255 ) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_UINT8;
		}
		if (is_numeric($value) && (floor($value)==$value) && $value >= 0  && $value <= 4294967295 ) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32;
		}
		if (is_numeric($value)) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_DOUBLE;
		}
		if (strlen($value) <= 255) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT8;
		}
		return RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT16;
	}
	public function code( $typedescription ) {
		return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : self::C_DATATYPE_SPECIFIED);
	}
	public function widenColumn( $type, $column, $datatype ) {
		$table = $type;
		$type = $datatype;
		$table = $this->safeTable($table);
		$column = $this->safeColumn($column);
		$newtype = $this->getFieldType($type);
		$changecolumnSQL = "ALTER TABLE $table CHANGE $column $column $newtype ";
		$this->adapter->exec( $changecolumnSQL );
	}
	public function addUniqueIndex( $table,$columns ) {
		$table = $this->safeTable($table);
		sort($columns); 
		foreach($columns as $k=>$v) {
			$columns[$k]= $this->safeColumn($v);
		}
		$r = $this->adapter->get("SHOW INDEX FROM $table");
		$name = "UQ_".sha1(implode(',',$columns));
		if ($r) {
			foreach($r as $i) {
				if ($i["Key_name"]== $name) {
					return;
				}
			}
		}
		$sql = "ALTER IGNORE TABLE $table
                ADD UNIQUE INDEX $name (".implode(",",$columns).")";
		$this->adapter->exec($sql);
	}
	public function sqlStateIn($state, $list) {
		$sqlState = "0";
		if ($state == "42S02") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
		if ($state == "42S22") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN;
		if ($state == "23000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION;
		return in_array($sqlState, $list);
	}
	protected function constrain($table, $table1, $table2, $property1, $property2, $dontCache) {
		try{
			$writer = $this;
			$adapter = $this->adapter;
			$db = $adapter->getCell("select database()");
			$fkCode = "fk".md5($table.$property1.$property2);
			$fks =  $adapter->getCell("
				SELECT count(*)
				FROM information_schema.KEY_COLUMN_USAGE
				WHERE TABLE_SCHEMA ='$db' AND TABLE_NAME ='".$writer->getFormattedTableName($table)."' AND
				CONSTRAINT_NAME <>'PRIMARY' AND REFERENCED_TABLE_NAME is not null
					  ");
			if ($fks>0) return false;
			if (!$dontCache) $this->fkcache[ $fkCode ] = true;
			$columns = $writer->getColumns($table);
			if ($writer->code($columns[$property1])!==RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32) {
				$writer->widenColumn($table, $property1, RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32);
			}
			if ($writer->code($columns[$property2])!==RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32) {
				$writer->widenColumn($table, $property2, RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32);
			}
			$idfield1 = $writer->getIDField($table1);
			$idfield2 = $writer->getIDField($table2);
			$table = $writer->getFormattedTableName($table);
			$table1 = $writer->getFormattedTableName($table1);
			$table2 = $writer->getFormattedTableName($table2);
			$sql = "
				ALTER TABLE ".$writer->noKW($table)."
				ADD FOREIGN KEY($property1) references `$table1`($idfield1) ON DELETE CASCADE;
					  ";
			$adapter->exec( $sql );
			$sql ="
				ALTER TABLE ".$writer->noKW($table)."
				ADD FOREIGN KEY($property2) references `$table2`($idfield2) ON DELETE CASCADE
					  ";
			$adapter->exec( $sql );
			return true;
		}
		catch(Exception $e){
			return false;
		}
	}
}
class RedBean_QueryWriter_SQLiteT extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter {
	protected $adapter;
  	protected $quoteCharacter = '`';
	const C_DATATYPE_INTEGER = 0;
	const C_DATATYPE_NUMERIC = 1;
	const C_DATATYPE_TEXT = 2;
	const C_DATATYPE_SPECIFIED = 99;
	public $typeno_sqltype = array(
			  RedBean_QueryWriter_SQLiteT::C_DATATYPE_INTEGER=>"INTEGER",
			  RedBean_QueryWriter_SQLiteT::C_DATATYPE_NUMERIC=>"NUMERIC",
			  RedBean_QueryWriter_SQLiteT::C_DATATYPE_TEXT=>"TEXT",
	);
	public $sqltype_typeno = array(
			  "INTEGER"=>RedBean_QueryWriter_SQLiteT::C_DATATYPE_INTEGER,
			  "NUMERIC"=>RedBean_QueryWriter_SQLiteT::C_DATATYPE_NUMERIC,
			  "TEXT"=>RedBean_QueryWriter_SQLiteT::C_DATATYPE_TEXT,
	);
	public function __construct( RedBean_Adapter $adapter ) {
		$this->adapter = $adapter;
		parent::__construct($adapter);
	}
	public function getTypeForID() {
		return self::C_DATATYPE_INTEGER;
	}
	public function scanType( $value ) {
		if ($value===null) return self::C_DATATYPE_INTEGER; 
		if (is_numeric($value) && (intval($value)==$value) && $value<2147483648) return self::C_DATATYPE_INTEGER;
		if ((is_numeric($value) && $value < 2147483648)
				  || preg_match("/\d\d\d\d\-\d\d\-\d\d/",$value)
				  || preg_match("/\d\d\d\d\-\d\d\-\d\d\s\d\d:\d\d:\d\d/",$value)
		) {
			return self::C_DATATYPE_NUMERIC;
		}
		return self::C_DATATYPE_TEXT;
	}
	public function addColumn( $table, $column, $type) {
		$table = $this->getFormattedTableName($table);
		$column = $this->check($column);
		$table = $this->check($table);
		$type=$this->typeno_sqltype[$type];
		$sql = "ALTER TABLE `$table` ADD `$column` $type ";
		$this->adapter->exec( $sql );
	}
	public function code( $typedescription ) {
		return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : 99);
	}
	private function quote( $items ) {
		foreach($items as $k=>$item) {
			$items[$k]=$this->noKW($item);
		}
		return $items;
	}
	public function widenColumn( $type, $column, $datatype ) {
		$table = $type;
		$type = $datatype;
		$table = $this->getFormattedTableName($table);
		$idfield = $this->idfield;
		$column = $this->check($column);
		$table = $this->check($table);
		$newtype = $this->typeno_sqltype[$type];
		$oldColumns = $this->getColumns($table);
		$oldColumnNames = $this->quote(array_keys($oldColumns));
		$newTableDefStr="";
		foreach($oldColumns as $oldName=>$oldType) {
			if ($oldName != $idfield) {
				if ($oldName!=$column) {
					$newTableDefStr .= ",`$oldName` $oldType";
				}
				else {
					$newTableDefStr .= ",`$oldName` $newtype";
				}
			}
		}
		$q = array();
		$q[] = "DROP TABLE IF EXISTS tmp_backup;";
		$q[] = "CREATE TEMPORARY TABLE tmp_backup(".implode(",",$oldColumnNames).");";
		$q[] = "INSERT INTO tmp_backup SELECT * FROM `$table`;";
		$q[] = "DROP TABLE `$table`;";
		$q[] = "CREATE TABLE `$table` ( `$idfield` INTEGER PRIMARY KEY AUTOINCREMENT  $newTableDefStr  );";
		$q[] = "INSERT INTO `$table` SELECT * FROM tmp_backup;";
		$q[] = "DROP TABLE tmp_backup;";
		foreach($q as $sq) {
			$this->adapter->exec($sq);
		}
	}
	public function getTables() {
		return $this->adapter->getCol( "SELECT name FROM sqlite_master
			WHERE type='table' AND name!='sqlite_sequence';" );
	}
	public function createTable( $table ) {
		$idfield = $this->safeColumn($this->getIDfield($table));
		$table = $this->safeTable($table);
		$sql = "
                     CREATE TABLE $table ( $idfield INTEGER PRIMARY KEY AUTOINCREMENT )
				  ";
		$this->adapter->exec( $sql );
	}
	public function getColumns( $table ) {
		$table = $this->safeTable($table, true);
		$columnsRaw = $this->adapter->get("PRAGMA table_info('$table')");
		$columns = array();
		foreach($columnsRaw as $r) {
			$columns[$r["name"]]=$r["type"];
		}
		return $columns;
	}
	public function addUniqueIndex( $table,$columns ) {
		$table = $this->safeTable($table);
		$name = "UQ_".sha1(implode(',',$columns));
		$sql = "CREATE UNIQUE INDEX IF NOT EXISTS $name ON $table (".implode(",",$columns).")";
		$this->adapter->exec($sql);
	}
	public function sqlStateIn($state, $list) {
		$sqlState = "0";
		if ($state == "HY000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
		if ($state == "23000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION;
		return in_array($sqlState, $list);
	}
	public function wipe($type) {
		$table = $this->safeTable($type);
		$this->adapter->exec("DELETE FROM $table");
	}
	public function addFK( $type, $targetType, $field, $targetField) {
	}
	protected  function constrain($table, $table1, $table2, $property1, $property2, $dontCache) {
		try{
			$writer = $this;
			$adapter = $this->adapter;
			$fkCode = "fk".md5($table.$property1.$property2);
			$idfield1 = $writer->getIDField($table1);
			$idfield2 = $writer->getIDField($table2);
			$table = $writer->getFormattedTableName($table);
			$table1 = $writer->getFormattedTableName($table1);
			$table2 = $writer->getFormattedTableName($table2);
			$sql1 = "
				 CREATE TRIGGER IF NOT EXISTS {$fkCode}a
					BEFORE DELETE ON $table1
					FOR EACH ROW BEGIN
						DELETE FROM $table WHERE  $table.$property1 = OLD.$idfield1;
					END;
					  ";
			$sql2 = "
				CREATE TRIGGER IF NOT EXISTS {$fkCode}b
					BEFORE DELETE ON $table2
					FOR EACH ROW BEGIN
						DELETE FROM $table WHERE $table.$property2 = OLD.$idfield2;
					END;
					  ";
			$adapter->exec($sql1);
			$adapter->exec($sql2);
			return true;
		}
		catch(Exception $e){
			return false;
		}
	}
}
class RedBean_QueryWriter_PostgreSQL extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter {
	const C_DATATYPE_INTEGER = 0;
	const C_DATATYPE_DOUBLE = 1;
	const C_DATATYPE_TEXT = 3;
	public $typeno_sqltype = array(
			  self::C_DATATYPE_INTEGER=>" integer ",
			  self::C_DATATYPE_DOUBLE=>" double precision ",
			  self::C_DATATYPE_TEXT=>" text "
	);
	public $sqltype_typeno = array(
			  "integer"=>self::C_DATATYPE_INTEGER,
			  "double precision" => self::C_DATATYPE_DOUBLE,
			  "text"=>self::C_DATATYPE_TEXT
	);
	protected $adapter;
  protected $quoteCharacter = '"';
 	protected $defaultValue = 'DEFAULT';
	public function getTypeForID() {
		return self::C_DATATYPE_INTEGER;
	}
  protected function getInsertSuffix($table) {
    return "RETURNING ".$this->getIDField($table);
  }
	public function __construct( RedBean_Adapter_DBAdapter $adapter ) {
		$this->adapter = $adapter;
		parent::__construct();
	}
	public function getTables() {
		return $this->adapter->getCol( "select table_name from information_schema.tables
where table_schema = 'public'" );
	}
	public function createTable( $table ) {
		$idfield = $this->getIDfield($table);
		$table = $this->safeTable($table);
		$sql = " CREATE TABLE $table ($idfield SERIAL PRIMARY KEY); ";
		$this->adapter->exec( $sql );
	}
	public function getColumns( $table ) {
		$table = $this->safeTable($table, true);
		$columnsRaw = $this->adapter->get("select column_name, data_type from information_schema.columns where table_name='$table'");
		foreach($columnsRaw as $r) {
			$columns[$r["column_name"]]=$r["data_type"];
		}
		return $columns;
	}
	public function scanType( $value ) {
		if ($value===null || ($value instanceof RedBean_Driver_PDO_NULL) ||(is_numeric($value)
				  && floor($value)==$value
				  && $value < 2147483648
				  && $value > -2147483648)) {
			return self::C_DATATYPE_INTEGER;
		}
		elseif(is_numeric($value)) {
			return self::C_DATATYPE_DOUBLE;
		}
		else {
			return self::C_DATATYPE_TEXT;
		}
	}
	public function code( $typedescription ) {
		return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : 99);
	}
	public function widenColumn( $type, $column, $datatype ) {
		$table = $type;
		$type = $datatype;
		$table = $this->safeTable($table);
		$column = $this->safeColumn($column);
		$newtype = $this->typeno_sqltype[$type];
		$changecolumnSQL = "ALTER TABLE $table \n\t ALTER COLUMN $column TYPE $newtype ";
		try {
			$this->adapter->exec( $changecolumnSQL );
		}catch(Exception $e) {
			die($e->getMessage());
		}
	}
	public function checkChanges($type, $id, $logid) {
		$table = $this->safeTable($type);
		$idfield = $this->getIDfield($type);
		$id = (int) $id;
		$logid = (int) $logid;
		$num = $this->adapter->getCell("
        SELECT count(*) FROM __log WHERE tbl=$table AND itemid=$id AND action=2 AND $idfield > $logid");
		if ($num) {
			throw new RedBean_Exception_FailedAccessBean("Locked, failed to access (type:$type, id:$id)");
		}
		$newid = $this->insertRecord("__log",array("action","tbl","itemid"),
				  array(array(2,  $type, $id)));
		if ($this->adapter->getCell("select id from __log where tbl=:tbl AND id < $newid and id > $logid and action=2 and itemid=$id ",
		array(":tbl"=>$type))) {
			throw new RedBean_Exception_FailedAccessBean("Locked, failed to access II (type:$type, id:$id)");
		}
		return $newid;
	}
	public function addUniqueIndex( $table,$columns ) {
		$table = $this->safeTable($table, true);
		sort($columns); 
		foreach($columns as $k=>$v) {
			$columns[$k]=$this->safeColumn($v);
		}
		$r = $this->adapter->get("SELECT
									i.relname as index_name
								FROM
									pg_class t,
									pg_class i,
									pg_index ix,
									pg_attribute a
								WHERE
									t.oid = ix.indrelid
									AND i.oid = ix.indexrelid
									AND a.attrelid = t.oid
									AND a.attnum = ANY(ix.indkey)
									AND t.relkind = 'r'
									AND t.relname = '$table'
								ORDER BY  t.relname,  i.relname;");
		$name = "UQ_".sha1($table.implode(',',$columns));
		if ($r) {
			foreach($r as $i) {
				if (strtolower( $i["index_name"] )== strtolower( $name )) {
					return;
				}
			}
		}
		$sql = "ALTER TABLE \"$table\"
                ADD CONSTRAINT $name UNIQUE (".implode(",",$columns).")";
		$this->adapter->exec($sql);
	}
	public function sqlStateIn($state, $list) {
		$sqlState = "0";
		if ($state == "42P01") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
		if ($state == "42703") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN;
		if ($state == "23505") $sqlState = RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION;
		return in_array($sqlState, $list);
	}
	public function getSQLSnippetFilter( $idfield, $keys, $sql=null, $inverse=false ) {
		if (!$sql) $sql=" TRUE ";
		if (!$inverse && count($keys)===0) return " TRUE ";
		$idfield = $this->noKW($idfield);
		$sqlInverse = ($inverse) ? "NOT" : "";
		$sqlKeyFilter = ($keys) ? " $idfield $sqlInverse IN (".implode(",",$keys).") AND " : " ";
		$sqlSnippet = $sqlKeyFilter . $sql;
		return $sqlSnippet;
	}
	public function addFK( $type, $targetType, $field, $targetField) {
		try{
			$table = $this->safeTable($type);
			$column = $this->safeColumn($field);
			$tableNoQ = $this->safeTable($type,true);
			$columnNoQ = $this->safeColumn($field,true);
			$targetTable = $this->safeTable($targetType);
			$targetColumn  = $this->safeColumn($targetField);
			$fkCode = $tableNoQ.'_'.$columnNoQ.'_fkey';
			$sql = "
						SELECT
								c.oid,
								n.nspname,
								c.relname,
								n2.nspname,
								c2.relname,
								cons.conname
						FROM pg_class c
						JOIN pg_namespace n ON n.oid = c.relnamespace
						LEFT OUTER JOIN pg_constraint cons ON cons.conrelid = c.oid
						LEFT OUTER JOIN pg_class c2 ON cons.confrelid = c2.oid
						LEFT OUTER JOIN pg_namespace n2 ON n2.oid = c2.relnamespace
						WHERE c.relkind = 'r'
						AND n.nspname IN ('public')
						AND (cons.contype = 'f' OR cons.contype IS NULL)
						AND
						(  cons.conname = '{$fkCode}' )
					  ";
			$rows = $this->adapter->get( $sql );
			if (!count($rows)) {
				try{
					$this->adapter->exec("ALTER TABLE  $table
					ADD FOREIGN KEY (  $column ) REFERENCES  $targetTable (
					$targetColumn) ON DELETE NO ACTION ON UPDATE NO ACTION ;");
					return true;
				}
				catch(Exception $e) {
				}
			}
		}
		catch(Exception $e){
			return false;
		}
	}
	protected function constrain($table, $table1, $table2, $property1, $property2, $dontCache) {
		try{
			$writer = $this;
			$adapter = $this->adapter;
			$fkCode = "fk".md5($table.$property1.$property2);
			$sql = "
						SELECT
								c.oid,
								n.nspname,
								c.relname,
								n2.nspname,
								c2.relname,
								cons.conname
						FROM pg_class c
						JOIN pg_namespace n ON n.oid = c.relnamespace
						LEFT OUTER JOIN pg_constraint cons ON cons.conrelid = c.oid
						LEFT OUTER JOIN pg_class c2 ON cons.confrelid = c2.oid
						LEFT OUTER JOIN pg_namespace n2 ON n2.oid = c2.relnamespace
						WHERE c.relkind = 'r'
						AND n.nspname IN ('public')
						AND (cons.contype = 'f' OR cons.contype IS NULL)
						AND
						(  cons.conname = '{$fkCode}a'	OR  cons.conname = '{$fkCode}b' )
					  ";
			$rows = $adapter->get( $sql );
			if (!count($rows)) {
				$table = $writer->getFormattedTableName($table);
				$table1 = $writer->getFormattedTableName($table1);
				$table2 = $writer->getFormattedTableName($table2);
				if (!$dontCache) $this->fkcache[ $fkCode ] = true;
				$sql1 = "ALTER TABLE \"$table\" ADD CONSTRAINT
						  {$fkCode}a FOREIGN KEY ($property1)
							REFERENCES \"$table1\" (id) ON DELETE CASCADE ";
				$sql2 = "ALTER TABLE \"$table\" ADD CONSTRAINT
						  {$fkCode}b FOREIGN KEY ($property2)
							REFERENCES \"$table2\" (id) ON DELETE CASCADE ";
				$adapter->exec($sql1);
				$adapter->exec($sql2);
			}
			return true;
		}
		catch(Exception $e){
			return false;
		}
	}
}
class RedBean_Exception extends Exception {
}
class RedBean_Exception_SQL extends Exception {
	private $sqlState;
	public function getSQLState() {
		return $this->sqlState;
	}
	public function setSQLState( $sqlState ) {
		$this->sqlState = $sqlState;
	}
	public function __toString() {
		return "[".$this->getSQLState()."] - ".$this->getMessage();
	}
} 
class RedBean_Exception_Security extends RedBean_Exception {}
class RedBean_OODB extends RedBean_Observable {
	private $stash = NULL;
	private $writer;
	private $isFrozen = false;
	private $beanhelper = null;
	public function __construct( $writer ) {
		if ($writer instanceof RedBean_IceWriter) {
			$this->isFrozen = true;
			$this->writer = $writer;
		}
		elseif ($writer instanceof RedBean_QueryWriter) {
			$this->writer = $writer;
		}
		else {
			throw new RedBean_Exception_Security("Passing an invalid Query Writer");
		}
		$this->beanhelper = new RedBean_BeanHelperFacade();
	}
	public function freeze( $tf ) {
		$this->isFrozen = (bool) $tf;
	}
	public function isFrozen() {
		return (bool) $this->isFrozen;
	}
	public function dispense($type ) {
		$this->signal( "before_dispense", $type );
		$bean = new RedBean_OODBBean();
		$bean->setBeanHelper($this->beanhelper);
		$bean->setMeta("type", $type );
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		$bean->setMeta("sys.idfield",$idfield);
		$bean->$idfield = 0;
		if (!$this->isFrozen) $this->check( $bean );
		$bean->setMeta("tainted",true);
		$this->signal( "dispense", $bean );
		return $bean;
	}
	public function setBeanHelper( RedBean_IBeanHelper $beanhelper) {
		$this->beanhelper = $beanhelper;
	}
	public function check( RedBean_OODBBean $bean ) {
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		if (!isset($bean->$idfield) ) {
			throw new RedBean_Exception_Security("Bean has incomplete Meta Information $idfield ");
		}
		if (!($bean->getMeta("type"))) {
			throw new RedBean_Exception_Security("Bean has incomplete Meta Information II");
		}
		$pattern = '/[^abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_]/';
		if (preg_match($pattern,$bean->getMeta("type"))) {
			throw new RedBean_Exception_Security("Bean Type is invalid");
		}
		foreach($bean as $prop=>$value) {
			if (
					  is_array($value) ||
					  (is_object($value)) ||
					  strlen($prop)<1 ||
					  preg_match($pattern,$prop)
			) {
				throw new RedBean_Exception_Security("Invalid Bean: property $prop  ");
			}
		}
	}
	public function find($type,$conditions=array(),$addSQL=null) {
		try {
			$beans = $this->convertToBeans($type,$this->writer->selectRecord($type,$conditions,$addSQL));
			return $beans;
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN)
			)) throw $e;
		}
		return array();
	}
	public function tableExists($table) {
		$tables = $this->writer->getTables();
		return in_array($this->writer->getFormattedTableName($table), $tables);
	}
	protected function processBuildCommands($table, $property, RedBean_OODBBean $bean) {
		if ($inx = ($bean->getMeta("buildcommand.indexes"))) {
			if (isset($inx[$property])) $this->writer->addIndex($table,$inx[$property],$property);
		}
	}
	protected function processGroups( $originals, $current, $additions, $trashcan, $residue ) {
		return array(
			array_merge($additions,array_diff($current,$originals)),
			array_merge($trashcan,array_diff($originals,$current)),
			array_merge($residue,array_intersect($current,$originals))
		);
	}
	public function store( RedBean_OODBBean $bean ) {
		$processLists = false;
		foreach($bean as $k=>$v) {
			if (is_array($v) || is_object($v)) { $processLists = true; break; }
		}
		if (!$processLists && !$bean->getMeta('tainted')) return $bean->getID();
		$this->signal( "update", $bean );
		if ($processLists) {
		$sharedAdditions = $sharedTrashcan = $sharedresidue = $sharedItems = array();
		$ownAdditions = $ownTrashcan = $ownresidue = array();
		$tmpCollectionStore = array();
		$embeddedBeans = array();
		foreach($bean as $p=>$v) {
			if ($v instanceof RedBean_OODBBean) {
				$embtype = $v->getMeta('type');
				$idfield = $this->writer->getIDField($embtype);
				if (!$v->$idfield || $v->getMeta('tainted')) {
					$this->store($v);
				}
				$beanID = $v->$idfield;
				$linkField = $p.'_id';
				$bean->$linkField = $beanID;
				$bean->setMeta('cast.'.$linkField,'id');
				$embeddedBeans[$linkField] = $v;
				$tmpCollectionStore[$p]=$bean->$p;
				$bean->removeProperty($p);
			}
			if (is_array($v)) {
				$originals = $bean->getMeta('sys.shadow.'.$p);
				if (!$originals) $originals = array();
				if (strpos($p,'own')===0) {
					list($ownAdditions,$ownTrashcan,$ownresidue)=$this->processGroups($originals,$v,$ownAdditions,$ownTrashcan,$ownresidue);
					$bean->removeProperty($p);
				}
				elseif (strpos($p,'shared')===0) {
					list($sharedAdditions,$sharedTrashcan,$sharedresidue)=$this->processGroups($originals,$v,$sharedAdditions,$sharedTrashcan,$sharedresidue);
					$bean->removeProperty($p);
				}
				else {
				}
			}
		}
		}
		if (!$this->isFrozen) $this->check($bean);
		$table = $bean->getMeta("type");
		$idfield = $this->writer->getIDField($table);
		if ($bean->getMeta('tainted')) {
		if (!$this->isFrozen && !$this->tableExists($table)) {
			$this->writer->createTable( $table );
			$bean->setMeta("buildreport.flags.created",true);
		}
		if (!$this->isFrozen) {
			$columns = $this->writer->getColumns($table) ;
		}
		$insertvalues = array();
		$insertcolumns = array();
		$updatevalues = array();
		foreach( $bean as $p=>$v ) {
			if ($p!=$idfield) {
				if (!$this->isFrozen) {
					if ($bean->getMeta("cast.$p",-1)!==-1) {
						$cast = $bean->getMeta("cast.$p");
						if ($cast=="string") {
							$typeno = $this->writer->scanType("STRING");
						}
						elseif ($cast=="id") {
							$typeno = $this->writer->getTypeForID();
						}
						else {
							throw new RedBean_Exception("Invalid Cast");
						}
					}
					else {
						$typeno = $this->writer->scanType($v);
					}
					if (isset($columns[$p])) {
						$sqlt = $this->writer->code($columns[$p]);
						if ($typeno > $sqlt) {
							$this->writer->widenColumn( $table, $p, $typeno );
							$bean->setMeta("buildreport.flags.widen",true);
						}
					}
					else {
						$this->writer->addColumn($table, $p, $typeno);
						$bean->setMeta("buildreport.flags.addcolumn",true);
						$this->processBuildCommands($table,$p,$bean);
					}
				}
				$insertvalues[] = $v;
				$insertcolumns[] = $p;
				$updatevalues[] = array( "property"=>$p, "value"=>$v );
			}
		}
		if (!$this->isFrozen && ($uniques = $bean->getMeta("buildcommand.unique"))) {
			foreach($uniques as $unique) {
				$this->writer->addUniqueIndex( $table, $unique );
			}
		}
		$rs = $this->writer->updateRecord( $table, $updatevalues, $bean->$idfield );
		$bean->$idfield = $rs;
		$bean->setMeta("tainted",false);
		}
		if ($processLists) {
		foreach($embeddedBeans as $linkField=>$embeddedBean) {
			if (!$this->isFrozen) {
				$this->writer->addIndex($bean->getMeta('type'),
							'index_foreignkey_'.$embeddedBean->getMeta('type'),
							 $linkField);
				$this->writer->addFK($bean->getMeta('type'),$embeddedBean->getMeta('type'),$linkField,$this->writer->getIDField($embeddedBean->getMeta('type')));
			}
		}
		$myFieldLink = $bean->getMeta('type').'_id';
		foreach($ownTrashcan as $trash) {
			if ($trash instanceof RedBean_OODBBean) {
				$trash->$myFieldLink = null; 
				$this->store($trash);
			}
			else {
				throw new RedBean_Exception_Security('Array may only contain RedBean_OODBBeans');
			}
		}
		foreach($ownAdditions as $addition) {
			if ($addition instanceof RedBean_OODBBean) {
				$addition->$myFieldLink = $bean->$idfield;
				$addition->setMeta('cast.'.$myFieldLink,'id');
				$this->store($addition);
				if (!$this->isFrozen) {
					$this->writer->addIndex($addition->getMeta('type'),
						'index_foreignkey_'.$bean->getMeta('type'),
						 $myFieldLink);
					$this->writer->addFK($addition->getMeta('type'),$bean->getMeta('type'),$myFieldLink,$idfield);
				}
			}
			else {
				throw new RedBean_Exception_Security('Array may only contain RedBean_OODBBeans');
			}
		}
		foreach($ownresidue as $residue) {
			if ($residue instanceof RedBean_OODBBean) {
				if ($residue->getMeta('tainted')) {
					$this->store($residue);
				}
			}
			else {
				throw new RedBean_Exception_Security('Array may only contain RedBean_OODBBeans');
			}
		}
		foreach($sharedTrashcan as $trash) {
			if ($trash instanceof RedBean_OODBBean) {
				$this->assocManager->unassociate($trash,$bean);
			}
			else {
				throw new RedBean_Exception_Security('Array may only contain RedBean_OODBBeans');
			}
		}
		foreach($sharedAdditions as $addition) {
			if ($addition instanceof RedBean_OODBBean) {
				$this->assocManager->associate($addition,$bean);
			}
			else {
				throw new RedBean_Exception_Security('Array may only contain RedBean_OODBBeans');
			}
		}
		foreach($sharedresidue as $residue) {
			if ($residue instanceof RedBean_OODBBean) {
				$this->store($residue);
			}
			else {
				throw new RedBean_Exception_Security('Array may only contain RedBean_OODBBeans');
			}
		}
		}
		$this->signal( "after_update", $bean );
		return (int) $bean->$idfield;
	}
	public function load($type, $id) {
		$this->signal("before_open",array("type"=>$type,"id"=>$id));
		$bean = $this->dispense( $type );
		if ($this->stash && isset($this->stash[$id])) {
			$row = $this->stash[$id];
		}
		else {
			try {
				$idfield = $this->writer->getIDField($type);
				$rows = $this->writer->selectRecord($type,array($idfield=>array($id)));
			}catch(RedBean_Exception_SQL $e ) {
				if (
				$this->writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)
				) {
					$rows = 0;
					if ($this->isFrozen) throw $e; 
				}
				else throw $e;
			}
			if (!$rows) return $bean; 
			$row = array_pop($rows);
		}
		foreach($row as $p=>$v) {
			$bean->$p = $v;
		}
		$this->signal( "open", $bean );
		$bean->setMeta("tainted",false);
		return $bean;
	}
	public function trash( RedBean_OODBBean $bean ) {
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		$this->signal( "delete", $bean );
		if (!$this->isFrozen) $this->check( $bean );
		try {
			$this->writer->selectRecord($bean->getMeta("type"),
				array($idfield => array( $bean->$idfield) ),null,true );
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		$bean->$idfield = 0;
		$this->signal( "after_delete", $bean );
	}
	public function batch( $type, $ids ) {
		if (!$ids) return array();
		$collection = array();
		try {
			$idfield = $this->writer->getIDField($type);
			$rows = $this->writer->selectRecord($type,array($idfield=>$ids));
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
			$rows = false;
		}
		$this->stash = array();
		if (!$rows) return array();
		foreach($rows as $row) {
			$this->stash[$row[$this->writer->getIDField($type)]] = $row;
		}
		foreach($ids as $id) {
			$collection[ $id ] = $this->load( $type, $id );
		}
		$this->stash = NULL;
		return $collection;
	}
	public function convertToBeans($type, $rows) {
		$collection = array();
		$this->stash = array();
		foreach($rows as $row) { 
			$id = $row[$this->writer->getIDField($type)];
			$this->stash[$id] = $row;
			$collection[ $id ] = $this->load( $type, $id );
		}
		$this->stash = NULL;
		return $collection;
	}
	public function count($type) {
		try {
			return (int) $this->writer->count($type);
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return 0;
	}
	public function wipe($type) {
		try {
			$this->writer->wipe($type);
			return true;
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return false;
	}
	public function getAssociationManager() {
		if (!isset($this->assocManager)) throw new Exception("No association manager available.");
		return $this->assocManager;
	}
	public function setAssociationManager(RedBean_AssociationManager $assoc) {
		$this->assocManager = $assoc;
	}
}
class RedBean_ToolBox {
	protected $oodb;
	protected $writer;
	protected $adapter;
	public function __construct( RedBean_OODB $oodb, RedBean_Adapter $adapter, RedBean_QueryWriter $writer ) {
		$this->oodb = $oodb;
		$this->adapter = $adapter;
		$this->writer = $writer;
		return $this;
	}
	public function getWriter() {
		return $this->writer;
	}
	public function getRedBean() {
		return $this->oodb;
	}
	public function getDatabaseAdapter() {
		return $this->adapter;
	}
}
class RedBean_AssociationManager extends RedBean_Observable {
	protected $oodb;
	protected $adapter;
	protected $writer;
	protected $flagUseConstraints = true;
	public function __construct( RedBean_ToolBox $tools ) {
		$this->oodb = $tools->getRedBean();
		$this->adapter = $tools->getDatabaseAdapter();
		$this->writer = $tools->getWriter();
		$this->toolbox = $tools;
	}
	public function getTable( $types ) {
		return $this->writer->getAssocTableFormat($types);
	}
	public function associate(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2) {
		$table = $this->getTable( array($bean1->getMeta("type") , $bean2->getMeta("type")) );
		$bean = $this->oodb->dispense($table);
		return $this->associateBeans( $bean1, $bean2, $bean );
	}
	public function setUseConstraints( $trueFalse ) {
		$this->flagUseConstraints = $trueFalse;
	}
	protected function associateBeans(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, RedBean_OODBBean $bean) {
		$idfield1 = $this->writer->getIDField($bean1->getMeta("type"));
		$idfield2 = $this->writer->getIDField($bean2->getMeta("type"));
		$property1 = $bean1->getMeta("type") . "_id";
		$property2 = $bean2->getMeta("type") . "_id";
		if ($property1==$property2) $property2 = $bean2->getMeta("type")."2_id";
		$bean->setMeta("buildcommand.unique" , array(array($property1, $property2)));
		$indexName1 = "index_for_".$bean->getMeta("type")."_".$property1;
		$indexName2 = "index_for_".$bean->getMeta("type")."_".$property2;
		$bean->setMeta("buildcommand.indexes", array($property1=>$indexName1,$property2=>$indexName2));
		$this->oodb->store($bean1);
		$this->oodb->store($bean2);
		$bean->setMeta("assoc.".$bean1->getMeta("type"),$bean1);
		$bean->setMeta("assoc.".$bean2->getMeta("type"),$bean2);
		$bean->setMeta("cast.$property1","id");
		$bean->setMeta("cast.$property2","id");
		$bean->$property1 = $bean1->$idfield1;
		$bean->$property2 = $bean2->$idfield2;
		try {
			$id = $this->oodb->store( $bean );
			if ($this->flagUseConstraints &&
				!$this->oodb->isFrozen() &&
				$bean->getMeta("buildreport.flags.created")){
				$bean->setMeta("buildreport.flags.created",0);
				if (!$this->oodb->isFrozen())
				$this->writer->addConstraint( $bean1, $bean2 );
			}
			return $id;
		}
		catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION
			))) throw $e;
		}
	}
	public function related( RedBean_OODBBean $bean, $type, $getLinks=false, $sql=false) {
	$table = $this->getTable( array($bean->getMeta("type") , $type) );
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		if ($type==$bean->getMeta("type")) {
			$type .= "2";
			$cross = 1;
		}
		else $cross=0;
		if (!$getLinks) $targetproperty = $type."_id"; else $targetproperty="id";
		$property = $bean->getMeta("type")."_id";
		try {
				$sqlFetchKeys = $this->writer->selectRecord(
					  $table,
					  array( $property => array( $bean->$idfield ) ),
					  $sql,
					  false
				);
				$sqlResult = array();
				foreach( $sqlFetchKeys as $row ) {
					$sqlResult[] = $row[$targetproperty];
				}
				if ($cross) {
					$sqlFetchKeys2 = $this->writer->selectRecord(
							  $table,
							  array( $targetproperty => array( $bean->$idfield ) ),
							  $sql,
							  false
					);
					foreach( $sqlFetchKeys2 as $row ) {
						$sqlResult[] = $row[$property];
					}
				}
			return $sqlResult; 
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
			return array();
		}
	}
	public function unassociate(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $fast=null) {
		$this->oodb->store($bean1);
		$this->oodb->store($bean2);
		$table = $this->getTable( array($bean1->getMeta("type") , $bean2->getMeta("type")) );
		$idfield1 = $this->writer->getIDField($bean1->getMeta("type"));
		$idfield2 = $this->writer->getIDField($bean2->getMeta("type"));
		$type = $bean1->getMeta("type");
		if ($type==$bean2->getMeta("type")) {
			$type .= "2";
			$cross = 1;
		}
		else $cross = 0;
		$property1 = $type."_id";
		$property2 = $bean2->getMeta("type")."_id";
		$value1 = (int) $bean1->$idfield1;
		$value2 = (int) $bean2->$idfield2;
		try {
			$rows = $this->writer->selectRecord($table,array(
				$property1 => array($value1), $property2=>array($value2)),null,$fast
			);
			if ($cross) {
				$rows2 = $this->writer->selectRecord($table,array(
				$property2 => array($value1), $property1=>array($value2)),null,$fast
				);
				if ($fast) return;
				$rows = array_merge($rows,$rows2);
			}
			if ($fast) return;
			$beans = $this->oodb->convertToBeans($table,$rows);
			foreach($beans as $link) {
				$link->setMeta("assoc.".$bean1->getMeta("type"),$bean1);
				$link->setMeta("assoc.".$bean2->getMeta("type"),$bean2);
				$this->oodb->trash($link);
			}
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return;
	}
	public function clearRelations(RedBean_OODBBean $bean, $type) {
		$this->oodb->store($bean);
		$table = $this->getTable( array($bean->getMeta("type") , $type) );
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		if ($type==$bean->getMeta("type")) {
			$property2 = $type."2_id";
			$cross = 1;
		}
		else $cross = 0;
		$property = $bean->getMeta("type")."_id";
		try {
			$this->writer->selectRecord( $table, array($property=>array($bean->$idfield)),null,true);
			if ($cross) {
				$this->writer->selectRecord( $table, array($property2=>array($bean->$idfield)),null,true);
			}
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
	}
	public function areRelated(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2) {
		if (!$bean1->getID() || !$bean2->getID()) return false;
		$table = $this->getTable( array($bean1->getMeta("type") , $bean2->getMeta("type")) );
		$idfield1 = $this->writer->getIDField($bean1->getMeta("type"));
		$idfield2 = $this->writer->getIDField($bean2->getMeta("type"));
		$type = $bean1->getMeta("type");
		if ($type==$bean2->getMeta("type")) {
			$type .= "2";
			$cross = 1;
		}
		else $cross = 0;
		$property1 = $type."_id";
		$property2 = $bean2->getMeta("type")."_id";
		$value1 = (int) $bean1->$idfield1;
		$value2 = (int) $bean2->$idfield2;
		try {
			$rows = $this->writer->selectRecord($table,array(
				$property1 => array($value1), $property2=>array($value2)),null
			);
			if ($cross) {
				$rows2 = $this->writer->selectRecord($table,array(
				$property2 => array($value1), $property1=>array($value2)),null
				);
				$rows = array_merge($rows,$rows2);
			}
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
			return false;
		}
		return (count($rows)>0);
	}
}
class RedBean_ExtAssociationManager extends RedBean_AssociationManager {
	public function extAssociate(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, RedBean_OODBBean $baseBean ) {
		$table = $this->getTable( array($bean1->getMeta("type") , $bean2->getMeta("type")) );
		$baseBean->setMeta("type", $table );
		return $this->associateBeans( $bean1, $bean2, $baseBean );
	}
}
class RedBean_ViewManager {
	protected $oodb;
	protected $adapter;
	protected $writer;
	public function __construct( RedBean_ToolBox $tools ) {
		$this->oodb = $tools->getRedBean();
		$this->adapter = $tools->getDatabaseAdapter();
		$this->writer = $tools->getWriter();
	}
	public function createView( $viewID, $refType, $types ) {
		if ($this->oodb->isFrozen()) return false;
		$history = array();
		$tables = array_flip( $this->writer->getTables() );
		$refTable = $refType; 
		$currentTable = $refTable;
		$history[$refType] = $refType;
		foreach($types as $t) {
			if (!isset($history[$t])){ 
				$history[$t] = $t;
				$connection = array($t,$currentTable);
				sort($connection);
				$connection = implode("_", $connection);
				$connectionTable = $this->writer->safeTable($connection,true);
				if (isset($tables[$connectionTable])) {
					$srcPoint = $this->writer->safeTable($connection).".".$currentTable."_id"; 
					$dstPoint = $this->writer->safeTable($currentTable).".".$this->writer->safeColumn($this->writer->getIDField($currentTable)); 
					$joins[$connection] = array($srcPoint,$dstPoint);
					$srcPoint = $this->writer->safeTable($connection).".".$t."_id";
					$dstPoint = $this->writer->safeTable($t).".".$this->writer->safeColumn($this->writer->getIDField($t));
					$joins[$t] = array($srcPoint,$dstPoint);
				}
				else {
					$srcPoint = $this->writer->safeTable($t).".".$currentTable."_id";
					$dstPoint = $this->writer->safeTable($currentTable).".".$this->writer->safeColumn($this->writer->getIDField($currentTable));
					$joins[$t] = array($srcPoint,$dstPoint);
				}
			}
			$currentTable=$t;
		}
		try{
			$rs = (boolean) $this->writer->createView($refType,$joins,$viewID); 
		}
		catch(Exception $e) {
			throw new RedBean_Exception_SQL('Could not create view, types does not seem related (yet)..');
		}
		return $rs;
	}
}
class RedBean_Setup {
	private static $observers = array();
	private static $toolbox = NULL;
	private static function checkDSN($dsn) {
		$dsn = trim($dsn);
		$dsn = strtolower($dsn);
		if (
		strpos($dsn, "mysql:")!==0
				  && strpos($dsn,"sqlite:")!==0
				  && strpos($dsn,"pgsql:")!==0
		) {
			trigger_error("
					Support for this DSN has not been implemented yet. \n
					Begin your DSN with: 'mysql:' or 'sqlite:'
				");
		}
		else {
			return true;
		}
	}
	public static function kickstart( $dsn, $username=NULL, $password=NULL, $frozen=false ) {
		if ($dsn instanceof PDO) {
			$pdo = new RedBean_Driver_PDO($dsn);
			$dsn = $pdo->getDatabaseType() ;
		}
		else {
			self::checkDSN($dsn);
			$pdo = new RedBean_Driver_PDO( $dsn,$username,$password );
		}
		$adapter = new RedBean_Adapter_DBAdapter( $pdo );
		if (strpos($dsn,"pgsql")===0) {
			$writer = new RedBean_QueryWriter_PostgreSQL( $adapter );
		}
		else if (strpos($dsn,"sqlite")===0) {
			$writer = new RedBean_QueryWriter_SQLiteT( $adapter );
		}
		else {
			$writer = new RedBean_QueryWriter_MySQL( $adapter );
		}
		$redbean = new RedBean_OODB( $writer );
		if ($frozen) $redbean->freeze(true);
		$toolbox = new RedBean_ToolBox( $redbean, $adapter, $writer );
		self::$toolbox = $toolbox;
		return self::$toolbox;
	}
	public static function getAttachedObservers() {
		return self::$observers;
	}
}
interface RedBean_IBeanFormatter {
	public function formatBeanTable( $type );
	public function formatBeanID( $type );
	public function getAlias( $type );
}
class RedBean_DefaultBeanFormatter implements RedBean_IBeanFormatter {
	public function formatBeanTable( $type ){
		return $type;
	}
	public function formatBeanID( $type ){
		return 'id';
	}
	public function getAlias( $type ) {
		if ($t = RedBean_OODBBean::$fetchType) {
			$type = $t;
			RedBean_OODBBean::$fetchType = null;
		}
		return $type;
	}
}
interface RedBean_IModelFormatter {
	public function formatModel( $model );
}
interface RedBean_IBeanHelper {
	public function getToolbox();
}
class RedBean_BeanHelperFacade implements RedBean_IBeanHelper {
	public function getToolbox() {
		return R::$toolbox;
	}
}
class RedBean_Plugin_Optimizer implements RedBean_Observer {
	private $adapter;
	private $oodb;
	private $writer;
	protected $optimizers = array();
	public function __construct( RedBean_ToolBox $toolbox ) {
		$this->oodb = $toolbox->getRedBean();
		$this->adapter = $toolbox->getDatabaseAdapter();
		$this->writer = $toolbox->getWriter();
	}
	protected function optimize($table,$column,$value) {
		foreach($this->optimizers as $optimizer) {
			$optimizer->setTable($table);
			$optimizer->setColumn($column);
			$optimizer->setValue($value);
			if (!$optimizer->optimize()) break;
		}
	}
	public function onEvent( $event , $bean ) {
		try {
			if ($event=="update") {
				$arr = $bean->export(); 
				unset($arr["id"]); 
				if (count($arr)==0) return;
				$table = $bean->getMeta("type");
				$columns = array_keys($arr);
				$column = $columns[ array_rand($columns) ];
				$value = $arr[$column];
				$this->optimize($table,$column,$value);
			}
		}catch(RedBean_Exception_SQL $e) { }
	}
	public function addOptimizer(RedBean_Plugin_IOptimizer $optimizer) {
		$this->optimizers[] = $optimizer;
	}
}
interface RedBean_Plugin_IOptimizer {
	public function setTable($table);
	public function setColumn($column);
	public function setValue($value);
	public function optimize();
}
class RedBean_Plugin_Optimizer_Shrink implements RedBean_Plugin_IOptimizer {
	protected $table;
	protected $column;
	protected $value;
	protected $toolbox;
	protected $writer;
	protected $adapter;
	public function __construct( RedBean_ToolBox $toolbox ) {
		$this->writer = $toolbox->getWriter();
		$this->adapter = $toolbox->getDatabaseAdapter();
	}
	public function setTable( $table ) {
		$this->table = $table;
	}
	public function setColumn( $column ) {
		$this->column = $column;
	}
	public function setValue( $value ) {
		$this->value = $value;
	}
	public function optimize() {
		$type = $this->writer->scanType($this->value);
		$fields = $this->writer->getColumns($this->table);
		if (!in_array($this->column,array_keys($fields))) return false;
		$typeInField = $this->writer->code($fields[$this->column]);
		if ($type < $typeInField) {
			try {
				@$this->adapter->exec("alter table ".$this->writer->safeTable($this->table)." drop __test");
			}catch(Exception $e) {}
			$type = $this->writer->typeno_sqltype[$type];
			@$this->adapter->exec("alter table ".$this->writer->safeTable($this->table)." add __test ".$type);
			@$this->adapter->exec("update ".$this->writer->safeTable($this->table)." set __test=".$this->writer->safeColumn($this->column)."");
			$rows = $this->adapter->get("select ".$this->writer->safeColumn($this->column)." as a, __test as b from ".$this->writer->safeTable($this->table));
			$diff = 0;
			foreach($rows as $row) {
				$diff += ($row["a"]!=$row["b"]);
			}
			if (!$diff) {
				@$this->adapter->exec("alter table ".$this->writer->safeTable($this->table)." change ".$this->writer->safeColumn($this->column)." ".$this->writer->safeColumn($this->column)." ".$type);
			}
			@$this->adapter->exec("alter table ".$this->writer->safeTable($this->table)." drop __test");
		}
		return false;
	}
}
class RedBean_Plugin_Optimizer_Datetime  implements RedBean_Plugin_IOptimizer {
	protected $table;
	protected $column;
	protected $value;
	protected $toolbox;
	protected $writer;
	protected $adapter;
	public function __construct( RedBean_ToolBox $toolbox ) {
		$this->writer = $toolbox->getWriter();
		$this->adapter = $toolbox->getDatabaseAdapter();
	}
	public function setTable( $table ) {
		$this->table = $table;
	}
	public function setColumn( $column ) {
		$this->column = $column;
	}
	public function setValue( $value ) {
		$this->value = $value;
	}
	public function optimize() {
		if (!$this->matchesDateTime($this->value)) return true;
		$type = $this->writer->scanType($this->value);
		$fields = $this->writer->getColumns($this->table);
		if (!in_array($this->column,array_keys($fields))) return false;
		$typeInField = $this->writer->code($fields[$this->column]);
		if ($typeInField!="datetime") {
			if ($this->matchesDateTime($this->value)) {
				$cnt = (int) $this->adapter->getCell("select count(*) as n from ".$this->writer->safeTable($this->table)." where
						  {$this->column} regexp '[0-9]{4}-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]'
						  OR {$this->column} IS NULL");
				$total = (int) $this->adapter->getCell("SELECT count(*) FROM ".$this->writer->safeTable($this->table));
				if ($total===$cnt) { 
					$this->adapter->exec("ALTER TABLE ".$this->writer->safeTable($this->table)." change ".$this->writer->safeColumn($this->column)." ".$this->writer->safeColumn($this->column)." datetime ");
				}
				return false;
			}
			return true;
		}
		else {
			return false; 
		}
	}
	public function matchesDateTime($value) {
		$pattern = "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/";
		return (boolean) (preg_match($pattern, $value));
	}
}
class RedBean_Plugin_QueryLogger implements RedBean_Observer {
	protected $logs = array();
	public static function getInstanceAndAttach( RedBean_Observable $adapter ) {
		$queryLog = new RedBean_Plugin_QueryLogger;
		$adapter->addEventListener( "sql_exec", $queryLog );
		return $queryLog;
	}
	private function __construct(){}
	public function onEvent( $eventName, $adapter ) {
		if ($eventName=="sql_exec") {
			$sql = $adapter->getSQL();
			$this->logs[] = $sql;
		}
	}
	public function grep( $word ) {
		$found = array();
		foreach($this->logs as $log) {
			if (strpos($log,$word)!==false) {
				$found[] = $log;
			}
		}
		return $found;
	}
	public function getLogs() {
		return $this->logs;
	}
	public function clear() {
		$this->logs = array();
	}
}
class RedBean_SimpleModel {
	protected $bean;
	public function loadBean( RedBean_OODBBean $bean ) {
		$this->bean = $bean;
	}
	public function __get( $prop ) {
		return $this->bean->$prop;
	}
	public function __set( $prop, $value ) {
		$this->bean->$prop = $value;
	}
	public function __isset($key) {
		return (isset($this->bean->$key));
	}
	protected function getConnected($type) {
		return $this->bean->getMeta("assoc.$type");
	}
}
class RedBean_ModelHelper implements RedBean_Observer {
	private static $modelFormatter;
	public function onEvent( $eventName, $bean ) {
		$bean->$eventName();
	}
	public static function getModelName( $model ) {
		if (self::$modelFormatter){
			return self::$modelFormatter->formatModel($model);
		}
		else {
			return "Model_".ucfirst($model);
		}
	}
	public static function setModelFormatter( RedBean_IModelFormatter $modelFormatter ) {
		self::$modelFormatter = $modelFormatter;
	}
}
class R {
	public static $toolboxes = array();
	public static $toolbox;
	public static $redbean;
	public static $writer;
	public static $adapter;
	public static $associationManager;
	public static $extAssocManager;
	public static $currentDB = "";
	public static function getVersion() {
		return "2.0";
	}
	public static $flagFearless = true;
	public static function setup( $dsn="sqlite:/tmp/red.db", $username=NULL, $password=NULL ) {
		$facadeInstances = self::setupMultiple( array("default"=>array("dsn"=>$dsn,"username"=>$username,"password"=>$password,"frozen"=>false)));
		$facadeInstance = $facadeInstances["default"];
		self::configureFacadeWithToolbox(self::$toolboxes["default"]);
		return $facadeInstance;
	}
	public static function setupMultiple( $databases ) {
		$objects = array();
		foreach($databases as $key=>$database) {
			self::$toolboxes[$key] = RedBean_Setup::kickstart($database["dsn"],$database["username"],$database["password"],$database["frozen"]);
			$objects[$key] = new RedBean_FacadeHelper($key);
		}
		return $objects;
	}
	public static function addDatabase( $key, $dsn, $user, $pass=null, $frozen=false ) {
		self::$toolboxes[$key] = RedBean_Setup::kickstart($dsn,$user,$pass,$frozen);
	}
	public static function selectDatabase($key) {
		if (self::$currentDB===$key) return false;
		self::configureFacadeWithToolbox(self::$toolboxes[$key]);
		self::$currentDB = $key;
		return true;
	}
	public static function debug( $tf = true ) {
		self::$adapter->getDatabase()->setDebugMode( $tf );
	}
	public static function store( RedBean_OODBBean $bean ) {
		return self::$redbean->store( $bean );
	}
	public static function freeze( $tf = true ) {
		self::$redbean->freeze( $tf );
	}
	public static function load( $type, $id ) {
		return self::$redbean->load( $type, $id );
	}
	public static function trash( RedBean_OODBBean $bean ) {
		return self::$redbean->trash( $bean );
	}
	public static function dispense( $type, $num = 1 ) {
		if ($num==1) {
			return self::$redbean->dispense( $type );
		}
		else {
			$beans = array();
			for($v=0; $v<$num; $v++) $beans[] = self::$redbean->dispense( $type );
			return $beans;
		}
	}
	public static function loadOrDispense( $type, $id = 0 ) {
		return ($id ? R::load($type,(int)$id) : R::dispense($type));
	}
	public static function findOrDispense( $type, $sql, $values ) {
		$foundBeans = self::find($type,$sql,$values);
		if (count($foundBeans)==0) return array(self::dispense($type)); else return $foundBeans;
	}
	public static function associate( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $extra = null ) {
		if (!$extra) {
			return self::$associationManager->associate( $bean1, $bean2 );
		}
		else{
			if (!is_array($extra)) {
				$info = json_decode($extra,true);
				if (!$info) $info = array("extra"=>$extra);
			}
			else {
				$info = $extra;
			}
			$bean = R::dispense("typeLess");
			$bean->import($info);
			return self::$extAssocManager->extAssociate($bean1, $bean2, $bean);
		}
	}
	public static function unassociate( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2 , $fast=false) {
		return self::$associationManager->unassociate( $bean1, $bean2, $fast );
	}
	public static function related( RedBean_OODBBean $bean, $type, $sql=null, $values=array()) {
		$keys = self::$associationManager->related( $bean, $type );
		if (count($keys)==0) return array();
		if (!$sql) return self::batch($type, $keys);
		$idfield = self::$writer->getIDField( $type );
		$rows = self::$writer->selectRecord( $type, array($idfield=>$keys),array($sql,$values),false );
		return self::$redbean->convertToBeans($type,$rows);
	}
	public static function areRelated( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2) {
		return self::$associationManager->areRelated($bean1,$bean2);
	}
	public static function unrelated(RedBean_OODBBean $bean, $type, $sql=null, $values=array()) {
		$idfield = self::$writer->getIDField( $type );
		$keys = self::$associationManager->related( $bean, $type );
		$rows = self::$writer->selectRecord( $type, array($idfield=>$keys), array($sql,$values), false, true );
		return self::$redbean->convertToBeans($type,$rows);
	}
	public static function relatedOne( RedBean_OODBBean $bean, $type, $sql='1', $values=array() ) {
		$beans = self::related($bean, $type, $sql, $values);
		if (count($beans)==0) return null;
		return reset( $beans );
	}
	public static function clearRelations( RedBean_OODBBean $bean, $type, RedBean_OODBBean $bean2 = null, $extra = null ) {
		$r = self::$associationManager->clearRelations( $bean, $type );
		if ($bean2) {
			self::associate($bean, $bean2, $extra);
		}
		return $r;
	}
	public static function find( $type, $sql="1", $values=array() ) {
		return self::$redbean->find($type,array(),array($sql,$values));
	}
	public static function findAndExport($type, $sql="1", $values=array()) {
		$items = self::find( $type, $sql, $values );
		$arr = array();
		foreach($items as $key=>$item) {
			$arr[$key]=$item->export();
		}
		return $arr;
	}
	public static function findOne( $type, $sql="1", $values=array()) {
		$items = self::find($type,$sql,$values);
		return reset($items);
	}
	public static function findLast( $type, $sql="1", $values=array() ) {
		$items = self::find( $type, $sql, $values );
		return end( $items );
	}
	public static function batch( $type, $ids ) {
		return self::$redbean->batch($type, $ids);
	}
	public static function exec( $sql, $values=array() ) {
		if (!self::$redbean->isFrozen()) {
			try {
				$rs = R::$adapter->exec( $sql, $values );
			}catch(RedBean_Exception_SQL $e) {
				if(self::$writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)) {
					return NULL;
				}
				else {
					throw $e;
				}
			}
			return $rs;
		}
		else {
			return R::$adapter->exec( $sql, $values );
		}
	}
	public static function getAll( $sql, $values=array() ) {
		if (!self::$redbean->isFrozen()) {
			try {
				$rs = R::$adapter->get( $sql, $values );
			}catch(RedBean_Exception_SQL $e) {
				if(self::$writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)) {
					return array();
				}
				else {
					throw $e;
				}
			}
			return $rs;
		}
		else {
			return R::$adapter->get( $sql, $values );
		}
	}
	public static function getCell( $sql, $values=array() ) {
		if (!self::$redbean->isFrozen()) {
			try {
				$rs = R::$adapter->getCell( $sql, $values );
			}catch(RedBean_Exception_SQL $e) {
				if(self::$writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)) {
					return NULL;
				}
				else {
					throw $e;
				}
			}
			return $rs;
		}
		else {
			return R::$adapter->getCell( $sql, $values );
		}
	}
	public static function getRow( $sql, $values=array() ) {
		if (!self::$redbean->isFrozen()) {
			try {
				$rs = R::$adapter->getRow( $sql, $values );
			}catch(RedBean_Exception_SQL $e) {
				if(self::$writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)) {
					return array();
				}
				else {
					throw $e;
				}
			}
			return $rs;
		}
		else {
			return R::$adapter->getRow( $sql, $values );
		}
	}
	public static function getCol( $sql, $values=array() ) {
		if (!self::$redbean->isFrozen()) {
			try {
				$rs = R::$adapter->getCol( $sql, $values );
			}catch(RedBean_Exception_SQL $e) {
				if(self::$writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)) {
					return array();
				}
				else {
					throw $e;
				}
			}
			return $rs;
		}
		else {
			return R::$adapter->getCol( $sql, $values );
		}
	}
	public static function copy($bean, $associatedBeanTypesStr="") {
		$type = $bean->getMeta("type");
		$copy = R::dispense($type);
		$copy->import( $bean->export() );
		$copy->copyMetaFrom( $bean );
		$copy->id = 0;
		R::store($copy);
		$associatedBeanTypes = explode(",",$associatedBeanTypesStr);
		foreach($associatedBeanTypes as $associatedBeanType) {
			$assocBeans = R::related($bean, $associatedBeanType);
			foreach($assocBeans as $assocBean) {
				R::associate($copy,$assocBean);
			}
		}
		$copy->setMeta("original",$bean);
		return $copy;
	}
	public static function swap( $beans, $property ) {
		$bean1 = array_shift($beans);
		$bean2 = array_shift($beans);
		$tmp = $bean1->$property;
		$bean1->$property = $bean2->$property;
		$bean2->$property = $tmp;
		R::store($bean1);
		R::store($bean2);
	}
	public static function convertToBeans($type,$rows) {
		return self::$redbean->convertToBeans($type,$rows);
	}
	public static $flagUseLegacyTaggingAPI = false;
	public static function hasTag($bean, $tags, $all=false) {
		$foundtags = R::tag($bean);
		if (is_string($foundtags)) $foundtags = explode(",",$tags);
		$same = array_intersect($tags,$foundtags);
		if ($all) {
			return (implode(",",$same)===implode(",",$tags));
		}
		return (bool) (count($same)>0);
	}
	public static function untag($bean,$tagList) {
		if ($tagList!==false && !is_array($tagList)) $tags = explode( ",", (string)$tagList); else $tags=$tagList;
		foreach($tags as $tag) {
			$t = R::findOne("tag"," title = ? ",array($tag));
			if ($t) {
				R::unassociate( $bean, $t );
			}
		}
	}
	public static function tag( RedBean_OODBBean $bean, $tagList = null ) {
		if (is_null($tagList)) {
			$tags = R::related( $bean, "tag");
			$foundTags = array();
			foreach($tags as $tag) {
				$foundTags[] = $tag->title;
			}
			if (self::$flagUseLegacyTaggingAPI) return implode(",",$foundTags);
			return $foundTags;
		}
		if ($tagList!==false && !is_array($tagList)) $tags = explode( ",", (string)$tagList); else $tags=$tagList;
		R::clearRelations( $bean, "tag" );
		if ($tagList===false) return;
		foreach($tags as $tag) {
			$t = R::findOne("tag"," title = ? ",array($tag));
			if (!$t) {
				$t = R::dispense("tag");
				$t->title = $tag;
				R::store($t);
			}
			R::associate( $bean, $t ); 
		}
	}
	public static function tagged( $beanType, $tagList ) {
		if ($tagList!==false && !is_array($tagList)) $tags = explode( ",", (string)$tagList); else $tags=$tagList;
		$collection = array();
		foreach($tags as $tag) {
			$retrieved = array();
			$tag = R::findOne("tag"," title = ? ", array($tag));
			if ($tag) $retrieved = R::related($tag, $beanType);
			foreach($retrieved as $key=>$bean) $collection[$key]=$bean;
		}
		return $collection;
	}
	public static function wipe( $beanType ) {
		R::$redbean->wipe($beanType);
	}
	public static function count( $beanType ) {
		return R::$redbean->count($beanType);
	}
	public static function configureFacadeWithToolbox( RedBean_ToolBox $tb ) {
		$oldTools = self::$toolbox;
		self::$toolbox = $tb;
		self::$writer = self::$toolbox->getWriter();
		self::$adapter = self::$toolbox->getDatabaseAdapter();
		self::$redbean = self::$toolbox->getRedBean();
		self::$associationManager = new RedBean_AssociationManager( self::$toolbox );
		self::$redbean->setAssociationManager(self::$associationManager);
		self::$extAssocManager = new RedBean_ExtAssociationManager( self::$toolbox );
		$helper = new RedBean_ModelHelper();
		self::$redbean->addEventListener("update", $helper );
		self::$redbean->addEventListener("open", $helper );
		self::$redbean->addEventListener("delete", $helper );
		self::$associationManager->addEventListener("delete", $helper );
		self::$redbean->addEventListener("after_delete", $helper );
		self::$redbean->addEventListener("after_update", $helper );
		self::$redbean->addEventListener("dispense", $helper );
		return $oldTools;
	}
	public static function cooker($arr) {
		return RedBean_Cooker::load($arr, R::$toolbox);
	}
	public static function view($viewID, $types) {
		if (self::$redbean->isFrozen()) return false;
		$types = explode(",",$types);
		if (count($types)<2) throw new RedBean_Exception_Security("Creating useless view for just one type? Provide at least two types!");
		$refType = array_shift($types);
		$viewManager = new RedBean_ViewManager( self::$toolbox );
		return $viewManager->createView($viewID,$refType,$types);
	}
	public static function exportAll($beans) {
		$array = array();
		foreach($beans as $bean) {
			if ($bean instanceof RedBean_OODBBean) {
				$array[] = $bean->export();
			}
		}
		return $array;
	}
	public static function begin() {
		self::$adapter->startTransaction();
	}
	public static function commit() {
		self::$adapter->commit();
	}
	public static function rollback() {
		self::$adapter->rollback();
	}
	public static function getColumns($table) {
		return self::$writer->getColumns($table);
	}
	public static function now() {
		return date('Y-m-d H:i:s');
	}
}
class RedBean_FacadeHelper {
	private $key;
	public function __construct($key) {
		$this->key = $key;
	}
	public function __call($func,$args) {
		R::selectDatabase($this->key);
		$func = "R::$func";
		return call_user_func_array($func,$args);
	}
}
class RedBean_BeanCan {
	private $modelHelper;
	public function __construct() {
		$this->modelHelper = new RedBean_ModelHelper;
	}
	private function resp($result=null, $id=null, $errorCode="-32603",$errorMessage="Internal Error") {
		$response = array(
			"jsonrpc"=>"2.0",
		);
		if ($id) {
			$response["id"] = $id;
		}
		if ($result) {
			$response["result"]=$result;
		}
		else {
			$response["error"] = array(
				"code"=>$errorCode,
				"message"=>$errorMessage
			);
		}
		return (json_encode($response));
	}
	public function handleJSONRequest( $jsonString ) {
		$jsonArray = json_decode($jsonString,true);
		if (!$jsonArray) return $this->resp(null,null,-32700,"Cannot Parse JSON");
		if (!isset($jsonArray["jsonrpc"])) return $this->resp(null,null,-32600,"No RPC version");
		if (($jsonArray["jsonrpc"]!="2.0")) return $this->resp(null,null,-32600,"Incompatible RPC Version");
		if (!isset($jsonArray["id"])) return $this->resp(null,null,-32600,"No ID");
		$id = $jsonArray["id"];
		if (!isset($jsonArray["method"])) return $this->resp(null,$id,-32600,"No method");
		if (!isset($jsonArray["params"])) {
			$data = array();
		}
		else {
			$data = $jsonArray["params"];
		}
		$method = explode(":",trim($jsonArray["method"]));
		if (count($method)!=2) {
			return $this->resp(null, $id, -32600,"Invalid method signature. Use: BEAN:ACTION");
		}
		$beanType = $method[0];
		$action = $method[1];
		if (preg_match("/\W/",$beanType)) return $this->resp(null, $id, -32600,"Invalid Bean Type String");
		if (preg_match("/\W/",$action)) return $this->resp(null, $id, -32600,"Invalid Action String");
		try {
			switch($action) {
				case "store":
					if (!isset($data[0])) return $this->resp(null, $id, -32602,"First param needs to be Bean Object");
					$data = $data[0];
					if (!isset($data["id"])) $bean = R::dispense($beanType); else
						$bean = R::load($beanType,$data["id"]);
					$bean->import( $data );
					$rid = R::store($bean);
					return $this->resp($rid, $id);
					break;
				case "load":
					if (!isset($data[0])) return $this->resp(null, $id, -32602,"First param needs to be Bean ID");
					$bean = R::load($beanType,$data[0]);
					return $this->resp($bean->export(),$id);
					break;
				case "trash":
					if (!isset($data[0])) return $this->resp(null, $id, -32602,"First param needs to be Bean ID");
					$bean = R::load($beanType,$data[0]);
					R::trash($bean);
					return $this->resp("OK",$id);
					break;
				default:
					$modelName = $this->modelHelper->getModelName( $beanType );
					if (!class_exists($modelName)) return $this->resp(null, $id, -32601,"No such bean in the can!");
					$beanModel = new $modelName;
					if (!method_exists($beanModel,$action)) return $this->resp(null, $id, -32601,"Method not found in Bean: $beanType ");
					return $this->resp( call_user_func_array(array($beanModel,$action), $data), $id);
			}
		}
		catch(Exception $exception) {
			return $this->resp(null, $id, -32099,$exception->getCode()."-".$exception->getMessage());
		}
	}
}
class RedBean_Cooker {
	public static function load($post, RedBean_ToolBox $toolbox) {
		$writer = $toolbox->getWriter();
		if (isset($post["associations"])) {
			$associations = $post["associations"];
			unset($post["associations"]);
		}
		$can = $pairs = $sorted = array();
		foreach($post as $key => $rawBean) {
			if (is_array($rawBean) && isset($rawBean["type"])) {
				$type = $rawBean["type"];
				unset($rawBean["type"]);
				$idfield = $writer->getIDField($type);
				if (isset($rawBean[$idfield])) {
					$id = $rawBean[$idfield];
					if ($id==0 && count($rawBean)===1) continue;
					unset($rawBean[$idfield]);
					$bean = R::load($type, $id);
				}
				else { 
					$bean = R::dispense($type);
				}
				foreach($rawBean as $field=>$value){
					if (!empty($value)) $bean->$field = $value;
				}
				$can[$key]=$bean;
				if (!isset($sorted[$type]))  $sorted[$type]=array();
				$sorted[$type][]=$bean;
			}
		}
		if (isset($associations) && is_array($associations)) {
			foreach($associations as $assoc) {
				foreach($assoc as $info) {
					if ($info=="0" || $info=="") continue;
					$keys = explode("-", $info);
					if (isset($can[$keys[0]])) $bean1 = $can[$keys[0]]; else {
						$loader = explode(":",$keys[0]);
						$bean1 = R::load( $loader[0], $loader[1] );
					} 
					$bean2 = $can[$keys[1]];
					$pairs[] = array( $bean1, $bean2 );
				}
			}
		}
		return array(
			"can"=>$can, 
			"pairs"=>$pairs, 
			"sorted"=>$sorted 
		);
	}
	public function setToolbox(RedBean_Toolbox $toolbox) {
		$this->toolbox = $toolbox;
		$this->redbean = $this->toolbox->getRedbean();
	}
	public function graph( $array ) {
		$beans = array();
		if (is_array($array) && isset($array["type"])) {
			$type = $array["type"];
			unset($array["type"]);
			if (isset($array["id"])) {
				$id = (int) $array["id"];
				$bean = $this->redbean->load($type,$id);
			}
			else {
				$bean = $this->redbean->dispense($type);
			}
			foreach($array as $property=>$value) {
				if (is_array($value)) {
					$bean->$property = $this->graph($value);
				}
				else {
					$bean->$property = $value;
				}
			}
			return $bean;
		}
		elseif (is_array($array)) {
			foreach($array as $key=>$value) {
				$beans[$key] = $this->graph($value);
			}
			return $beans;
		}
		else {
			return $array;
		}
		return $beans;
	}
}
