<?php
namespace NexaMerchant\OdooApi\Helper;
/**
 * Ripcord is an easy to use XML-RPC library for PHP. 
 * @package Ripcord
 * @author Auke van Slooten <auke@muze.nl>
 * @copyright Copyright (C) 2010, Muze <www.muze.nl>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version Ripcord 0.9 - PHP 5
 */

/**
 * The ripcord class contains a number of useful static methods. This makes it a bit easier to create a server or client, convert types 
 * and check for errors.
 * @package Ripcord
 */
class Ripcord
{
	/**
	 *  This method checks whether the given argument is an XML-RPC fault.
	 *  @param mixed $fault
	 *  @return bool
	 */
	public static function isFault($fault) 
	{
		if ( isset($fault) && is_array($fault) ) {
			return xmlrpc_is_fault($fault);
		} else {
			return false;
		}
	}

	/**
	 *  This method generates an XML-RPC fault with the given code and message.
	 *  @param int $code
	 *  @param string $message
	 *  @return array
	 */
	public static function fault($code, $message) 
	{
		return array('faultCode' => $code, 'faultString' => $message);
	}
	
	/**
	 * This method returns a new Ripcord server, which by default implements XML-RPC, Simple RPC and SOAP 1.1.
	 * The server will publish any methods passed through the $services argument. It can be configured through
	 * the $options argument.
	 * @param mixed $services Optional. Either an object or an array of objects. If the array has non-numeric keys, the key will be used as a namespace for the methods in the object.
	 * @param array $options Optional. An array of options to set for the Ripcord server. 
	 * @see Ripcord_Server
	 */
	public static function server($services = null, $options = null, $documentor = null) 
	{
		self::load('Ripcord_Server');
		if ( !isset($documentor) )
		{
			$doc = array('name', 'css', 'wsdl', 'wsdl2');
			$docOptions = array();
			foreach ( $doc as $key ) 
			{
				if ( isset($options[$key]) ) 
				{
					$docOptions[$key] = $options[$key];
					unset( $options[$key] );
				}
			}
			$docOptions['version'] = $options['version'];
			$documentor = self::documentor( $docOptions );
		}
		return new Ripcord_Server($services, $options, $documentor);
	}
	
	/**
	 * This method returns a new Ripcord client. By default this will be an XML-RPC client, but you can change this
	 * through the $options argument. 
	 * @param string $url The url of the RPC server to connect with
	 * @param array $options Optional. An array of options to set for the Ripcord client.
	 * @see Ripcord_Client
	 */
	public static function client($url, $options = null, $transport = null ) 
	{
		self::load('Ripcord_Client');
		if ( !isset($transport) ) 
		{
			$transport = new Ripcord_Transport_Stream();
		}
		return new Ripcord_Client($url, $options, $transport);
	}
	
	/**
	 * This method returns a new Ripcord documentor object.
	 * @param array $options Optional. An array of options to set for the Ripcord documentor.
	 * @param object docCommentParser Optional. An object that parses a docComment block. Must
	 * implement the Ripcord_Documentor_CommentParser interface.
	 * @see Ripcord_Client
	 */
	public static function documentor( $options = null, $docCommentParser = null ) 
	{
		self::load('Ripcord_Documentor');
		if (!$docCommentParser) {
			$docCommentParser = new Ripcord_Documentor_Parser_phpdoc();
		}
		return new Ripcord_Documentor( $options, $docCommentParser );
	}
	
	/**
	 * This method returns an XML-RPC datetime object from a given unix timestamp.
	 * @param int $timestamp
	 * @return object
	 */
	public static function datetime($timestamp) 
	{
		$datetime = date("Ymd\TH:i:s", $timestamp);
		xmlrpc_set_type($datetime, 'datetime');
		return $datetime;
	}

