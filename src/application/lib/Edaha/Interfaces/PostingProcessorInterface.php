<?php
namespace Edaha\Interfaces;

interface PostingProcessorInterface
{
    public function preValidate();
    public function validate();
    public function preProcess();
    public function process();
    public function postProcess();
    public function postCommit();
}
