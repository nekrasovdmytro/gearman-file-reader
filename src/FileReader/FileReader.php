<?php

/*
 * @author Dmytro Nekrasov <nekrasov.dmytro@gmil.com>
 * @see http://lviniy-web.org
 * */

namespace FileReader;


class FileReader
{
    /*
     * @var \SplFixedArray $source
     * */
    protected $source;

    /*
     * @param \SplFixedArray $source
     * */
    public function setSource(\SplFixedArray $source)
    {
        $this->source = $source;
    }

    public function read()
    {
        $this->source->rewind();

        while($this->source->valid())
        {
            echo "\n";

            echo $this->source->current();

            $this->source->next();
        }
    }
}