	/**
	 * This method returns a unix timestamp from a given XML-RPC datetime object.
	 * It will throw a 'Variable is not of type datetime' Ripcord_Exception (code -6)
	 * if the given argument is not of the correct type.
	 * @param object $datetime
	 * @return int
	 */
	public static function timestamp($datetime) 
	{
		if (xmlrpc_get_type($datetime)=='datetime') 
		{
			return $datetime->timestamp;
		} else {
			throw Ripcord_Exception('Variable is not of type datetime', -6);
		}
	}
	
	/**
	 * This method returns an XML-RPC base64 object from a given binary string.
	 * @param string $binary
	 * @return object
	 */
	public static function base64($binary) 
	{
		xmlrpc_set_type($binary, 'base64');
		return $binary;
	}
	
	/**
	 * This method returns a (binary) string from a given XML-RPC base64 object.
	 * It will throw a 'Variable is not of type base64' Ripcord_Exception (code -7)
	 * if the given argument is not of the correct type.
	 * @param object $base64
	 * @return string
	 */
	public static function binary($base64) 
	{
		if (xmlrpc_get_type($base64)=='base64')
		{
			return $base64->scalar;
		} else {
			throw Ripcord_Exception('Variable is not of type base64', -7);
		}
	}
	
	/**
	 * This method returns the type of the given parameter. This can be any of the XML-RPC data types, e.g.
	 * 'struct', 'int', 'string', 'base64', 'boolean', 'double', 'array' or 'datetime'. 
	 * @param mixed $param
	 * @return string
	 */
	public static function getType($param) 
	{
		return xmlrpc_get_type($param);
	}

	/**
	 * This method returns a new Ripcord client, configured to access a SOAP 1.1 server.
	 * @param string $url 
	 * @param array $options Optional.
	 * @see Ripcord_Client
	 */
	public static function soapClient($url, $options = null, $transport = null) 
	{
		$options['version'] = 'soap 1.1';
		return self::client($url, $options, $transport);
	}
	
	/**
	 * This method returns a new Ripcord client, configured to access an XML-RPC server.
	 * @param string $url 
	 * @param array $options Optional.
	 * @return object
	 * @see Ripcord_Client
	 */
	public static function xmlrpcClient($url, $options = null, $transport = null) 
	{
		$options['version'] = 'xmlrpc';
		return self::client($url, $options, $transport);
	}
	
	/**
	 * This method returns a new Ripcord client, configured to access a Simple RPC server.
	 * @param string $url 
	 * @param array $options Optional.
	 * @return object
	 * @see Ripcord_Client
	 */
	public static function simpleClient($url, $options = null, $transport = null) 
	{
		$options['version'] = 'simple';
		return self::client($url, $options, $transport);
	}

	/**
	 * This method includes a ripcord class, using require_once. Used for autoloading ripcord classes.
	 * @param string $class The name of the class to load.
	 * @return boolean
	 */
	public static function load($class) 
	{
		if (substr($class, 0, 8)=='Ripcord_') 
		{
			$root = dirname(__FILE__).'/ripcord_';
			$class = substr($class, 8);
			$file = str_replace('.', '', $class);
			$file = str_replace('_', '/', $file);
			$file = strtolower($file);
			while ($file && $file!='.') 
			{
				if ( file_exists($root.$file.'.php') ) 
				{
					require_once($root.$file.'.php');
					return true;
				} else {
					$file = dirname($file);
				}
			}
		}
		return false;
	}
	
	/**
	 * This method creates a new Ripcord_Client_Call object, which encodes the information needed for
	 * a method call to an rpc server. This is mostly used for the system.multiCall method.
	 * @param string $method The name of the method call to encode
	 * @param mixed $args,... The remainder of the arguments are encoded as parameters to the call
	 * @return object
	 */
	public static function encodeCall() 
	{
		self::load('Ripcord_Client');
		$params = func_get_args();
		$method = array_shift($params);
		return new Ripcord_Client_Call( $method, $params );
	}
	
