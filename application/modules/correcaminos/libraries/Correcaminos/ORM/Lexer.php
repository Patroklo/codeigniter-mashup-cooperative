<?php
class Lexer {
    /**
     * @var array of scanned data
     */
    public $data = array();
    public $pointer;
    /**
     * Sets the input data to be tokenized.
     *
     * The Lexer is immediately reset and the new input tokenized.
     * Any unprocessed tokens from any previous input are lost.
     *
     * @param string $input The input to be tokenized.
     */
    public function __construct($input = '') {
        $this -> pointer = 0;
        $this -> data = array();
        if ($input != '') {
            $this -> addComment($input);
        }
    }

    public function addComment($input) {
        static $regex;

        if (!isset($regex)) {
            $regex = '/(' . implode(')|(', $this -> getCatchablePatterns()) . ')|' . implode('|', $this -> getNonCatchablePatterns()) . '/i';
        }

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $input, -1, $flags);
        foreach ($matches as $match) {
            // Must remain before 'value' assignment since it can change content
            $type = $this -> getType($match[0]);
            $this -> data[] = array('value' => $match[0], 'type' => $type, 'position' => $match[1], );
        }
    }
   
   
    /*function getNode()
    {
        $nodo
        $max = count($this -> data);
        for($i = $this -> pointer; $i < $max; $i++)
        {
            if($this -> cata[$i] !== '@')
            {
               
            }
        }
    }*/

    const T_NONE = 1;
    const T_INTEGER = 2;
    const T_STRING = 3;
    const T_FLOAT = 4;

    // All tokens that are also identifiers should be >= 100
    const T_IDENTIFIER = 100;
    const T_AT = 101;
    const T_CLOSE_CURLY_BRACES = 102;
    const T_CLOSE_PARENTHESIS = 103;
    const T_COMMA = 104;
    const T_EQUALS = 105;
    const T_FALSE = 106;
    const T_NAMESPACE_SEPARATOR = 107;
    const T_OPEN_CURLY_BRACES = 108;
    const T_OPEN_PARENTHESIS = 109;
    const T_TRUE = 110;
    const T_NULL = 111;
    const T_COLON = 112;

    protected $noCase = array('@' => self::T_AT, ',' => self::T_COMMA, '(' => self::T_OPEN_PARENTHESIS, ')' => self::T_CLOSE_PARENTHESIS, '{' => self::T_OPEN_CURLY_BRACES, '}' => self::T_CLOSE_CURLY_BRACES, '=' => self::T_EQUALS, ':' => self::T_COLON, '\\' => self::T_NAMESPACE_SEPARATOR);

    protected $withCase = array('true' => self::T_TRUE, 'false' => self::T_FALSE, 'null' => self::T_NULL);

    /**
     * {@inheritdoc}
     */
    protected function getCatchablePatterns() {
        return array('[a-z_\\\][a-z0-9_\:\\\]*[a-z]{1}', '(?:[+-]?[0-9]+(?:[\.][0-9]+)*)(?:[eE][+-]?[0-9]+)?', '"(?:[^"]|"")*"', );
    }

    /**
     * {@inheritdoc}
     */
    protected function getNonCatchablePatterns() {
        return array('\s+', '\*+', '(.)');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return int
     */
    protected function getType(&$value) {
        $type = self::T_NONE;

        if ($value[0] === '"') {
            $value = str_replace('""', '"', substr($value, 1, strlen($value) - 2));

            return self::T_STRING;
        }

        if (isset($this -> noCase[$value])) {
            return $this -> noCase[$value];
        }

        if ($value[0] === '_' || $value[0] === '\\' || ctype_alpha($value[0])) {
            return self::T_IDENTIFIER;
        }

        $lowerValue = strtolower($value);

        if (isset($this -> withCase[$lowerValue])) {
            return $this -> withCase[$lowerValue];
        }

        // Checking numeric value
        if (is_numeric($value)) {
            return (strpos($value, '.') !== false || stripos($value, 'e') !== false) ? self::T_FLOAT : self::T_INTEGER;
        }

        return $type;
    }

}

/**
 * A test class
 *
 * @column(type = "string", length = 12)
 * @param  foo bar
 * @return baz
 */

class TestClass {

    /** derp */
    public $uno;

    /** dorp */
    private $dos;

}

$prueba = new ReflectionClass('TestClass');

$this -> lexer = new Lexer();

if (false === $pos = strpos($prueba -> getDocComment(), '@')) {
    return array();
}

// also parse whatever character is before the @
if ($pos > 0) {
    $pos -= 1;
}

$this -> context = $context;
$this -> lexer -> addComment(trim(substr($prueba -> getDocComment(), $pos), '* /'));

foreach ($prueba->getProperties() as $prop) {
    $prop = $prueba -> getProperty($prop->name);
    $this -> lexer -> addComment($prop -> getDocComment());
}

echo '<pre>';
echo var_dump($this -> lexer -> data);
echo '</pre>';