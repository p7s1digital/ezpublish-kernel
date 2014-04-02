<?php
/**
 * File containing the eZ\Publish\Core\FieldType\Tests\RichText\Converter\BaseTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Xslt;

use eZ\Publish\Core\FieldType\RichText\Converter\Xslt;
use eZ\Publish\Core\FieldType\RichText\Validator;
use PHPUnit_Framework_TestCase;
use DOMDocument;
use DOMXpath;

/**
 * Base class for XSLT converter tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\RichText\Converter
     */
    protected $converter;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\Validator
     */
    protected $validator;

    /**
     * Provider for conversion test.
     *
     * @return array
     */
    public function providerForTestConvert()
    {
        $fixtureSubdirectories = $this->getFixtureSubdirectories();

        $map = array();

        foreach ( glob( __DIR__ . "/_fixtures/{$fixtureSubdirectories["input"]}/*.xml" ) as $inputFile )
        {
            $basename = basename( $inputFile, ".xml" );
            $outputFile = __DIR__ . "/_fixtures/{$fixtureSubdirectories["output"]}/{$basename}.xml";
            $outputFileLossy = __DIR__ . "/_fixtures/{$fixtureSubdirectories["output"]}/{$basename}.lossy.xml";

            if ( !file_exists( $outputFile ) && file_exists( $outputFileLossy ) )
            {
                $outputFile = $outputFileLossy;;
            }

            $map[] = array( $inputFile, $outputFile );
        }

        return $map;
    }

    protected function removeComments( DOMDocument $document )
    {
        $xpath = new DOMXpath( $document );
        $nodes = $xpath->query( "//comment()" );

        for ( $i = 0; $i < $nodes->length; $i++ )
        {
            $nodes->item( $i )->parentNode->removeChild( $nodes->item( $i ) );
        }
    }

    /**
     * @param string $inputFile
     * @param string $outputFile
     *
     * @dataProvider providerForTestConvert
     */
    public function testConvert( $inputFile, $outputFile )
    {
        $endsWith = ".lossy.xml";
        if ( substr_compare( $inputFile, $endsWith, -strlen( $endsWith ), strlen( $endsWith ) ) === 0 )
        {
            $this->markTestSkipped( "Skipped lossy conversion." );
        }

        $inputDocument = $this->createDocument( $inputFile );
        $outputDocument = $this->createDocument( $outputFile );

        $this->removeComments( $inputDocument );
        $this->removeComments( $outputDocument );

        $converter = $this->getConverter();
        $convertedDocument = $converter->convert( $inputDocument );

        $this->assertEquals(
            $outputDocument->saveXML(),
            $convertedDocument->saveXML()
        );

        $validator = $this->getConversionValidator();
        if ( isset( $validator ) )
        {
            $errors = $validator->validate( $convertedDocument );
            $this->assertTrue(
                empty( $errors ),
                "Conversion result did not validate against the configured schema:" .
                $this->formatValidationErrors( $errors )
            );
        }
    }

    /**
     * @param string $xmlFile
     *
     * @return \DOMDocument
     */
    protected function createDocument( $xmlFile )
    {
        $document = new DOMDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;

        $document->loadXml( file_get_contents( $xmlFile ) );

        return $document;
    }

    protected function formatValidationErrors( array $errors )
    {
        $output = "\n";
        foreach ( $errors as $error )
        {
            $output .= " - " . $error . "\n";
        }
        return $output;
    }

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\Converter\Xslt
     */
    protected function getConverter()
    {
        if ( $this->converter === null )
        {
            $this->converter = new Xslt(
                $this->getConversionTransformationStylesheet(),
                $this->getCustomConversionTransformationStylesheets()
            );
        }

        return $this->converter;
    }

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\Validator
     */
    protected function getConversionValidator()
    {
        $validationSchema = $this->getConversionValidationSchema();
        if ( $validationSchema !== null && $this->validator === null )
        {
            $this->validator = new Validator( $validationSchema );
        }

        return $this->validator;
    }

    /**
     * Returns subdirectories for input and output fixtures.
     *
     * The test will try to match each XML file in input directory with
     * the file of the same name in the output directory.
     *
     * It is possible to test lossy conversion as well (say legacy ezxml).
     * To use this file name of the fixture that is converted with data loss
     * needs to end with `.lossy.xml`. As input test with this fixture will
     * be skipped, but as output fixture it will be matched to the input
     * fixture file of the same name but without `.lossy` part.
     *
     * Comments in fixtures are removed before conversion, so be free to use
     * comments inside fixtures for documentation as needed.
     *
     * @return array
     */
    abstract public function getFixtureSubdirectories();

    /**
     * Return the absolute path to conversion transformation stylesheet.
     *
     * @return string
     */
    abstract protected function getConversionTransformationStylesheet();

    /**
     * Return custom XSLT stylesheets configuration.
     *
     * Stylesheet paths must be absolute.
     *
     * Code example:
     *
     * <code>
     *  array(
     *      array(
     *          "path" => __DIR__ . "/core.xsl",
     *          "priority" => 100
     *      ),
     *      array(
     *          "path" => __DIR__ . "/custom.xsl",
     *          "priority" => 99
     *      ),
     *  )
     * </code>
     *
     * @return array
     */
    protected function getCustomConversionTransformationStylesheets()
    {
        return array();
    }

    /**
     * Return an array of absolute paths to conversion result validation schemas.
     *
     * @return string[]
     */
    protected function getConversionValidationSchema()
    {
        return array();
    }
}