	/* 
	 * This method binds the first parameter to the output of a Ripcord client call. If
	 * the second argument is a Ripcord_Client_Call object, it binds the parameter to it,
	 * if not it simply assigns the second parameter to the first parameter.
	 * This means that doing: 
	 * > ripcord::bind( $result, $client->someMethod() )
	 * will always result in $result eventually containing the return value of $client->someMethod().
	 * Whether multiCall mode has been enabled or not.
	 */
	public function bind(&$bound, $call) 
	{
		if ( is_a( $call, 'Ripcord_Client_Call' ) ) 
		{
			$call->bound =& $bound;
		} else {
			$bound = $call;
		}
		return null;
	}
	
	/**
	 * Method {method} not found. - Thrown by the ripcord server when a requested method isn't found.
	 */
	const methodNotFound     = -1;
	/**
	 * Argument {index} is not a valid Ripcord call - Thrown by the client when passing incorrect arguments to system.multiCall.
	 */
	const notRipcordCall     = -2;
	/**
	 * Cannot recurse system.multiCall  - Thrown by the ripcord server when system.multicall is called within itself.
	 */
	const cannotRecurse      = -3;
	/**
	 * Could not access {url} - Thrown by the transport object when unable to access the given url.
	 */
	const cannotAccessURL    = -4;
	/**
	 * PHP XMLRPC library is not installed - Thrown by the ripcord server and client when the xmlrpc library is not installed.
	 */
	const xmlrpcNotInstalled = -5;
	/**
	 * Variable is not of type datetime - Thrown by the ripcord timestamp method.
 	 */
	const notDatetime        = -6;
	/**
	 * Variable is not of type base64 - Thrown by the ripcord binary method.
 	 */
	const notBase64          = -7;
	/**
	 * Variable is not a classname or an object - Thrown by the ripcord server.
	 */
	const unknownServiceType = -8;
}

/**
 * This interface is implemented by all exceptions thrown by Ripcord.
 * @package Ripcord
 */
interface Ripcord_Exception {}

/**
 * This class is used whenever an when a method passed to the server is invalid.
 * - ripcord::methodNotFound (-1) Method {method} not found. - Thrown by the ripcord server when a requested method isn't found.
 * @package Ripcord
 */
class Ripcord_BadMethodCallException extends \BadMethodCallException implements Ripcord_Exception { }
 
/**
 * This class is used whenever prerequisite requirements are not met.
 * - ripcord::xmlrpcNotInstalled (-5) PHP XMLRPC library is not installed - Thrown by the ripcord server and client when the xmlrpc library is not installed.
 * @package Ripcord
 */
class Ripcord_ConfigurationException extends \Exception implements Ripcord_Exception { }

/**
 * This class is used whenever an argument passed to a Ripcord method is invalid for any reason. Possible exceptions thrown are:
 * - ripcord::notRipcordCall (-2) Argument {index} is not a valid Ripcord call - Thrown by the client when passing incorrect arguments to system.multiCall.
 * - ripcord::cannotRecurse (-3) Cannot recurse system.multiCall  - Thrown by the ripcord server when system.multicall is called within itself.
 * - ripcord::notDateTime (-6) Variable is not of type datetime - Thrown by the ripcord timestamp method.
 * - ripcord::notBase64 (-7) Variable is not of type base64 - Thrown by the ripcord binary method.
 * - ripcord::unknownServiceType (-8) Variable is not a classname or an object - Thrown by the ripcord server.
 * @package Ripcord
 */
class Ripcord_InvalidArgumentException extends \InvalidArgumentException implements Ripcord_Exception { }

/**
 * This class is used whenever something goes wrong in sending / receiving data. Possible exceptions thrown are:
 * - ripcord::cannotAccessURL (-4) Could not access {url} - Thrown by the transport object when unable to access the given url.
 * @package Ripcord
 */
class Ripcord_TransportException extends \RuntimeException implements Ripcord_Exception { }

/**
 * This class is used for exceptions generated from xmlrpc faults returned by the server. The code and message correspond
 * to the code and message from the xmlrpc fault.
 * @package Ripcord
 */
