<?php
abstract class AdminModel {
    
    protected   $offset = 0,
                $limit = 0,
                $order,
                $orders = [];
    
    public  $found = 0;

    public function __construct($page, $per_page, $order = "id") {
        $this->limit = $per_page;
        $this->offset = ($page - 1) * $this->limit;
        $this->order = $this->orders[$order];
    }
    
    abstract public function add($objects, $now = true);
    abstract public function update($objects, $now = true);
    abstract public function trash($ids, $now = true);
    abstract public function remove($ids, $now = true);
    
}