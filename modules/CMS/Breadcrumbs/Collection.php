<?php
namespace KodiCMS\CMS\Breadcrumbs;

class Collection implements \Countable, \Iterator
{

    /**
     *
     * @var array
     */
    protected $options = [
        'view' => 'cms::app.partials.breadcrumbs',
    ];

    /**
     *
     * @var array
     */
    protected $items = [];


    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }


    /**
     * @return boolean
     */
    public function isLast()
    {
        $items = $this->items;

        return $this->current() === end($items);
    }


    /**
     * @return boolean
     */
    public function isFirst()
    {
        $items = $this->items;

        return $this->current() === reset($items);
    }


    /**
     * @param string|static $name
     * @param bool          $url
     * @param bool|null     $isActive
     * @param integer|null  $position
     * @param array         $data
     *
     * @return $this
     */
    public function add($name, $url = false, $isActive = null, $position = null, array $data = [])
    {
        if ($name instanceof static) {
            foreach ($name as $item) {
                $this->addItem($item);
            }
        } else if ($name instanceof Item) {
            $this->addItem($name, $position);
        } else {
            $this->addItem(new Item(
                $name, $url, $isActive, $data), $position
            );
        }

        return $this;
    }


    /**
     * @param string $key
     * @param string $value
     *
     * @return Item
     */
    public function getBy($key, $value)
    {
        $position = $this->findBy($key, $value);

        if ($position === null) {
            return null;
        }

        return $this->items[$position];
    }


    /**
     * @param string $key
     * @param string $value
     *
     * @return int|null|string
     */
    public function findBy($key, $value)
    {
        foreach ($this->items as $position => $item) {
            if (is_array($value) and in_array($item->{$key}, $value)) {
                return $position;
            } else if ($item->{$key} == $value) {
                return $position;
            }
        }

        return null;
    }


    /**
     * @param string       $key
     * @param string       $value
     * @param bool         $url
     * @param bool         $isActive
     * @param integer|null $position
     * @param array        $data
     *
     * @return $this|bool
     */
    public function changeBy($key, $value, $url = false, $isActive = null, $position = null, array $data = [])
    {
        $item = $this->getBy($key, $value);
        if ($item === null) {
            return false;
        }

        $item->setUrl($url);

        if ( ! is_null($isActive)) {
            $item->setActive($isActive);
        }

        if ( ! empty( $data )) {
            $item->setAttribute($data);
        }

        if ( ! is_null($position)) {
            $position               = $this->getNextPosition($position);
            $this->items[$position] = $item;
        }

        return $this;
    }


    /**
     * @param string $name
     *
     * @return bool
     */
    public function deleteByName($name)
    {
        return $this->deleteBy('name', $name);
    }


    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function deleteBy($key, $value)
    {
        $position = $this->findBy($key, $value);

        if ($position === null) {
            return false;
        }

        unset( $this->items[$position] );
    }


    /**
     * @param null|integer $position
     *
     * @return int
     */
    protected function getNextPosition($position = null)
    {
        $position = (int) $position;

        while (isset( $this->items[$position] )) {
            $position++;
        }

        return $position;
    }


    public function __unset($name)
    {
        unset( $this->items[$name] );
    }


    protected function sort()
    {
        ksort($this->items);

        return $this;
    }


    /**
     * @return  integer
     */
    public function count()
    {
        return count($this->items);
    }


    public function rewind()
    {
        reset($this->items);
    }


    public function current()
    {
        $item = current($this->items);

        return $item;
    }


    /**
     * @return integer
     */
    public function key()
    {
        $item = key($this->items);

        return $item;
    }


    /**
     * @return Item
     */
    public function next()
    {
        $item = next($this->items);

        return $item;
    }


    /**
     * @return boolean
     */
    public function valid()
    {
        $key = key($this->items);

        return ( ! is_null($key) AND $key !== false );
    }


    /**
     * @return \View
     */
    public function render()
    {
        $this->sort();

        return view($this->options['view'], [
            'breadcrumbs' => $this,
        ]);
    }


    public function __toString()
    {
        return (string) $this->render();
    }


    /**
     * @param Item     $item
     * @param null|int $position
     */
    protected function addItem(Item $item, $position = null)
    {
        $position               = $this->getNextPosition($position);
        $this->items[$position] = $item;
    }
}