class Ripcord_RemoteException extends \Exception implements Ripcord_Exception { }

class Ripcord_Client 
{
	/**
	 * The url of the rpc server
	 */
	private $_url = '';

	/**
	 * The transport object, used to post requests.
	 */
	private $_transport = null;

	/**
	 * A list of output options, used with the xmlrpc_encode_request method.
	 * @see Ripcord_Server::setOutputOption()
	 */
	private $_outputOptions = array(
		"output_type" => "xml",
		"verbosity" => "pretty",
		"escaping" => array("markup"),
		"version" => "xmlrpc",
		"encoding" => "utf-8"
	);

	/**
	 * The namespace to use when calling a method.
	 */
	private $_namespace = null;

	/**
	 * A reference to the root client object. This is so when you use namespaced sub clients, you can always
	 * find the _response and _request data in the root client.
	 */
	private $_rootClient = null;
	
	/**
	 * A flag to indicate whether or not to preemptively clone objects passed as arguments to methods, see
	 * php bug #50282. Only correctly set in the rootClient.
	 */
	private $_cloneObjects = false;
	
	/**
	 * A flag to indicate if we are in a multiCall block. Start this with $client->system->multiCall()->start()
	 */
	protected $_multiCall = false;

	/**
	 * A list of deferred encoded calls.
	 */
	protected $_multiCallArgs = array();
	
	/**
	  * The exact response from the rpc server. For debugging purposes.
	 */
	public $_response = '';

	/**
	 * The exact request from the client. For debugging purposes.
	 */
	public $_request = '';

	/**
	 * Whether or not to throw exceptions when an xml-rpc fault is returned by the server. Default is false.
	 */
	public $_throwExceptions = false;
	
	/**
	 * Whether or not to decode the XML-RPC datetime and base64 types to unix timestamp and binary string
	 * respectively.
	 */
	public $_autoDecode = true;
	
	/**
	 * The constructor for the RPC client.
	 * @param string $url The url of the rpc server
	 * @param array $options Optional. A list of outputOptions. See {@link Ripcord_Server::setOutputOption()}
	 * @param object $rootClient Optional. Used internally when using namespaces.
	 * @throws Ripcord_ConfigurationException (ripcord::xmlrpcNotInstalled) when the xmlrpc extension is not available.
	 */
	public function __construct( $url, array $options = null, $transport = null, $rootClient = null ) 
	{
		if ( !isset($rootClient) ) {
			$rootClient = $this;
			if ( !function_exists( 'xmlrpc_encode_request' ) )
			{
				throw new Ripcord_ConfigurationException('PHP XMLRPC library is not installed', 
					ripcord::xmlrpcNotInstalled);
			}
			$version = explode('.', phpversion() );
			if ( (0 + $version[0]) == 5) {
				if ( ( 0 + $version[1]) < 2 ) { 
					$this->_cloneObjects = true; // workaround for bug #50282
				}
			}
		}
		$this->_rootClient = $rootClient;
		$this->_url = $url;
		if ( isset($options) ) 
		{
			if ( isset($options['namespace']) ) 
			{
				$this->_namespace = $options['namespace'];
				unset( $options['namespace'] );
			}
			$this->_outputOptions = $options;
		}
		if ( isset($transport) ) {
			$this->_transport = $transport;
		}
	}

