FileBinarySearch
===

Usage
-----

Usage sample :

  require_once( 'src/autoload.php');
  $file = tempnam("/tmp", "sample_file_binary_search_");
  file_put_contents($file, implode("\n", range(10,20,2)));

  $fbs = new \FileBinarySearch\FileBinarySearch($file);

  var_dump( $fbs->search(10));
  /*
  string(2) "10"
  */

  var_dump( $fbs->search(14));
  /*
  string(2) "14"
  */

  var_dump( $fbs->search(15));
  /*
  bool(false)
   */

  var_dump( $fbs->search(25));
  /*
  bool(false)
  */

If you have formated lines, you can use your own comparaison method

  function mycompare( $tested_line, $searched_value ) {
      //returns < 0 if $tested_line < $searched_value
      //returns > 0 if $tested_line > $searched_value
      //returns 0 if $tested_line == $searched_value
  }

  $fbs = new \FileBinarySearch\FileBinarySearch($file, 'mycompare');

Unit Tests
----------

    phpunit

Thanks
------

Files structure inspired by [Geocoder](https://github.com/willdurand/Geocoder)
from [William Durand](https://github.com/willdurand)