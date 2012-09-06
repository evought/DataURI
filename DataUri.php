<?php
/**
 * The DataUri class provides a convenient way to access and construct 
 * data URIs, but should not be relied upon for enforcing RFC 2397 standards.
 * 
 * This class will not:
 *  - Validate the media-type provided/parsed
 *  - Validate the encoded data provided/parsed
 * 
 * @link http://www.flyingtophat.co.uk/blog/27/using-data-uris-with-php Examples
 * @author <a href="http://www.flyingtophat.co.uk/">Lucas</a>
 */
class DataUri {
    
    /** @var Regular expression used for decomposition of data URI scheme */
    private static $REGEX_URI = '/^data:(.+?){0,1}(?:(?:;(base64)\,){1}|\,)(.+){0,1}$/';
    
    const DEFAULT_TYPE = 'text/plain;charset=US-ASCII';
    
    const ENCODING_URL_ENCODED_OCTETS = 0;
    const ENCODING_BASE64 = 1;
    
    /** @var Keyword used in the data URI to signify base64 encoding */
    const BASE64_KEYWORD = 'base64';
    
    private $mediaType;
    private $encoding;
    private $encodedData;
    
    /**
     * Instantiates an instance of the DataURI class, initialised with the 
     * default values defined in RFC 2397. That is the media-type of 
     * text/plain;charset=US-ASCII and encoding type of URL encoded octets.
     * 
     * @param string $mediaType
     * @param string $data  Unencoded data
     * @param integer $encoding Class constant of either
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     * 
     * @throws InvalidArgumentException
     */
    public function __construct($mediaType = DataUri::DEFAULT_TYPE,
            $data = '',
            $encoding = DataUri::ENCODING_URL_ENCODED_OCTETS
            ) {
        try {
            $this->setMediaType($mediaType);
            $this->setData($data, $encoding);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Returns the data URI's media-type. If none was provided then in 
     * accordance to RFC 2397 it will default to text/plain;charset=US-ASCII
     * 
     * @return string Media-type
     */
    public function getMediaType() {
        return empty($this->mediaType) === false
            ? $this->mediaType
            : DataUri::DEFAULT_TYPE;
    }
    
    /**
     * Sets the media-type.
     * 
     * @param string $mediaType Media-type
     */
    public function setMediaType($mediaType) {
        $this->mediaType = $mediaType;
    }
    
    /**
     * Returns the method of encoding used for the data.
     * 
     * @return int Class constant of either 
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     */
    public function getEncoding() {
        return $this->encoding;
    }
    
    /**
     * Returns the data in its encoded form.
     * 
     * @return string Encoded data
     */
    public function getEncodedData() {
        return $this->encodedData;
    }
    
    /**
     * Sets the encoded data and the encoding scheme used to encode/decode it.
     * Be aware that the data is not validated, so ensure that the correct
     * encoding scheme is provided otherwise the method 
     * {@link DataUri::tryDecodeData($decodedData)} will fail.

     * @param int $encoding Class constant of either 
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     * @param string $data Data encoded with the encoding scheme provided
     * @throws InvalidArgumentException
     */
    public function setEncodedData($encoding, $data) {
        if(($encoding !== DataUri::ENCODING_URL_ENCODED_OCTETS) &&
            ($encoding !== DataUri::ENCODING_BASE64)) {
            throw new InvalidArgumentException('Unsupported encoding scheme');
        }
        
        $this->encoding = $encoding;
        $this->encodedData = $data;
    }
    
    
    /**
     * Sets the data for the data URI, which it stores in encoded form using
     * the encoding scheme provided.
     * 
     * @param string $data Data to encode then store
     * @param int $encoding Class constant of either 
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     * @throws InvalidArgumentException
     */
    public function setData($data, $encoding = DataUri::ENCODING_URL_ENCODED_OCTETS) {
        switch($encoding) {
            case DataUri::ENCODING_URL_ENCODED_OCTETS:
		$this->encoding = DataUri::ENCODING_URL_ENCODED_OCTETS;
                $this->encodedData = rawurlencode($data);
                break;
            case DataUri::ENCODING_BASE64:
		$this->encoding = DataUri::ENCODING_BASE64;
                $this->encodedData = base64_encode($data);
                break;
            default:
                throw new InvalidArgumentException('Unsupported encoding scheme');
                break;
        }
    }
    
    /**
     * Tries to decode the URI's data using the encoding scheme set.
     * 
     * @param null $decodedData Stores the decoded data
     * @return boolean <code>true</code> if data was output, 
     * else <code>false</code>
     */
    public function tryDecodeData(&$decodedData) {
        $hasOutput = false;
        
        switch($this->getEncoding()) {
            case DataUri::ENCODING_URL_ENCODED_OCTETS:
                $decodedData = rawurldecode($this->getEncodedData());
                $hasOutput = true;
                break;
            case DataUri::ENCODING_BASE64:
                $b64Decoded = base64_decode($this->getEncodedData(), true);

                if($b64Decoded !== false) {
                    $decodedData = $b64Decoded;
                    $hasOutput = true;
                }
                break;
            default:
                // NOP
                break;
        }

        return $hasOutput;
    }
    
    /**
     * Generates a data URI string representation of the object.
     * 
     * @return string
     */
    public function toString() {
        $output = 'data:';
        
        if(($this->getMediaType() !== DataUri::DEFAULT_TYPE) || 
            ($this->getEncoding() !== DataUri::ENCODING_URL_ENCODED_OCTETS)) {
            $output .= $this->getMediaType();

            if($this->getEncoding() === DataUri::ENCODING_BASE64) {
                $output .= ';'.DataUri::BASE64_KEYWORD;
            }
        }

        $output .= ','.$this->getEncodedData();
        return $output; 
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Determines whether a string is data URI with the components necessary for
     * it to be parsed by the {@link DataUri::tryParse($s, &$out)} method.
     * 
     * @param string $string Data URI
     * @return boolean <code>true</code> if possible to parse,
     * else <code>false</code>
     */
    public static function isParsable ($dataUriString) {
        return (preg_match(DataUri::$REGEX_URI, $dataUriString) === 1);
    }
    
    /**
     * Parses a string data URI into an instance of a DataUri object.
     * 
     * @param string $dataUriString Data URI to be parsed
     * @param DataUri $out Output DataUri of the method
     * @return boolean <code>true</code> if successful, else <code>false</code>
     */
    public static function tryParse($dataUriString, &$out) {
        $hasOutput = false;
        
        if(DataUri::isParsable($dataUriString)) {
            $matches = null;
            if(preg_match_all(DataUri::$REGEX_URI,
                $dataUriString,
                $matches,
                PREG_SET_ORDER) !== false) {

                $mediatype = isset($matches[0][1])
                    ? $matches[0][1]
                    : DataUri::DEFAULT_TYPE;

                $matchedEncoding = isset($matches[0][2]) ? $matches[0][2] : '';
                $encoding = (strtolower($matchedEncoding) === DataUri::BASE64_KEYWORD)
                    ? DataUri::ENCODING_BASE64
                    : DataUri::ENCODING_URL_ENCODED_OCTETS;

                $data = isset($matches[0][3]) 
                    ? $matches[0][3] 
                    : '';

                $dataUri = new DataUri();
                $dataUri->setMediaType($mediatype);
                $dataUri->setEncodedData($encoding, $data);

                $out = $dataUri;
                $hasOutput = true;
            }
        }
        
        return $hasOutput;
    }
}
?>