<?php

namespace hypeJunction\Prototyper\Structs;

/**
 * A read-only interface to a (possibly mutable) group of items.
 * Brrowed from Elgg core
 */
interface Collection extends \Countable, \Iterator {
	
	/**
	 * Returns a new collection only containing the elements which pass the filter.
	 * 
	 * @param callable $filter Receives an item. Return true to keep the item.
	 * 
	 * @return Collection
	 */
	public function filter(callable $filter);
	
	/**
	 * Returns true iff the item is in this collection at least once.
	 * 
	 * @param mixed $item The object or value to check for 
	 * 
	 * @return boolean
	 */
	public function contains($item);

	/**
	 * Take items of the collection and return a new collection
	 * with all the items having the $mapper applied to them.
	 * 
	 * The callable is not guaranteed to execute immediately for each item.
	 * 
	 * @param callable $mapper Returns the mapped value
	 * 
	 * @return Collection
	 */
	public function map(callable $mapper);
}