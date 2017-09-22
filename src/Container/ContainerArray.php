<?php

namespace Kollus\Component\Container;

class ContainerArray extends \ArrayIterator
{
    /**
     * @param mixed $element
     */
    public function appendElement($element)
    {
        $this->append($element);
    }

    /**
     * @param mixed $element
     * @return bool
     */
    public function removeElement($element)
    {
        $key = array_search($element, $this->getArrayCopy(), true);
        if ($key === false) {
            return false;
        }
        $this->offsetUnset($key);
        return true;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this, true);
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return ContainerArray
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->toArray(), $offset, $length, true));
    }
}