	/**
	 * This method catches any native method called on the client and calls it on the rpc server instead. It automatically
	 * parses the resulting xml and returns native php type results.
	 * @throws Ripcord_InvalidArgumentException (ripcord::notRipcordCall) when handling a multiCall and the 
	 * arguments passed do not have the correct method call information
	 * @throws Ripcord_RemoteException when _throwExceptions is true and the server returns an XML-RPC Fault.
	 */
	public function __call($name, $args) 
	{
		if ( isset($this->_namespace) ) 
		{
			$name = $this->_namespace . '.' . $name;
		}

		if ( $name === 'system.multiCall' || $name == 'system.multicall' ) 
		{
			if ( !$args || ( is_array($args) && count($args)==0 ) ) 
			{
				// multiCall is called without arguments, so return the fetch interface object
				return new Ripcord_Client_MultiCall( $this->_rootClient, $name );
			} else if ( is_array( $args ) && (count( $args ) == 1) && 
				is_array( $args[0] )  && !isset( $args[0]['methodName'] ) ) 
			{ 
				// multicall is called with a simple array of calls.
				$args = $args[0];
			}
			$this->_rootClient->_multiCall = false;
			$params = array();
			$bound = array();
			foreach ( $args as $key => $arg ) 
			{
				if ( !is_a( $arg, 'Ripcord_Client_Call' ) && 
					(!is_array($arg) || !isset($arg['methodName']) ) ) 
				{
					throw new Ripcord_InvalidArgumentException(
						'Argument '.$key.' is not a valid Ripcord call', 
							ripcord::notRipcordCall);
				}
				if ( is_a( $arg, 'Ripcord_Client_Call' ) ) 
				{
					$arg->index  = count( $params );
					$params[]    = $arg->encode();
				}
				else
				{
					$arg['index'] = count( $params );
					$params[]    = array(
						'methodName' => $arg['methodName'],
						'params'     => isset($arg['params']) ? 
							(array) $arg['params'] : array()
					);
				}
				$bound[$key] = $arg;
			}
			$args = array( $params );
			$this->_rootClient->_multiCallArgs = array();
		}
		if ( $this->_rootClient->_multiCall ) {
			$call = new Ripcord_Client_Call( $name, $args );
			$this->_rootClient->_multiCallArgs[] = $call;
			return $call;
		}
		if ($this->_rootClient->_cloneObjects) { //workaround for php bug 50282
			foreach( $args as $key => $arg) {
				if (is_object($arg)) {
					$args[$key] = clone $arg;
				}
			}
		}
		$request  = xmlrpc_encode_request( $name, $args, $this->_outputOptions );
		$response = $this->_transport->post( $this->_url, $request );
		$result   = xmlrpc_decode( $response, $this->_outputOptions['encoding'] );
		var_dump($result);
		$this->_rootClient->_request  = $request;
		$this->_rootClient->_response = $response;
		if ( ripcord::isFault( $result ) && $this->_throwExceptions ) 
		{
			throw new Ripcord_RemoteException($result['faultString'], $result['faultCode']);
		}
		if ( isset($bound) && is_array( $bound ) ) 
		{
			foreach ( $bound as $key => $callObject ) 
			{
				if ( is_a( $callObject, 'Ripcord_Client_Call' ) )
				{
					$returnValue = $result[$callObject->index];
				}
				else
				{
					$returnValue = $result[$callObject['index']];
				}
				if ( is_array( $returnValue ) && count( $returnValue ) == 1 ) 
				{
					// XML-RPC specification says that non-fault results must be in a single item array
					$returnValue = current($returnValue);
				}
				if ($this->_autoDecode)
				{
					$type = xmlrpc_get_type($returnValue);
					switch ($type) 
					{
						case 'base64' : 
							$returnValue = ripcord::binary($returnValue);
						break;
						case 'datetime' :
							$returnValue = ripcord::timestamp($returnValue);
						break;
					}
				}
				if ( is_a( $callObject, 'Ripcord_Client_Call' ) ) {
					$callObject->bound = $returnValue;
				} 
				$bound[$key] = $returnValue;
			}
			$result = $bound;
		}		
		return $result;
	}

	/**
	 * This method catches any reference to properties of the client and uses them as a namespace. The
	 * property is automatically created as a new instance of the rpc client, with the name of the property
	 * as a namespace.
	 * @param string $name The name of the namespace
	 * @return object A Ripcord Client with the given namespace set.
	 */
	public function __get($name) 
	{
		$result = null;
		if ( !isset($this->{$name}) ) 
		{
			$result = new Ripcord_Client(
				$this->_url, 
				array_merge($this->_outputOptions, array( 
					'namespace' => $this->_namespace ? 
						$this->_namespace . '.' . $name : $name
				) ),
				$this->_transport,
				$this->_rootClient
			);
			$this->{$name} = $result;
		}
		return $result;
	}
}

