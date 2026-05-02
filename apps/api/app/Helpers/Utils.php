<?php

namespace App\Helpers;

use DeviceDetector\DeviceDetector;

class Utils
{
    public static function getDeviceDetectorByUserAgent(string $userAgent): DeviceDetector
    {
        $detector = new DeviceDetector(
            userAgent: $userAgent,
        );

        $detector->parse();

        return $detector;
    }

    /**
     * Get device name from user agent
     */
    public static function getDeviceNameFromDetector(DeviceDetector $device): string
    {
        return implode(' / ', array_filter([
            trim(implode(' ', [$device->getOs('name'), $device->getOs('version')])),
            trim(implode(' ', [$device->getClient('name'), $device->getClient('version')])),
        ])) ?? 'Unknown';
    }
}
