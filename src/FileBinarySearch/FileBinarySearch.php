<?php

namespace FileBinarySearch;

/**
 * Tac is a tac and tail php implementation for php
 *
 * @author Pierre Tachoire <pierre.tachoire@gmail.com>
 */
class FileBinarySearch
{

    const VALUE = 'value';
    const POS = 'pos';

    protected $buffer_size = 1024;
    protected $filesize = null;
    protected $buffer = null;
    protected $hdle = null;
    protected $formater;
    protected $first=array();
    protected $last=array();

    public function __construct( $filename, $compare=null, $buffer_size=1 ) {
        $this->setComparator($compare);
        if( ($this->hdle = @fopen( $filename, "r" ) ) === false ) {
            $this->errorManager();
        }
        $this->filesize = filesize($filename);
        $this->buffer_size = $buffer_size;
    }

    protected function compare( $line, $value ) {
        return call_user_func($this->compare, $line, $value);
    }

    protected function basicomparator( $a, $b ) {
        return $a - $b;
    }

    protected function setComparator( $compare=null ) {
        if( $compare == null ) {
            $compare = array($this, 'basicomparator');
        }
        if( is_callable($compare) == false ) {
            throw new \InvalidArgumentException(sprintf('%s is not a callable function', print_r($compare, 1)));
        }
        $this->compare = $compare;
    }

    protected function init() {
        $this->first = $this->getLineAt(0);
        $this->fseek(0, SEEK_END);
        $this->last = $this->getLineAt( $this->ftell() );
    }

    public function search( $value ) {
        $this->init();
        $lo = $this->first;
        $hi = $this->last;

        try {
            while($hi[self::POS] > $lo[self::POS] ) {
                $mid = $this->getMid($lo, $hi);
                $cmp = $this->compare( $mid[self::VALUE], $value );
                if ($cmp < 0) {
                    $lo = $this->getNext($mid);
                } elseif ($cmp > 0) {
                    $hi = $this->getPrevious($mid);
                } else {
                    return $mid[self::VALUE];
                }
            }
        } catch( \InvalidArgumentException $ex ) {}
        return $this->compare( $lo[self::VALUE], $value ) == 0 ? $lo[self::VALUE] : false;
    }

    protected function getMid( array $lo, array $hi ) {
        $pos = $lo[self::POS] + round(($hi[self::POS]-$lo[self::POS])/2);
        return $this->getLineAt($pos);
    }

    protected function getNext( array $mid ) {
        $pos = $mid[self::POS]+1;
        return $this->getLineAt($pos);
    }

    protected function getPrevious( array $mid ) {
        return $this->getLineAt($mid[self::POS]-strlen($mid[self::VALUE])-2);
    }

    protected function getLineAt( $pos ) {
        $this->fseek( $pos );
        return array(
            self::VALUE => $this->nextline(),
            self::POS => $this->ftell()
        );
    }

    public function __destruct() {
        @fclose($this->hdle);
    }

    protected function fseek($offset=0, $whence=SEEK_SET) {
        if( $whence == SEEK_SET && ($offset < 0 || $offset > $this->filesize)) {
            throw new \InvalidArgumentException(sprintf('offset %d is not valid', $offset));
        }
        $this->buffer = null;
        if( fseek( $this->hdle, $offset, $whence ) < 0 ) {
            throw new \InvalidArgumentException(sprintf('offset %d is not valid', $offset));
        }
    }

    protected function ftell() {
        return ftell($this->hdle);
    }


    protected function nextline() {
        $line = false;
        while( ftell($this->hdle) > 0 || $this->buffer != null ) {
            if( $this->buffer == null ) {
                if( ( $readable_size = $this->readablesize( $this->hdle, $this->buffer_size ) ) == 0 ) {
                    break;
                }
                $this->goback( $this->hdle, $readable_size );
                $this->buffer = fread($this->hdle, $readable_size);
                $this->goback( $this->hdle, $readable_size );
            }

            if(($pos = strrpos($this->buffer, "\n")) === false ) {
                $line = $this->buffer . $line;
                $this->buffer = null;
            } else {
                $line = substr( $this->buffer, $pos+1 ) . $line;
                if( $line != '' ) {
                    break;
                }
                //je me replace au bon endroit
                $this->buffer = substr( $this->buffer, 0, $pos );
            }
        }
        if( (isset($pos) && $pos !== null && $pos !== false) ) {
            $this->fseek( $this->ftell($this->hdle)+$pos+1 );
        }
        return trim(fgets($this->hdle));
    }

    protected function goback( $hdle, $size ) {
        if( fseek($hdle, -$size, SEEK_CUR ) != 0 ) {
            fseek($hdle, 0, SEEK_SET );
        }
        return ftell($hdle);
    }

    protected function readablesize() {
        $tell=ftell($this->hdle);
        return $tell-$this->buffer_size > 0 ? $this->buffer_size : $tell;
    }

    protected function errorManager( $message=null ) {
        if(( $error = error_get_last()) != null ) {
            $pattern = $message != null ? "\n%s" : '%s';
            $message .= sprintf($pattern, $error['message']);
        }
        throw new \ErrorException( $message, $error['type'], 1, $error['file'], $error['line'] );
    }
}