/**
 * This class provides the fetch interface for system.multiCall. It is returned
 * when calling $client->system->multiCall() with no arguments. Upon construction
 * it puts the originating client into multiCall deferred mode. The client will
 * gather the requested method calls instead of executing them immediately. It
 * will them execute all of them, in order, when calling
 * $client->system->multiCall()->fetch().
 * This class extends Ripcord_Client only so it has access to its protected _multiCall
 * property.
 */
class Ripcord_Client_MultiCall extends Ripcord_Client 
{
	
	/*
	 * The reference to the originating client to put into multiCall mode.
	 */
	private $client = null;

	private $methodName = null;
	
	/*
	 * This method creates a new multiCall fetch api object.
	 */
	public function __construct( $client, $methodName = 'system.multiCall' ) 
	{
		$this->client = $client;
		$this->methodName = $methodName;
	}

	/*
	 * This method puts the client into multiCall mode. While in this mode all 
	 * method calls are collected as deferred calls (Ripcord_Client_Call).
	 */
	public function start() 
	{	
		$this->client->_multiCall = true;
	}
	
	/*
	 * This method finally calls the clients multiCall method with all deferred
	 * method calls since multiCall mode was enabled.
	 */
	public function execute() 
	{
		if ($this->methodName=='system.multiCall') {
			return $this->client->system->multiCall( $this->client->_multiCallArgs );
		} else { // system.multicall
			return $this->client->system->multicall( $this->client->_multiCallArgs );
		}
	}
	
}

/**
 *  This class is used with the Ripcord_Client when calling system.multiCall. Instead of immediately calling the method on the rpc server,
 *  a Ripcord_Client_Call  object is created with all the information needed to call the method using the multicall parameters. The call object is
 *  returned immediately and is used as input parameter for the multiCall call. The result of the call can be bound to a php variable. This
 *  variable will be filled with the result of the call when it is available.
 * @package Ripcord
 */
class Ripcord_Client_Call 
{
	/**
	 * The method to call on the rpc server
	 */
	public $method = null;
	
	/**
	 * The arguments to pass on to the method.
	 */
	public $params = array();
	
	/**
	 * The index in the multicall request array, if any.
	 */
	public $index  = null;
	
	/**
	 * A reference to the php variable to fill with the result of the call, if any.
	 */
	public $bound  = null;
	
	/**
	 * The constructor for the Ripcord_Client_Call class.
	 * @param string $method The name of the rpc method to call
	 * @param array $params The parameters for the rpc method.
	 */
	public function __construct($method, $params) 
	{
		$this->method = $method;
		$this->params = $params;
	}

	/**
	 * This method allows you to bind a php variable to the result of this method call.
	 * When the method call's result is available, the php variable will be filled with
	 * this result.
	 * @param mixed $bound The variable to bind the result from this call to.
	 * @return object Returns this object for chaining.
	 */
	public function bind(&$bound) 
	{
		$this->bound =& $bound;
		return $this;
	}

	/**
	 * This method returns the correct format for a multiCall argument.
	 * @return array An array with the methodName and params
	 */
	public function encode() {
		return array(
			'methodName' => $this->method,
			'params' => (array) $this->params
		);
	}
	
}

/**
 * This interface describes the minimum interface needed for the transport object used by the
 * Ripcord_Client
 * @package Ripcord
 */
interface Ripcord_Transport 
{
	/**
	 * This method must post the request to the given url and return the results.
	 * @param string $url The url to post to.
	 * @param string $request The request to post.
	 * @return string The server response
	 */
	public function post( $url, $request );
}

/**
 * This class implements the Ripcord_Transport interface using PHP streams.
 * @package Ripcord
 */
