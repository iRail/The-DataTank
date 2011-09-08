<?php

include_once('rb.php');
include_once('Config.class.php');

/**
 * This class parses tabular data and imports it into a custom defined table.
 * NOTE: Be carefull with whitespace and trailing newlines.
 */
class TabularDataImporter {
    private $data;
    private $text;
    private $tableName;
    private $format;
    private $columns;
    private $delimiter;

    /**
     * The delimiter should be ',' if the data looks like this:
     * a,b,c,d
     * not:
     * a, b, c, d
     */
    public function __construct($tableName, $format, $columns, $delimiter=',') {
        $this->data = array();
        $this->tableName = 'custom__' . $tableName;
        $this->format = $format;
        $this->columns = $columns;
        $this->delimiter = $delimiter;
    }

    /**
     * This function will create a custom table for tabular data.
     * TODO Add int, date and float type, use $format for this.
     * TODO Add primary key with *;
     * TODO Use columns, how to escape?
     * TODO Try to use rb models for defining a table.
     * TODO Escape tableName.
     */
    private function createCustomTable() {
        // create the custom table.
        $sql = 'create table if not exists ' . $this->tableName . " (\n" .
            'id int not null auto_increment,';
        $i = 0;
        while ($i<strlen($this->format)) {
            $sql .= 'column' . $i . ' varchar(255) not null, ';
            $i++;
        }
        $sql .= 'primary key (id));';

        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        R::exec($sql);

        // add custom table to custom_tables table.
        $table = R::dispense('custom_tables');
        $table->name = $this->tableName;
        $table->format = $this->format;
        $table->columns = $this->columns;
        $id = R::store($table);

        return R::load('custom_tables', $id);
    }

    /**
     * Create a custom table if it doesn't already exist and then save all the 
     * rows to the table.
     *
     * The custom_tables holds all custom tables. It has the schema:
     * - name string varchar(255),
     * - format varchar(255),
     * - columns varchar(255)
     */
    public function save($text) {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $table = R::findOne(
            'custom_tables',
            'name = :name and format = :format and columns = :columns',
            array(':name' => $this->tableName,
                  ':format' => $this->format,
                  ':columns' => $this->columns
            )
        );
        if (!$table) {
            $table = $this->createCustomTable();
        } else if ($table->format != $this->format or $table->columns != $this->columns) {
            throw new Exception('The format of the data does not comply with the ' .
                'existing schema of ' . $this->tableName);
        }
        foreach(explode("\n", $text) as $line) {
            $values = explode($this->delimiter, $line);
            if (count($values) != strlen($table->format)) {
                throw new Exception('The length of the row is not the same as the ' .
                    'length defined in $table->format.');
                break;
            }
            $i = 0;
            $row = R::dispense($this->tableName);
            foreach ($values as $value) {
                $attribute = 'column' . $i;
                $row->$attribute = $value;
                $i++;
            }
            R::store($row);
        }
    }
}

function test_importer() {
    $csv = "Werner,a,b,c,10\n" 
         . "Pieter,d,e,f,5\n"
         . "Jan,g,h,i,12";
    $importer = new TabularDataImporter('coders', 'ssssi', 'name,fst,snd,thrd,nr');
    $importer->save($csv);

    R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
    $coders = R::find('custom__coders');
    
    foreach($coders as $coder) {
        print $coder . "\n";
    }

    R::exec('delete from custom_tables where name=custom__coders');
    R::exec('drop table custom__coders');
}

test_importer();

?>
