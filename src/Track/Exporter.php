<?php

namespace App\Track;

use App\Entity\Track\Version;

class Exporter
{
    public const FORMAT_GPX = 'gpx';

    public function export(iterable $versions, string $format): string
    {
        switch ($format) {
            case self::FORMAT_GPX:
                $result = $this->exportGpx($versions);
                break;
            default:
                throw new \RuntimeException('Unknown format');
        }

        return $result;
    }

    /**
     * @param iterable|Version[] $versionCollection
     */
    public function exportGpx(iterable $versionCollection): string
    {
        $xml = new \SimpleXMLElement('<gpx/>');
        $xml->addAttribute('version', '1.1');
        $xml->addAttribute('creator', 'track-hub.com: http://track-hub.com/');

        foreach ($versionCollection as $version) {
            $trkXml = $xml->addChild('trk');

            $name = $version->getTrack()->getNameEn();
            if ($name !== null) {
                $trkXml->addChild('name', $name);
            }

            $trkSegXSml = $trkXml->addChild('trkseg');

            foreach ($version->getPoints() as $point) {
                $trkSegPointXml = $trkSegXSml->addChild('trkpt');
                $trkSegPointXml->addAttribute('lat', $point->getLat());
                $trkSegPointXml->addAttribute('lon', $point->getLng());
                if ($point->getElevation()) {
                    $trkSegPointXml->addChild('ele', $point->getElevation());
                }
            }

            foreach ($version->getWayPoints() as $wayPoint) {
                $wayPointXml = $xml->addChild('wpt');
                $wayPointXml->addAttribute('lat', $wayPoint->getLat());
                $wayPointXml->addAttribute('lon', $wayPoint->getLng());
                $wayPointXml->addChild('name', $wayPointXml->getName());
            }
        }

        $stringResult = $xml->asXML();
        if ($stringResult === false) {
            throw new \RuntimeException('XML generation failed');
        }

        return $stringResult;
    }
}
