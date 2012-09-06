<?php
require('DataUri.php');

class DataUriTest extends PHPUnit_Framework_TestCase {

    /**
     * @var DataUri
     */
    protected $dataUri;

    protected function setUp() {
        $this->dataUri = new DataUri();
    }

    /**
     * @covers DataUri::getMediaType
     */
    public function testGetMediaType() {
        $this->assertEquals(DataUri::DEFAULT_TYPE, $this->dataUri->getMediaType());
        
        $this->dataUri->setMediaType('image/gif');
        $this->assertEquals('image/gif', $this->dataUri->getMediaType());
    }

    /**
     * @covers DataUri::setMediaType
     */
    public function testSetMediaType() {
        $this->dataUri->setMediaType('image/png');
        $this->assertEquals('image/png', $this->dataUri->getMediaType());
        
        $this->dataUri->setMediaType('');
        $this->assertEquals(DataUri::DEFAULT_TYPE, $this->dataUri->getMediaType());
    }

    /**
     * @covers DataUri::getEncoding
     */
    public function testGetEncoding() {
        $this->assertEquals(DataUri::ENCODING_URL_ENCODED_OCTETS, $this->dataUri->getEncoding());
        
        $this->dataUri->setEncodedData(DataUri::ENCODING_BASE64, '');
        $this->assertEquals(DataUri::ENCODING_BASE64, $this->dataUri->getEncoding());
    }

    /**
     * @covers DataUri::getEncodedData
     */
    public function testGetEncodedData() {
        $this->dataUri->setEncodedData(DataUri::ENCODING_BASE64, 'Example');
        $this->assertEquals('Example', $this->dataUri->getEncodedData());
    }

    /**
     * @covers DataUri::setEncodedData
     */
    public function testSetEncodedData() {
        $this->dataUri->setEncodedData(DataUri::ENCODING_BASE64, 'Example');
        $this->assertEquals('Example', $this->dataUri->getEncodedData());
    }

    /**
     * @covers DataUri::setEncodedData
     * @expectedException InvalidArgumentException
     */
    public function testSetEncodedDataException()
    {
        $this->dataUri->setEncodedData(null, 'Example');
    }
    
    /**
     * @covers DataUri::setData
     */
    public function testSetData() {
        $this->dataUri->setData('', DataUri::ENCODING_BASE64);
        $this->assertEquals('', $this->dataUri->getEncodedData());
        
        $this->dataUri->setData('ABC<>\/.?^%£');
        $this->assertEquals(rawurlencode('ABC<>\/.?^%£'), $this->dataUri->getEncodedData());
        
        $this->dataUri->setData('KFJ%&£"%*||`', DataUri::ENCODING_URL_ENCODED_OCTETS);
        $this->assertEquals(rawurlencode('KFJ%&£"%*||`'), $this->dataUri->getEncodedData());
        
        $this->dataUri->setData('~:{}[123S', DataUri::ENCODING_BASE64);
        $this->assertEquals(base64_encode('~:{}[123S'), $this->dataUri->getEncodedData());
        
        $this->dataUri->setData('', DataUri::ENCODING_URL_ENCODED_OCTETS);
        $this->assertEquals('', $this->dataUri->getEncodedData());
    }
    
    /**
     * @covers DataUri::setData
     * @expectedException InvalidArgumentException
     */
    public function testSetDataException()
    {
        $this->dataUri->setEncodedData('', null);
    }
    
    /**
     * @covers DataUri::tryDecodeData
     * @todo   Write a test where the data causes tryDecodeData() to return false
     */
    public function testTryDecodeData() {
        // Base64 with emtpy value
        $this->dataUri->setData('', DataUri::ENCODING_BASE64);
        $decodedData = null;
        $this->assertTrue($this->dataUri->tryDecodeData($decodedData));
        $this->assertEquals('', $decodedData);
        
        // Default encoding type
        $this->dataUri->setData('ABC<>\/.?^%£');
        $decodedData = null;
        $this->assertTrue($this->dataUri->tryDecodeData($decodedData));
        $this->assertEquals('ABC<>\/.?^%£', $decodedData);
        
        // URL encoded octet encoding with value
        $this->dataUri->setData('KFJ%&£"%*||`', DataUri::ENCODING_URL_ENCODED_OCTETS);
        $decodedData = null;
        $this->assertTrue($this->dataUri->tryDecodeData($decodedData));
        $this->assertEquals('KFJ%&£"%*||`', $decodedData);
        
        // Base64 with value
        $this->dataUri->setData('~:{}[123S', DataUri::ENCODING_BASE64);
        $decodedData = null;
        $this->assertTrue($this->dataUri->tryDecodeData($decodedData));
        $this->assertEquals('~:{}[123S', $decodedData);
        
        // URL encoded octet with emtpy value
        $this->dataUri->setData('', DataUri::ENCODING_URL_ENCODED_OCTETS);
        $decodedData = null;
        $this->assertTrue($this->dataUri->tryDecodeData($decodedData));
        $this->assertEquals('', $decodedData);
        
        // Encoded data set through DataUri::setEncodedData()
        $this->dataUri->setEncodedData(DataUri::ENCODING_BASE64, base64_encode('MGH4%"£4;FF'));
        $decodedData = null;
        $this->assertTrue($this->dataUri->tryDecodeData($decodedData));
        $this->assertEquals('MGH4%"£4;FF', $decodedData);
        
        //$this->dataUri->setEncodedData(DataUri::ENCODING_BASE64, null);
        //$decodedData = null;
        //$this->assertFalse($this->dataUri->tryDecodeData($decodedData));
    }

    /**
     * @covers DataUri::toString
     */
    public function testToString() {
        $this->assertEquals('data:,', $this->dataUri->toString());
        
        $this->dataUri->setMediaType('image/png');
        $this->dataUri->setData('HG2/$%&£"34A', DataUri::ENCODING_BASE64);
        
        $encoded = base64_encode('HG2/$%&£"34A');
        $this->assertEquals("data:image/png;base64,{$encoded}",
            $this->dataUri->toString());
    }
    
    /**
     * @covers DataUri::isParsable
     */
    public function testIsParsable() {
        $this->assertFalse(DataUri::isParsable(''));
        $this->assertTrue(DataUri::isParsable('data:,'));
        $this->assertTrue(DataUri::isParsable('data:text/plain;charset=US-ASCII;base64,ABC'));
    }

    /**
     * @covers DataUri::tryParse
     */
    public function testTryParse() {
        $dataUri = null;
        $this->assertFalse(DataUri::tryParse('', $dataUri));
        
        $dataUri = null;
        $this->assertTrue(DataUri::tryParse('data:,', $dataUri));
        $this->assertEquals($dataUri, new DataUri());
        
        $dataUri = null;
        $this->assertTrue(DataUri::tryParse('data:image/png;base64,', $dataUri));
        $this->assertEquals($dataUri, new DataUri('image/png', '', DataUri::ENCODING_BASE64));
    }
}
?>