class  Ripcord_Transport_Stream implements Ripcord_Transport 
{
	/**
	 * A list of stream context options.
	 */
	private $options = array();
	
	/**
	 * Contains the headers sent by the server.
	 */
	public $responseHeaders = null;
	
	/**
	 * This is the constructor for the Ripcord_Transport_Stream class.
	 * @param array $contextOptions Optional. An array with stream context options.
	 */
	public function __construct( $contextOptions = null ) 
	{
		if ( isset($contextOptions) ) 
		{
			$this->options = $contextOptions;
		}
	}

	/**
	 * This method posts the request to the given url.
	 * @param string $url The url to post to.
	 * @param string $request The request to post.
	 * @return string The server response
	 * @throws Ripcord_TransportException (ripcord::cannotAccessURL) when the given URL cannot be accessed for any reason.
	 */
	public function post( $url, $request ) 
	{
		$options = array_merge( 
			$this->options, 
			array( 
				'http' => array(
					'method' => "POST",
					'header' => "Content-Type: text/xml",
					'content' => $request
				) 
			) 
		);
		$context = stream_context_create( $options );
		$result  = @file_get_contents( $url, false, $context );
		//$this->responseHeaders = $http_response_header;
		if ( !$result )
		{
			throw new Ripcord_TransportException( 'Could not access ' . $url, 
				ripcord::cannotAccessURL );
		}
		return $result;
	}
}

/**
 * This class implements the Ripcord_Transport interface using CURL.
 * @package Ripcord
 */
class Ripcord_Transport_CURL implements Ripcord_Transport 
{
	/**
	 * A list of CURL options.
	 */
	private $options = array();
	
	/**
	 * A flag that indicates whether or not we can safely pass the previous exception to a new exception.
	 */
	private $skipPreviousException = false;
	
	/**
	 * Contains the headers sent by the server.
	 */
	public $responseHeaders = null;

	private $_skipPreviousException = false;

	/**
	 * This is the constructor for the Ripcord_Transport_CURL class.
	 * @param array $curlOptions A list of CURL options.
	 */
	public function __construct( $curlOptions = null ) 
	{
		if ( isset($curlOptions) )
		{
			$this->options = $curlOptions;
		}
		$version = explode('.', phpversion() );
		if ( ( (0 + $version[0]) == 5) && ( 0 + $version[1]) < 3 ) { // previousException supported in php >= 5.3
			$this->_skipPreviousException = true;
		}			
	}

	/**
	 * This method posts the request to the given url
	 * @param string $url The url to post to.
	 * @param string $request The request to post.
	 * @throws Ripcord_TransportException (ripcord::cannotAccessURL) when the given URL cannot be accessed for any reason.
	 * @return string The server response
	 */
	public function post( $url, $request) 
	{
		$curl = curl_init();
		$options = (array) $this->options + array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $url,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $request,
			CURLOPT_HEADER         => true
		);
		curl_setopt_array( $curl, $options );
		$contents = curl_exec( $curl );
		$headerSize = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
		$this->responseHeaders = substr( $contents, 0, $headerSize );
		$contents = substr( $contents, $headerSize );

		if ( curl_errno( $curl ) ) 
		{
			$errorNumber = curl_errno( $curl );
			$errorMessage = curl_error( $curl );
			curl_close( $curl );
			$version = explode('.', phpversion() );
			if (!$this->_skipPreviousException) { // previousException supported in php >= 5.3
				$exception = new Ripcord_TransportException( 'Could not access ' . $url
					, ripcord::cannotAccessURL
					, new \Exception( $errorMessage, $errorNumber ) 
				);
			} else {
				$exception = new Ripcord_TransportException( 'Could not access ' . $url 
					. ' ( original CURL error: ' . $errorMessage . ' ) ',
					ripcord::cannotAccessURL
				);
			}
			throw $exception;
		}
		curl_close($curl);
		return $contents;
	}
}
