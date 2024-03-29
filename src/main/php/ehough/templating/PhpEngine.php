<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ENT_SUBSTITUTE')) {
    define('ENT_SUBSTITUTE', 8);
}

if(!function_exists('array_replace')) {
    function array_replace()
    {
        $args     = func_get_args();
        $num_args = func_num_args();
        $res      = array();

        for($i = 0; $i < $num_args; $i++) {

            if(is_array($args[$i])) {
                foreach($args[$i] as $key => $val) {
                    $res[$key] = $val;
                }
            } else {
                trigger_error(__FUNCTION__ .'(): Argument #'.($i+1).' is not an array', E_USER_WARNING);
                return NULL;
            }
        }

        return $res;
    }
}

/**
 * ehough_templating_PhpEngine is an engine able to render PHP templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ehough_templating_PhpEngine implements ehough_templating_EngineInterface, ArrayAccess
{
    protected $loader;
    protected $current;
    /**
     * @var ehough_templating_helper_HelperInterface[]
     */
    protected $helpers = array();
    protected $parents = array();
    protected $stack = array();
    protected $charset = 'UTF-8';
    protected $cache = array();
    protected $escapers = array();
    protected static $escaperCache = array();
    protected $globals = array();
    protected $parser;

    private $evalTemplate;
    private $evalParameters;

    /**
     * Constructor.
     *
     * @param ehough_templating_TemplateNameParserInterface $parser  A ehough_templating_TemplateNameParserInterface instance
     * @param ehough_templating_loader_LoaderInterface      $loader  A loader instance
     * @param ehough_templating_helper_HelperInterface[]    $helpers An array of helper instances
     */
    public function __construct(ehough_templating_TemplateNameParserInterface $parser, ehough_templating_loader_LoaderInterface $loader, array $helpers = array())
    {
        $this->parser  = $parser;
        $this->loader  = $loader;

        $this->addHelpers($helpers);

        $this->initializeEscapers();
        foreach ($this->escapers as $context => $escaper) {
            $this->setEscaper($context, $escaper);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException if the template does not exist
     *
     * @api
     */
    public function render($name, array $parameters = array())
    {
        $storage = $this->load($name);
        $key = hash('sha256', serialize($storage));
        $this->current = $key;
        $this->parents[$key] = null;

        // attach the global variables
        $parameters = array_replace($this->getGlobals(), $parameters);
        // render
        if (false === $content = $this->evaluate($storage, $parameters)) {
            throw new RuntimeException(sprintf('The template "%s" cannot be rendered.', $this->parser->parse($name)));
        }

        // decorator
        if ($this->parents[$key]) {
            $slots = $this->get('slots');
            $this->stack[] = $slots->get('_content');
            $slots->set('_content', $content);

            $content = $this->render($this->parents[$key], $parameters);

            $slots->set('_content', array_pop($this->stack));
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function exists($name)
    {
        try {
            $this->load($name);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function supports($name)
    {
        $template = $this->parser->parse($name);

        return 'php' === $template->get('engine');
    }

    /**
     * Evaluates a template.
     *
     * @param ehough_templating_storage_Storage $template   The template to render
     * @param array                             $parameters An array of parameters to pass to the template
     *
     * @return string|false The evaluated template, or false if the engine is unable to render the template
     *
     * @throws InvalidArgumentException
     */
    protected function evaluate(ehough_templating_storage_Storage $template, array $parameters = array())
    {
        $this->evalTemplate = $template;
        $this->evalParameters = $parameters;
        unset($template, $parameters);

        if (isset($this->evalParameters['this'])) {
            throw new InvalidArgumentException('Invalid parameter (this)');
        }
        if (isset($this->evalParameters['view'])) {
            throw new InvalidArgumentException('Invalid parameter (view)');
        }

        $view = $this;
        if ($this->evalTemplate instanceof ehough_templating_storage_FileStorage) {
            extract($this->evalParameters, EXTR_SKIP);
            $this->evalParameters = null;

            ob_start();
            require $this->evalTemplate;

            $this->evalTemplate = null;

            return ob_get_clean();
        } elseif ($this->evalTemplate instanceof ehough_templating_storage_StringStorage) {
            extract($this->evalParameters, EXTR_SKIP);
            $this->evalParameters = null;

            ob_start();
            eval('; ?>'.$this->evalTemplate.'<?php ;');

            $this->evalTemplate = null;

            return ob_get_clean();
        }

        return false;
    }

    /**
     * Gets a helper value.
     *
     * @param string $name The helper name
     *
     * @return ehough_templating_helper_HelperInterface The helper value
     *
     * @throws InvalidArgumentException if the helper is not defined
     *
     * @api
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Returns true if the helper is defined.
     *
     * @param string $name The helper name
     *
     * @return bool    true if the helper is defined, false otherwise
     *
     * @api
     */
    public function offsetExists($name)
    {
        return isset($this->helpers[$name]);
    }

    /**
     * Sets a helper.
     *
     * @param ehough_templating_helper_HelperInterface $name  The helper instance
     * @param string          $value An alias
     *
     * @api
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Removes a helper.
     *
     * @param string $name The helper name
     *
     * @throws LogicException
     *
     * @api
     */
    public function offsetUnset($name)
    {
        throw new LogicException(sprintf('You can\'t unset a helper (%s).', $name));
    }

    /**
     * Adds some helpers.
     *
     * @param ehough_templating_helper_HelperInterface[] $helpers An array of helper
     *
     * @api
     */
    public function addHelpers(array $helpers)
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, is_int($alias) ? null : $alias);
        }
    }

    /**
     * Sets the helpers.
     *
     * @param ehough_templating_helper_HelperInterface[] $helpers An array of helper
     *
     * @api
     */
    public function setHelpers(array $helpers)
    {
        $this->helpers = array();
        $this->addHelpers($helpers);
    }

    /**
     * Sets a helper.
     *
     * @param ehough_templating_helper_HelperInterface $helper The helper instance
     * @param string          $alias  An alias
     *
     * @api
     */
    public function set(ehough_templating_helper_HelperInterface $helper, $alias = null)
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }

        $helper->setCharset($this->charset);
    }

    /**
     * Returns true if the helper if defined.
     *
     * @param string $name The helper name
     *
     * @return bool    true if the helper is defined, false otherwise
     *
     * @api
     */
    public function has($name)
    {
        return isset($this->helpers[$name]);
    }

    /**
     * Gets a helper value.
     *
     * @param string $name The helper name
     *
     * @return ehough_templating_helper_HelperInterface The helper instance
     *
     * @throws InvalidArgumentException if the helper is not defined
     *
     * @api
     */
    public function get($name)
    {
        if (!isset($this->helpers[$name])) {
            throw new InvalidArgumentException(sprintf('The helper "%s" is not defined.', $name));
        }

        return $this->helpers[$name];
    }

    /**
     * Decorates the current template with another one.
     *
     * @param string $template The decorator logical name
     *
     * @api
     */
    public function extend($template)
    {
        $this->parents[$this->current] = $template;
    }

    /**
     * Escapes a string by using the current charset.
     *
     * @param mixed  $value   A variable to escape
     * @param string $context The context name
     *
     * @return string The escaped value
     *
     * @api
     */
    public function escape($value, $context = 'html')
    {
        if (is_numeric($value)) {
            return $value;
        }

        // If we deal with a scalar value, we can cache the result to increase
        // the performance when the same value is escaped multiple times (e.g. loops)
        if (is_scalar($value)) {
            if (!isset(self::$escaperCache[$context][$value])) {
                self::$escaperCache[$context][$value] = call_user_func($this->getEscaper($context), $value);
            }

            return self::$escaperCache[$context][$value];
        }

        return call_user_func($this->getEscaper($context), $value);
    }

    /**
     * Sets the charset to use.
     *
     * @param string $charset The charset
     *
     * @api
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        foreach ($this->helpers as $helper) {
            $helper->setCharset($this->charset);
        }
    }

    /**
     * Gets the current charset.
     *
     * @return string The current charset
     *
     * @api
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Adds an escaper for the given context.
     *
     * @param string $context The escaper context (html, js, ...)
     * @param mixed  $escaper A PHP callable
     *
     * @api
     */
    public function setEscaper($context, $escaper)
    {
        $this->escapers[$context] = $escaper;
        self::$escaperCache[$context] = array();
    }

    /**
     * Gets an escaper for a given context.
     *
     * @param string $context The context name
     *
     * @return mixed  $escaper A PHP callable
     *
     * @throws InvalidArgumentException
     *
     * @api
     */
    public function getEscaper($context)
    {
        if (!isset($this->escapers[$context])) {
            throw new InvalidArgumentException(sprintf('No registered escaper for context "%s".', $context));
        }

        return $this->escapers[$context];
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @api
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * Returns the assigned globals.
     *
     * @return array
     *
     * @api
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * Initializes the built-in escapers.
     *
     * Each function specifies a way for applying a transformation to a string
     * passed to it. The purpose is for the string to be "escaped" so it is
     * suitable for the format it is being displayed in.
     *
     * For example, the string: "It's required that you enter a username & password.\n"
     * If this were to be displayed as HTML it would be sensible to turn the
     * ampersand into '&amp;' and the apostrophe into '&aps;'. However if it were
     * going to be used as a string in JavaScript to be displayed in an alert box
     * it would be right to leave the string as-is, but c-escape the apostrophe and
     * the new line.
     *
     * For each function there is a define to avoid problems with strings being
     * incorrectly specified.
     */
    protected function initializeEscapers()
    {
        $this->escapers = array(
            'html' => array($this, '__callbackInitializeEscapersHtml'),
            'js' => array($this, '__callbackInitializeEscapersJs1')
        );

        self::$escaperCache = array();
    }

    /**
     * Runs the PHP function htmlspecialchars on the value passed.
     *
     * @param string $value the value to escape
     *
     * @return string the escaped value
     */
    public function __callbackInitializeEscapersHtml($value)
    {
        // Numbers and Boolean values get turned into strings which can cause problems
        // with type comparisons (e.g. === or is_int() etc).
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, $this->getCharset(), false) : $value;
    }

    /**
     * A function that escape all non-alphanumeric characters
     * into their \xHH or \uHHHH representations
     *
     * @param string $value the value to escape
     * @return string the escaped value
     */
    public function __callbackInitializeEscapersJs1($value)
    {
        if ('UTF-8' != $this->getCharset()) {
            $value = $this->convertEncoding($value, 'UTF-8', $this->getCharset());
        }

        $callback = array($this, '__callbackInitializeEscapersJs2');

        if (null === $value = preg_replace_callback('#[^\p{L}\p{N} ]#u', $callback, $value)) {
            throw new InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
        }

        if ('UTF-8' != $this->getCharset()) {
            $value = $this->convertEncoding($value, $this->getCharset(), 'UTF-8');
        }

        return $value;
    }

    public function __callbackInitializeEscapersJs2($matches)
    {
        $char = $matches[0];

        // \xHH
        if (!isset($char[1])) {
            return '\\x'.substr('00'.bin2hex($char), -2);
        }

        // \uHHHH
        $char = $this->convertEncoding($char, 'UTF-16BE', 'UTF-8');

        return '\\u'.substr('0000'.bin2hex($char), -4);
    }

    /**
     * Convert a string from one encoding to another.
     *
     * @param string $string The string to convert
     * @param string $to     The input encoding
     * @param string $from   The output encoding
     *
     * @return string The string with the new encoding
     *
     * @throws RuntimeException if no suitable encoding function is found (iconv or mbstring)
     */
    public function convertEncoding($string, $to, $from)
    {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $string);
        }

        throw new RuntimeException('No suitable convert encoding function (use UTF-8 as your encoding or install the iconv or mbstring extension).');
    }

    /**
     * Gets the loader associated with this engine.
     *
     * @return ehough_templating_loader_LoaderInterface A ehough_templating_loader_LoaderInterface instance
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Loads the given template.
     *
     * @param string|ehough_templating_TemplateReferenceInterface $name A template name or a ehough_templating_TemplateReferenceInterface instance
     *
     * @return ehough_templating_storage_Storage A Storage instance
     *
     * @throws InvalidArgumentException if the template cannot be found
     */
    protected function load($name)
    {
        $template = $this->parser->parse($name);

        $key = $template->getLogicalName();
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $storage = $this->loader->load($template);

        if (false === $storage) {
            throw new InvalidArgumentException(sprintf('The template "%s" does not exist.', $template));
        }

        return $this->cache[$key] = $storage;
    }
}
