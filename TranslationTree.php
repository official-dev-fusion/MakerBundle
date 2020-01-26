<?php

namespace DF\MakerBundle;

/**
 * TranslationTree is a container for key/value pairs or key/sub containerhr.
 * This container does not overwrite the old values
 *
 * Example:
 * $tree = [
 *     'tab0' => 'foo',
 *     'tab1' => [ 'tab1_1' => 'bar', ],
 * ];
 * echo($tree['tab1']['tab1_1']); // Display : bar
 * Can be written:
 * $tree = new TranslationTree();
 * $tree
 *     ->set('tab0', 'foo')
 *     ->set('tab1.tab1_1', 'bar');
 * echo($tree->get('tab1.tab1_1')); // Display : bar
 *
 * If you write :
 * $tree = new TranslationTree();
 * $tree->set('ta0.tab0_1', 'foo');
 * $tree->set('ta0.tab0_1', 'bar');
 * echo($tree->get('ta0.tab0_1'));
 * It will show : foo
 */
class TranslationTree implements \IteratorAggregate, \Countable
{
    private $tree;
    
    public function __construct($tree=[])
    {
        $this->tree = $tree;
    }
    
    /**
     * Returns the tree.
     *
     * @return array A tree in array
     */
    public function all()
    {
        return $this->tree;
    }
    
    /**
     * Replaces the current tree by a new set.
     *
     * @param array $tree An array of tree
     */
    public function replace(array $tree=[])
    {
        $this->tree = $tree;
    }
    
    /**
     * Returns a value with key and sub keys.
     *
     * @param string $keys     The key with sub keys
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get($keys, $default=null)
    {
        $keys = explode('.', $keys);
        $tree = &$this->tree;
        foreach ($keys as $key) {
            if (!isset($tree[$key])) {
                return $default;
            }
            if (false !== next($keys)) {
                $tree = &$tree[$key];
            } else {
                return $tree[$key];
            }
        }
        
        throw new \LogicException("No keys found");
    }

    /**
     * Sets a value with key and sub keys.
     *
     * @param string $keys   The keys
     * @param mixed  $value The value
     */
    public function set(string $keys, $value)
    {
        $keys = explode('.', $keys);
        $tree = &$this->tree;
        foreach ($keys as $key) {
            if (false !== next($keys)) {
                $tree[$key] = $tree[$key] ?? [];
                if (!is_array($tree[$key])) {
                    throw new \LogicException("The value for key $key is not an array.");
                }
                $tree = &$tree[$key];
            } else {
                $tree[$key] = $tree[$key] ?? $value;
            }
        }
        
        return $this;
    }

    /**
     * Returns true if the key and sub key is defined
     *
     * @param string $keys The key and sub keys
     *
     * @return bool true if the keys exist, false otherwise
     */
    public function has($key)
    {
        $keys = explode('.', $keys);
        $tree = &$this->tree;
        foreach ($keys as $key) {
            if (!isset($tree[$key])) {
                return $false;
            }
            if (false !== next($keys)) {
                $tree = &$tree[$key];
            } else {
                return true;
            }
        }
        
        throw new \LogicException("No keys found");
    }

    /**
     * Removes a value.
     *
     * @param string $keys The key or sub keys
     */
    public function remove($key)
    {
        $keys = explode('.', $keys);
        $tree = &$this->tree;
        foreach ($keys as $key) {
            if (!isset($tree[$key])) {
                return $false;
            }
            if (false !== next($keys)) {
                $tree = &$tree[$key];
            } else {
                unset($tree[$key]);
                return true;
            }
        }
        
        throw new \LogicException("No keys found");
    }

    /**
     * Returns an iterator for tree.
     *
     * @return \RecursiveIteratorIterator An \RecursiveIteratorIterator instance
     */
    public function getIterator()
    {
        return new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->tree));
    }

    /**
     * Returns the number of leaves.
     *
     * @return int The number of leaves
     */
    public function count()
    {
        $count=0;
        foreach ($this as $leave) {
            $count++;
        }
        return $count;
    }

    public function ksort()
    {
        $this->ksort_recursive($this->tree);

        return $this;
    }

    private function ksort_recursive(&$array)
    {
        if (is_array($array)) {
            ksort($array);
            array_walk($array, [ $this, 'ksort_recursive' ]);
        }
    }

    public function keys()
    {
        $result=[];
        $this->keysRecursive($this->tree, "", "", $result);
        return $result;
    }
    
    private function keysRecursive(&$array, $key, $keys, &$result)
    {
        if (is_array($array)) {
            if ($key) {
                $keys .= $key . '.';
            }
            foreach ($array as $key => $value) {
                $this->keysRecursive($value, $key, $keys, $result);
            }
        } else {
            $result[] = $keys . $key;
        }
    }
}
