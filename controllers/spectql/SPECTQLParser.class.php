<?php
/**
 * Parser a SPECTQL query and generates a stack of expressions. It throw SPECTQLParseTDTExceptions 
 *
 * @package The-Datatank/controllers/spectql
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */

include_once("lib/parse_engine.php");
include_once("controllers/spectql/SPECTQLTokenizer.class.php");
include_once("controllers/spectql/filters/SPECTQLGeoFilter.class.php");
include_once("controllers/spectql/filters/SPECTQLFilter.class.php");
include_once("controllers/spectql/filters/SPECTQLFilterList.class.php");
include_once("controllers/spectql/selectors/SPECTQLSelector.class.php");
include_once("controllers/spectql/selectors/AArgument.class.php");
include_once("controllers/spectql/selectors/SPECTQLLink.class.php");
include_once("controllers/spectql/selectors/SPECTQLWildcard.class.php");
include_once("controllers/spectql/selectors/SPECTQLColumnName.class.php");
include_once("controllers/spectql/functions/FunctionFactory.class.php");
include_once("controllers/spectql/functions/AFunction.class.php");
include_once("controllers/spectql/parseexceptions.php");
include_once("controllers/spectql/SPECTQLResource.class.php");
include_once("controllers/spectql/SPECTQLTools.class.php");
include_once("controllers/spectql/spectql.class");

class SPECTQLParser{
    private $querystring;

    /**
     * Provides the link with the parser character
     */ 
    private static $symbols = array(
        "." => "'.'",
        ">" => "'>'",
        "<" => "'<'",
        "==" => "EQ",
        ">=" => "GE",
        "<=" => "LE",
        "!=" => "NE",
        ":=" => "ALIAS",
        "~" => "'~'",
        "(" => "'('",
        ")" => "')'",
        "{" => "'{'",
        "}" => "'}'",
        "=>" => "LN",
        "|" => "'|'",
        "&" => "'&'",
        "!" => "'!'",
        "+" => "'+'",
        "-" => "'-'",
        "/" => "'/'",
        "*" => "'*'",
        ":" => "':'",
        "," => "','",
        "^" => "'^'",
        "%" => "'%'",
        "?" => "'?'",
        "'" => "SQ"
    );
    
    /**
     * An $expression is a string containing all information after a /
     * For instance: http://datatank.demo.ibbt.be/spectql/Belgium.{Zonenr,count(Zonenaam), avg(PostNr)}
     */
    public function __construct($querystring) {
        //url decode
        $this->querystring = ltrim(urldecode($querystring),"/");
    }

    public function interpret(){
        $querystring= $this->querystring;
        $tokenizer = new SPECTQLTokenizer($querystring, array_keys(self::$symbols));
        $this->parser = new parse_engine(new spectql());

        if (!strlen($querystring)){
            //give an error, but in javascript, redirect to our index.html
            header("HTTP1.1 491 No parse string");
            echo "<script>window.location = \"index.html\";</script>";
            exit(0);
            
        }
        try {
            while($tokenizer->hasNext()){
                $t = $tokenizer->pop();
                if (is_numeric($t)){
                    $this->parser->eat('num', $t);
                }else if($t == "'"){                    
                    $this->parser->eat('string',$tokenizer->pop());
                    $tokenizer->pop();
                    
                }
                else if (preg_match("/[0-9a-zA-Z_\-]+/si",$t)) {
                    $this->parser->eat('name', $t);
                }
                else{
                    $this->parser->eat(self::$symbols[$t], null);
                }
            }
            $result=  $this->parser->eat_eof();
            $o = new StdClass();
            $o->spectql=$result;
            //DBG: var_dump($o);
            return $o;
        } catch (parse_error $e) {
            throw new ParserTDTException($e->getMessage());
        }
    }
}


?>