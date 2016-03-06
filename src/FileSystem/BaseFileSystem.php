<?php

/*
 * @author Dmytro Nekrasov <nekrasov.dmytro@gmil.com>
 * @see http://lviniy-web.org
 * */

namespace FileSystem;


class BaseFileSystem
{
    const DIR_FILE_TYPE = 'dir';

    /*
     * @var \SplFixedArray $fileArray
     * */
    protected $fileArray;

    /*
     * @var \FilesystemIterator $fileIterator
     * */
    protected $fileIterator;

    /*
     * @var int $index
     * */
    protected $index = 0;

    public function __construct()
    {
        $this->fileArray = new \SplFixedArray();
        $this->index = 0;
    }

    /*
     * @param string $path - real path like this /var/www/project
     * */
    public function recursiveReadDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \Exception("$dir is not a directory");
        }

        $fileIterator = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);

        //set size of SplFixedArray every circle of recursion
        $this->fileArray->setSize($this->fileArray->count() + iterator_count($fileIterator));

        //start from the beginning
        $fileIterator->rewind();

        while ($fileIterator->valid()) {
            $realPath = $fileIterator->getRealPath();

            // if Directory go recursion
            $fileIterator->isDir() && $this->recursiveReadDir($realPath);

            //if $realPath is file and we have a permission to read it
            if ($fileIterator->isFile() && $fileIterator->isReadable()) {
                $this->fileArray[$this->index] = $realPath;
                $this->index++;
            }

            $fileIterator->next();
        }
    }

    /*
     * @param string $path - real path like this /var/www/project
     * @return \SplFixedArray
     * */
    public function getFiles($dir)
    {
        $this->recursiveReadDir($dir);

        for ($i = $this->fileArray->count(); $i > 0; $i--) {
            if (!empty($this->fileArray[$i])) {
                break;
            }
        }

        $this->fileArray->setSize($i + 1);

        return $this->divideArray($this->fileArray, 50);
    }

    /*
     * @param \SplFixedArray $array
     * @param int $pieces - count of pieces in array and therefore count of workers
     * */
    public function divideArray(\SplFixedArray $array, $pieces)
    {
        if ($array->count() < $pieces) {
            throw new \Exception("Count of array is less than workers/pieces");
        }

        $countInOne = ceil($array->count() / $pieces);
        $simpleArray = [];
        $splFixedArray = new \SplFixedArray();
        $splFixedArray->setSize($countInOne);

        $array->rewind();
        $i = 0;
        while ($array->valid()) {

            $splFixedArray[$i] = $array->current();

            $array->next();
            $i++;

            // Count in one is same with counter $i
            if ($i == $countInOne) {
                $simpleArray[] = $splFixedArray;
                $splFixedArray = new \SplFixedArray();
                $splFixedArray->setSize($countInOne);
                $i = 0;
            }
        }

        //last iteration after circle
        if (count($simpleArray) < $pieces) {
            $simpleArray[] = $splFixedArray;
        }

        return $simpleArray;
    }

    /*
     * @param string $path - real path like this /var/www/project
     * @return string
     * */
    protected function preparePath($path)
    {
        // add slash if it is missing
        if (substr($path, -1) != '/') {
            $path = $path . \DIRECTORY_SEPARATOR;
        }

        return $path;
    }
}