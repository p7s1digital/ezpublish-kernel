<?php
/**
 * File containing the RichTextStorage class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Psr\Log\LoggerInterface;
use DOMDocument;
use DOMXPath;

class RichTextStorage extends GatewayBasedStorage
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct( array $gateways = array(), LoggerInterface $logger = null )
    {
        parent::__construct( $gateways );
        $this->logger = $logger;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /** @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );
        $document = new DOMDocument;
        $document->loadXML( $field->value->data );

        $xpath = new DOMXPath( $document );
        $xpath->registerNamespace( "docbook", "http://docbook.org/ns/docbook" );
        // This will select only links with non-empty 'xlink:href' attribute value
        $xpathExpression = "//docbook:link[string( @xlink:href ) and not( starts-with( @xlink:href, 'ezurl://' )" .
            "or starts-with( @xlink:href, 'ezcontent://' )" .
            "or starts-with( @xlink:href, 'ezlocation://' )" .
            "or starts-with( @xlink:href, '#' ) )]";

        $links = $xpath->query( $xpathExpression );

        if ( empty( $links ) )
        {
            return false;
        }

        $urlSet = array();
        $remoteIdSet = array();
        $linksInfo = array();

        /** @var \DOMElement $link */
        foreach ( $links as $index => $link )
        {
            preg_match(
                "~^(ezremote://)?([^#]*)?(#.*|\\s*)?$~",
                $link->getAttribute( "xlink:href" ),
                $matches
            );
            $linksInfo[$index] = $matches;

            if ( empty( $matches[1] ) )
            {
                $urlSet[$matches[2]] = true;
            }
            else
            {
                $remoteIdSet[$matches[2]] = true;
            }
        }

        $linksIds = $gateway->getLinkIds( array_keys( $urlSet ) );
        $contentIds = $gateway->getContentIds( array_keys( $remoteIdSet ) );

        foreach ( $links as $index => $link )
        {
            list( , $scheme, $url, $fragment ) = $linksInfo[$index];

            if ( empty( $scheme ) )
            {
                if ( !isset( $linksIds[$url] ) )
                {
                    $linksIds[$url] = $gateway->insertLink( $url );
                }
                $href = "ezurl://{$linksIds[$url]}{$fragment}";
            }
            else
            {
                if ( !isset( $contentIds[$url] ) )
                {
                    throw new NotFoundException( "Content", $url );
                }
                $href = "ezcontent://{$contentIds[$url]}{$fragment}";
            }

            $link->setAttribute( "xlink:href", $href );
        }

        $field->value->data = $document->saveXML();

        return true;
    }

    /**
     * Modifies $field if needed, using external data (like for Urls)
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /** @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );
        $document = new DOMDocument;
        $document->loadXML( $field->value->data );

        $xpath = new DOMXPath( $document );
        $xpath->registerNamespace( "docbook", "http://docbook.org/ns/docbook" );
        $xpathExpression = "//docbook:link[starts-with( @xlink:href, 'ezurl://' )]";

        $links = $xpath->query( $xpathExpression );

        if ( empty( $links ) )
        {
            return;
        }

        $linkIdSet = array();
        $linksInfo = array();

        /** @var \DOMElement $link */
        foreach ( $links as $index => $link )
        {
            preg_match(
                "~^ezurl://([^#]*)?(#.*|\\s*)?$~",
                $link->getAttribute( "xlink:href" ),
                $matches
            );
            $linksInfo[$index] = $matches;

            if ( !empty( $matches[1] ) )
            {
                $linkIdSet[$matches[1]] = true;
            }
        }

        $linkUrls = $gateway->getLinkUrls( array_keys( $linkIdSet ) );

        foreach ( $links as $index => $link )
        {
            list( , $urlId, $fragment ) = $linksInfo[$index];

            if ( isset( $linkUrls[$urlId] ) )
            {
                $href = $linkUrls[$urlId] . $fragment;
            }
            else
            {
                // URL id is empty or not in the DB
                if ( isset( $this->logger ) )
                {
                    $this->logger->error( "URL with ID {$urlId} not found" );
                }
                $href = "#";
            }

            $link->setAttribute( "xlink:href", $href );
        }

        $field->value->data = $document->saveXML();
    }

    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
    }
}
