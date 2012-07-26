<?php
/**
 * This file contains the HTML Table printer.
 * 
 * I wrote this file based upon the csv formatter...
 * 
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/**
 * This class inherits from the abstract Formatter. It will return our resultobject into a
 * html table datastructure.
 */
class HtmlTableFormatter extends AFormatter {

    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    public function printHeader() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: text/html; charset=UTF-8");
    }

    /**
     * encloses the $element in double quotes
     */
    private function escape($element) {
        return htmlspecialchars($element);
    }

    public function printBody() {
        $keys = array_keys(get_object_vars($this->objectToPrint));
        $key = $keys[0];
        $this->objectToPrint = $this->objectToPrint->$key;

        if (!is_array($this->objectToPrint)) {
            throw new FormatNotAllowedTDTException("You can only request a HTML-table on an array", array("CSV", "json", "rdf", "xml", "n3", "ttl"));
        }
        if (isset($this->objectToPrint[0])) {
            //print the header row
            $headerrow = array();
            if (is_object($this->objectToPrint[0])) {
                $headerrow = array_keys(get_object_vars($this->objectToPrint[0]));
            } else {
                $headerrow = array_keys($this->objectToPrint[0]);
            }

            // we're going to escape all of our fields
            $enclosedHeaderrow = array();

            foreach ($headerrow as $element) {
                array_push($enclosedHeaderrow, $this->escape($element));
            }
            
            echo "<html>\n".
                 "  <head>\n".
                 "    <title>Table Formatter</title>\n".
                 "    <style>\n".
                 "      table td {border: 1px solid grey}".
                 "      table th {background-color:#FFFFEE;}".
                 "    </style>\n".
                 "  </head>\n".
                 "  <body>\n".
                 "\n".
                 "    <table>\n".
                 "      <tr>\n".
                 "        <th>".implode("</th>\n        <th>", $enclosedHeaderrow)."</th>\n".
                 "      </tr>\n";

            foreach ($this->objectToPrint as $row) {
                echo "      <tr>\n";
                if (is_object($row)) {
                    $row = get_object_vars($row);
                }

                foreach ($row as $element) {
                    echo "        <td>";
                    if (is_object($element)) {
                        if (isset($element->id)) {
                            echo $element->id;
                        } else if (isset($element->name)) {
                            echo $element->name;
                        } else {
                            echo "OBJECT";
                        }
                    } elseif (is_array($element)) {
                        if (isset($element["id"])) {
                            echo $element["id"];
                        } else if (isset($element["name"])) {
                            echo $element["name"];
                        } else {
                            echo "ARRAY";
                        }
                    } else {
                        echo $this->escape($element);
                    }
                    echo "</td>\n";
                }
                echo "      </tr>\n";
            }
            echo "    </table>\n".
                 "\n".
                 "  </body>\n".
                 "</html>";
        }
    }

    public static function getDocumentation() {
        return "A Html Table formater, works only on arrays...";
    }

}
?>
