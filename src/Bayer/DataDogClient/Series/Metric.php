<?php

namespace Bayer\DataDogClient\Series;

use Bayer\DataDogClient\Series\Metric\InvalidPointException;
use Bayer\DataDogClient\Series\Metric\InvalidTypeException;

class Metric {

    const TYPE_GAUGE   = 'gauge';
    const TYPE_COUNTER = 'counter';

    protected $name;
    protected $type;
    protected $host;
    protected $tags = array();
    protected $points = array();

    /**
     * A Metric groups multiple measure points.
     *
     * A single point or an array of multiple points
     * can be specified during initiating.
     *
     * @param string $name
     * @param array  $points
     */
    public function __construct($name, array $points) {
        // Allow constructing with a single point
        if (is_numeric($points[0])) {
            $points = array($points);
        }

        $this->setName($name)
            ->setPoints($points)
            ->setType(self::TYPE_GAUGE);
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Metric
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @throws InvalidTypeException
     *
     * @return Metric
     */
    public function setType($type) {
        if (!$this->isValidType($type)) {
            throw new InvalidTypeException('Type must be one of Metric::TYPE_*');
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @param mixed $host
     *
     * @return Metric
     */
    public function setHost($host) {
        $this->host = $host;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return Metric
     */
    public function setTags($tags) {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return Metric
     */
    public function addTag($name, $value) {
        $this->tags[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     *
     * @return Metric
     */
    public function removeTag($name) {
        if (isset($this->tags[$name])) {
            unset($this->tags[$name]);
        }

        return $this;
    }

    /**
     * @return Metric
     */
    public function removeTags() {
        $this->tags = array();

        return $this;
    }

    /**
     * @return array
     */
    public function getPoints() {
        return $this->points;
    }

    /**
     * @param array $points
     *
     * @return Metric
     */
    public function setPoints(array $points) {
        $this->removePoints();
        $this->addPoints($points);

        return $this;
    }

    /**
     * Add a new measure point to the metric.
     *
     * A point consists of an optional timestamp and a numeric value. If
     * no timestamp is specified, the current timestamp will be used. Order
     * matters. If a timestamp is specified, it should be the first value.
     *
     * Examples:
     *   Simple point:   array(20)
     *   With timestamp: array(1234567, 20)
     *
     * @param array $point
     * @throws InvalidPointException
     *
     * @return Metric
     */
    public function addPoint(array $point) {
        // Add timestamp if non provided
        if (!isset($point[1])) {
            $point = array(time(), $point[0]);
        }

        if (!is_integer($point[0])) {
            throw new InvalidPointException('Timestamp must be an integer');
        }

        if (!is_int($point[1]) && !is_float($point[1])) {
            throw new InvalidPointException('Value must be integer or float');
        }

        $this->points[] = $point;

        return $this;
    }

    /**
     * @param array $points
     *
     * @return Metric
     */
    public function addPoints(array $points) {
        foreach ($points as $point) {
            $this->addPoint($point);
        }

        return $this;
    }

    /**
     * @return Metric
     */
    public function removePoints() {
        $this->points = array();

        return $this;
    }

    /**
     * @return array
     */
    public function toArray() {
        $data = array(
            'metric' => $this->getName(),
            'type'   => $this->getType(),
            'points' => $this->getPoints(),
        );

        if ($host = $this->getHost()) {
            $data['host'] = $host;
        }

        if ($tags = $this->getTags()) {
            $data['tags'] = array();
            foreach ($tags as $tag => $value) {
                $data['tags'][] = "$tag:$value";
            }
        }

        return $data;
    }


    /**
     * @param $type
     *
     * @return bool
     */
    protected function isValidType($type) {
        return in_array(
            $type,
            array(
                self::TYPE_GAUGE,
                self::TYPE_COUNTER
            )
        );
    }
}