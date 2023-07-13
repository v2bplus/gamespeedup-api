<?php
namespace CoreController;

abstract class CliAbstract extends CommonAbstract
{
    public function init()
    {
        parent::init();
        $this->disableView();
    }
}
