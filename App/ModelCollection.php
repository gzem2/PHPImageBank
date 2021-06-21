<?php

declare(strict_types=1);

namespace PHPImageBank\App;

use PHPImageBank\App\Model;

/**
 * Collection of models
 */
class ModelCollection
{
    protected array $content = []; /**< models to hold in collection */

    /**
     * Add model to collection
     * @param Model $model model to add
     */
    public function add(Model $model) : void
    {
        array_push($this->content, $model);
    }

    /**
     * Get model at specific index
     * @param int $index index of a model
     * @return Model at index
     */
    public function get(int $index) : Model
    {
        return $this->content[$index];
    }

    /**
     * Get collection content
     * @return array of models
     */
    public function data() : array
    {
        return $this->content;
    }

    /**
     * Get first item of collection or false if collection is empty
     * @return Model or false
     */
    public function one()
    {
        if(!isset($this->content[0]) || empty($this->content[0])){
            return false;
        } else {
            return $this->content[0];
        }
    }
}