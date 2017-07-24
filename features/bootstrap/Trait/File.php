<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-24 10:12
 */
trait File
{
    /**
     * @Given a non existed file as $:arg1
     */
    public function aNonExistedFileAs($arg1)
    {
        $arg1 = str_replace("$", "", $arg1);
        $this->variableList[$arg1] = sys_get_temp_dir() . '/' . uniqid('vd_', true);;
    }
}