<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Map\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Polygon;

class MapFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        DummyOptions::registerToNormalizer();
    }

    protected function tearDown(): void
    {
        DummyOptions::unregisterFromNormalizer();
    }

    public function testFromArray(): void
    {
        $array = self::createMapArray();
        $map = Map::fromArray($array);

        $this->assertEquals($array['center']['lat'], $map->toArray()['center']['lat']);
        $this->assertEquals($array['center']['lng'], $map->toArray()['center']['lng']);

        $this->assertEquals((float) $array['zoom'], $map->toArray()['zoom']);

        $this->assertCount(1, $markers = $map->toArray()['markers']);
        $this->assertEquals($array['markers'][0]['position']['lat'], $markers[0]['position']['lat']);
        $this->assertEquals($array['markers'][0]['position']['lng'], $markers[0]['position']['lng']);
        $this->assertSame($array['markers'][0]['title'], $markers[0]['title']);
        $this->assertSame($array['markers'][0]['infoWindow']['headerContent'], $markers[0]['infoWindow']['headerContent']);
        $this->assertSame($array['markers'][0]['infoWindow']['content'], $markers[0]['infoWindow']['content']);

        $this->assertCount(1, $polygons = $map->toArray()['polygons']);
        $this->assertEquals($array['polygons'][0]['points'], $polygons[0]['points']);
        $this->assertEquals($array['polygons'][0]['points'], $polygons[0]['points']);
        $this->assertSame($array['polygons'][0]['title'], $polygons[0]['title']);
        $this->assertSame($array['polygons'][0]['infoWindow']['headerContent'], $polygons[0]['infoWindow']['headerContent']);
        $this->assertSame($array['polygons'][0]['infoWindow']['content'], $polygons[0]['infoWindow']['content']);

        $this->assertCount(1, $polylines = $map->toArray()['polylines']);
        $this->assertEquals($array['polylines'][0]['points'], $polylines[0]['points']);
        $this->assertEquals($array['polylines'][0]['points'], $polylines[0]['points']);
        $this->assertSame($array['polylines'][0]['title'], $polylines[0]['title']);
        $this->assertSame($array['polylines'][0]['infoWindow']['headerContent'], $polylines[0]['infoWindow']['headerContent']);
        $this->assertSame($array['polylines'][0]['infoWindow']['content'], $polylines[0]['infoWindow']['content']);
    }

    public function testToArrayFromArray(): void
    {
        $map = (new Map())
            ->center(new Point(48.8566, 2.3522))
            ->zoom(12)
            ->addMarker(new Marker(
                position: new Point(48.8566, 2.3522),
                title: 'Paris',
                infoWindow: new InfoWindow('Welcome to Paris, the city of lights', extra: ['color' => 'red']),
                extra: ['color' => 'blue'],
            ))
            ->addMarker(new Marker(
                position: new Point(44.837789, -0.57918),
                title: 'Bordeaux',
                infoWindow: new InfoWindow('Welcome to Bordeaux, the city of wine', extra: ['color' => 'red']),
                extra: ['color' => 'blue'],
            ))
            ->addPolygon(new Polygon(
                points: [
                    new Point(48.858844, 2.294351),
                    new Point(48.853, 2.3499),
                    new Point(48.8566, 2.3522),
                ],
                title: 'Polygon 1',
                infoWindow: new InfoWindow('Polygon 1', 'Polygon 1', extra: ['color' => 'red']),
                extra: ['color' => 'blue'],
            ));

        $newMap = Map::fromArray($map->toArray());

        $this->assertEquals($map->toArray(), $newMap->toArray());
    }

    public function testFromArrayWithInvalidCenter(): void
    {
        $array = self::createMapArray();
        $array['center'] = 'invalid';

        $this->expectException(\TypeError::class);
        Map::fromArray($array);
    }

    public function testFromArrayWithInvalidZoom(): void
    {
        $array = self::createMapArray();
        $array['zoom'] = 'invalid';

        $this->expectException(\TypeError::class);
        Map::fromArray($array);
    }

    public function testFromArrayWithInvalidMarkers(): void
    {
        $array = self::createMapArray();
        $array['markers'] = 'invalid';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "markers" parameter must be an array.');
        Map::fromArray($array);
    }

    public function testFromArrayWithInvalidMarker(): void
    {
        $array = self::createMapArray();
        $array['markers'] = [
            [
                'invalid',
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "position" parameter is required.');
        Map::fromArray($array);
    }

    public function testFromArrayWithInvalidPolygons(): void
    {
        $array = self::createMapArray();
        $array['polygons'] = 'invalid';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "polygons" parameter must be an array.');
        Map::fromArray($array);
    }

    public function testFromArrayWithInvalidPolygon(): void
    {
        $array = self::createMapArray();
        $array['polygons'] = [
            [
                'invalid',
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "points" parameter is required.');
        Map::fromArray($array);
    }

    public function testFromArrayWithInvalidPolylines(): void
    {
        $array = self::createMapArray();
        $array['polylines'] = 'invalid';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "polylines" parameter must be an array.');
        Map::fromArray($array);
    }

    public function testFromArrayWithInvalidPolyline(): void
    {
        $array = self::createMapArray();
        $array['polylines'] = [
            [
                'invalid',
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "points" parameter is required.');
        Map::fromArray($array);
    }

    private static function createMapArray(): array
    {
        return [
            'center' => [
                'lat' => 48.8566,
                'lng' => 2.3522,
            ],
            'zoom' => 12,
            'markers' => [
                [
                    'position' => [
                        'lat' => 48.8566,
                        'lng' => 2.3522,
                    ],
                    'title' => 'Paris',
                    'infoWindow' => [
                        'headerContent' => 'Paris',
                        'content' => 'Paris, the city of lights',
                    ],
                ],
            ],
            'polygons' => [
                [
                    'points' => [
                        [
                            'lat' => 48.858844,
                            'lng' => 2.294351,
                        ],
                        [
                            'lat' => 48.853,
                            'lng' => 2.3499,
                        ],
                        [
                            'lat' => 48.8566,
                            'lng' => 2.3522,
                        ],
                    ],
                    'title' => 'Polygon 1',
                    'infoWindow' => [
                        'headerContent' => 'Polygon 1',
                        'content' => 'Polygon 1',
                    ],
                ],
            ],
            'polylines' => [
                [
                    'points' => [
                        [
                            'lat' => 48.858844,
                            'lng' => 2.294351,
                        ],
                        [
                            'lat' => 48.853,
                            'lng' => 2.3499,
                        ],
                        [
                            'lat' => 48.8566,
                            'lng' => 2.3522,
                        ],
                    ],
                    'title' => 'Polyline 1',
                    'infoWindow' => [
                        'headerContent' => 'Polyline 1',
                        'content' => 'Polyline 1',
                    ],
                ],
            ],
        ];
    }
}
