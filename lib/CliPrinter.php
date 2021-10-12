<?php

namespace ScCli;

class CliPrinter
{
    private function out($message)
    {
        echo $message;
    }

    private function newline()
    {
        $this->out("\n");
    }

    /**
     * @param string $message
     */
    public function display(string $message)
    {
        $this->newline();
        $this->out($message);
        $this->newline();
        $this->newline();
    }
}