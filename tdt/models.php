<?php

class Model_Module extends RedBean_SimpleModel {
    public function update() {
        if (R::find('module', 'name = :name', array(':name' => $this->name))) {
            throw new Exception('Module with name already exists.');
        }
    }
}

class Model_Resource extends RedBean_SimpleModel {
    // TODO Implemnt.
}

?>
