<?php

namespace FileBinarySearch\Tests;

use FileBinarySearch\FileBinarySearch;

/**
 * @author Pierre Tachoire
 */
class FileBinarySearchTest extends \PHPUnit_Framework_TestCase
{

    protected $tmp_filename;

    protected function setup( ) {
        $this->tmp_filename = tempnam("/tmp", "file_binary_search_");
        file_put_contents($this->tmp_filename, implode("\n", range(0,1000,2)));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFseekTooHigh() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $fbs->fseek( filesize($this->tmp_filename)+1 );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFseekTooLow() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $fbs->fseek( -1 );
    }

    public function testFseek() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $fbs->fseek( 0 );
        $this->assertEquals( 0, $fbs->ftell() );

        $fbs->fseek( 0, SEEK_END );
        $this->assertEquals( filesize($this->tmp_filename), $fbs->ftell() );

        $fbs->fseek( -10, SEEK_END );
        $this->assertEquals( filesize($this->tmp_filename)-10, $fbs->ftell() );
    }

    public function testGetLineAt() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $tested = $fbs->getLineAt( 0 );
        $expected = array(
            FileBinarySearch::VALUE => 0,
            FileBinarySearch::POS => 2
        );
        $this->assertEquals($expected, $tested );

        $tested = $fbs->getLineAt( 1 );
        $expected = array(
            FileBinarySearch::VALUE => 0,
            FileBinarySearch::POS => 2
        );
        $this->assertEquals($expected, $tested );

        $tested = $fbs->getLineAt( 2 );
        $expected = array(
            FileBinarySearch::VALUE => 0,
            FileBinarySearch::POS => 2
        );
        $this->assertEquals($expected, $tested );

        $tested = $fbs->getLineAt( 3 );
        $expected = array(
            FileBinarySearch::VALUE => 2,
            FileBinarySearch::POS => 4
        );
        $this->assertEquals($expected, $tested );

        $tested = $fbs->getLineAt( 4 );
        $expected = array(
            FileBinarySearch::VALUE => 2,
            FileBinarySearch::POS => 4
        );
        $this->assertEquals($expected, $tested );

        $tested = $fbs->getLineAt( 5 );
        $expected = array(
            FileBinarySearch::VALUE => 4,
            FileBinarySearch::POS => 6
        );
        $this->assertEquals($expected, $tested );

        $tested = $fbs->getLineAt( 8 );
        $expected = array(
            FileBinarySearch::VALUE => 6,
            FileBinarySearch::POS => 8
        );
        $this->assertEquals($expected, $tested );

        $fbs->fseek(0, SEEK_END);
        $expected_pos = $fbs->ftell();
        $tested = $fbs->getLineAt( $expected_pos );
        $expected = array(
            FileBinarySearch::VALUE => 1000,
            FileBinarySearch::POS => $expected_pos
        );
        $this->assertEquals($expected, $tested );

        $expected_pos = $expected_pos-4;
        $tested = $fbs->getLineAt( $expected_pos );
        $expected = array(
            FileBinarySearch::VALUE => 998,
            FileBinarySearch::POS => $expected_pos
        );
        $this->assertEquals($expected, $tested );

    }

    public function testGetNext() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $start = $fbs->getLineAt( 0 );
        $tested = $fbs->getNext( $start );
        $expected = array(
            FileBinarySearch::VALUE => 2,
            FileBinarySearch::POS => 4
        );
        $this->assertEquals($expected, $tested );

        $tested = $fbs->getNext( $tested );
        $expected = array(
            FileBinarySearch::VALUE => 4,
            FileBinarySearch::POS => 6
        );
        $this->assertEquals($expected, $tested );

        $fbs->fseek(-4, SEEK_END);
        $pos = $fbs->ftell();
        $start = $fbs->getLineAt( $pos );
        $tested = $fbs->getNext( $start );
        $expected = array(
            FileBinarySearch::VALUE => 1000,
            FileBinarySearch::POS => $pos+4
        );
        $this->assertEquals($expected, $tested );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetNextAtEnd() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $fbs->fseek(0, SEEK_END);
        $pos = $fbs->ftell();
        $start = $fbs->getLineAt( $pos );
        $tested = $fbs->getNext( $start );
    }

    public function testGetPrevious() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $start = $fbs->getLineAt( 3 );
        $tested = $fbs->getPrevious( $start );
        $expected = array(
            FileBinarySearch::VALUE => 0,
            FileBinarySearch::POS => 2
        );
        $this->assertEquals($expected, $tested );

        $start = $fbs->getLineAt( 5 );
        $tested = $fbs->getPrevious( $start );
        $expected = array(
            FileBinarySearch::VALUE => 2,
            FileBinarySearch::POS => 4
        );
        $this->assertEquals($expected, $tested );

        $fbs->fseek(0, SEEK_END);
        $start = $fbs->getLineAt( $fbs->ftell() );
        $tested = $fbs->getPrevious( $start );
        $expected = array(
            FileBinarySearch::VALUE => 998,
            FileBinarySearch::POS => $start[FileBinarySearch::POS]-4
        );
        $this->assertEquals($expected, $tested);

        $tested = $fbs->getPrevious( $tested );
        $expected = array(
            FileBinarySearch::VALUE => 996,
            FileBinarySearch::POS => $start[FileBinarySearch::POS]-8
        );
        $this->assertEquals($expected, $tested);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetPriousAtStart() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $start = $fbs->getLineAt( 0 );
        $tested = $fbs->getPrevious( $start );
    }

    public function testInit() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $fbs->init();
        $expected = array(
            FileBinarySearch::VALUE => 0,
            FileBinarySearch::POS => 2
        );
        $this->assertEquals($expected, $expected);
        $expected = array(
            FileBinarySearch::VALUE => 1000,
            FileBinarySearch::POS => 1949
        );
        $this->assertEquals($expected, $expected);
    }

    public function testGetMid() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $left = $fbs->getLineAt(0);
        $expected = $fbs->getNext($left);
        $right = $fbs->getNext($expected);

        $tested = $fbs->getMid($left,$right);
        $this->assertEquals( $expected, $tested );

    }

    public function testNotFound() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $this->assertTrue( $fbs->search( 1 ) === false );
        $this->assertTrue( $fbs->search( -1 ) === false );
        $this->assertTrue( $fbs->search( 175 ) === false );
        $this->assertTrue( $fbs->search( 515 ) === false );
        $this->assertTrue( $fbs->search( 10001 ) === false );
        $this->assertTrue( $fbs->search( 999 ) === false );
        $this->assertTrue( $fbs->search( 501 ) === false );
        $this->assertTrue( $fbs->search( 759 ) === false );
    }

    public function testFound() {
        $fbs = new FileBinarySearchTestable($this->tmp_filename);
        $expected = 0;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 2;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 10;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 452;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 500;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 514;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 516;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 998;
        $this->assertEquals( $expected, $fbs->search($expected) );
        $expected = 1000;
        $this->assertEquals( $expected, $fbs->search($expected) );
    }

}

class FileBinarySearchTestable extends FileBinarySearch {
    public $first;
    public $last;
    public function ftell() { return parent::ftell(); }
    public function fseek($offset=0, $whence=SEEK_SET) { return parent::fseek($offset,$whence); }
    public function getLineAt($offset) { return parent::getLineAt($offset); }
    public function getPrevious(array $mid) { return parent::getPrevious($mid); }
    public function getNext(array $mid) { return parent::getNext($mid); }
    public function init() { return parent::init(); }
    public function getMid(array $a, array $b) { return parent::getMid($a,$b); }

}