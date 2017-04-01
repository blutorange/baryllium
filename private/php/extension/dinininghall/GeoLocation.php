<?php

/**
 * @author madgaksha
 */
namespace Moose\Extension\DiningHall;

class GeoLocation implements GeoLocationInterface {
    private $latitude, $longitude;

    public function __construct(float $latitude, float $longitude) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude(): float {
        return $this->latitude;
    }

    public function getLongitude(): float {
        return $this->longitude;
    }

    public function __toString() {
        return "GeoLocation($this->latitude, $this->longitude)";
    }
}