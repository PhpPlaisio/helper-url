<?php
//----------------------------------------------------------------------------------------------------------------------
use SetBased\Abc\Helper\Url;

//----------------------------------------------------------------------------------------------------------------------
class UrlTest extends PHPUnit_Framework_TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test relative URLs.
   */
  public function testIsRelative1()
  {
    $tests = ['/', '/foo', '~/', '~/foo'];

    foreach ($tests as $test)
    {
      $this->assertTrue(Url::isRelative($test));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test not relative URLs
   */
  public function testIsRelative2()
  {
    $tests = ['//', '/\\', 'https://www.setbased.nl'];

    foreach ($tests as $test)
    {
      $this->assertFalse(Url::isRelative($test));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for relative2Absolute.
   */
  public function testRelative2Absolute()
  {
    $cases = [];

    $cases[] = ['relative' => "<a href='/hello_world.html'>Hello World</a>",
                'absolute' => "<a href='http://www.example.com/hello_world.html'>Hello World</a>"];

    $cases[] = ['relative' => "<a href=\"/hello_world.html\">Hello World</a>",
                'absolute' => "<a href=\"http://www.example.com/hello_world.html\">Hello World</a>"];

    $cases[] = ['relative' => "<a href='/hello world.html'>Hello World</a>",
                'absolute' => "<a href='http://www.example.com/hello world.html'>Hello World</a>"];

    $cases[] = ['relative' => "<a href=\"/hello world.html\">Hello World</a>",
                'absolute' => "<a href=\"http://www.example.com/hello world.html\">Hello World</a>"];

    $cases[] = ['relative' => "<a href='/hello+world.html'>Hello World</a>",
                'absolute' => "<a href='http://www.example.com/hello+world.html'>Hello World</a>"];

    $cases[] = ['relative' => "<a href=\"/hello+world.html\">Hello World</a>",
                'absolute' => "<a href=\"http://www.example.com/hello+world.html\">Hello World</a>"];

    $cases[] = ['relative' => "<a href='/hello%20world.html'>Hello World</a>",
                'absolute' => "<a href='http://www.example.com/hello%20world.html'>Hello World</a>"];

    $cases[] = ['relative' => "<a href=\"/hello%20world.html\">Hello World</a>",
                'absolute' => "<a href=\"http://www.example.com/hello%20world.html\">Hello World</a>"];

    $cases[] = ['relative' => "<a href='/hello_world.html'>
                                    <img src='/images/hello_word.png' alt='hello world'/></a>",
                'absolute' => "<a href='http://www.example.com/hello_world.html'>
                                    <img src='http://www.example.com/images/hello_word.png' alt='hello world'/></a>"];

    foreach ($cases as $case)
    {
      $url = Url::relative2Absolute($case['relative'], 'http://www.example.com');
      $this->assertEquals($case['absolute'], $url);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for combine.
   */
  public function testcombine()
  {
    $cases = [];

    $cases[] = ['relative' => 'http://0:0@a/b/c/g', 'result' => 'http://0:0@a/b/c/g'];
    $cases[] = ['relative' => 'http://0.0:0.0@a:8080/b/c/g', 'result' => 'http://0.0:0.0@a:8080/b/c/g'];
    $cases[] = ['relative' => 'http://a1:a2@a:8080/b/c/g', 'result' => 'http://a1:a2@a:8080/b/c/g'];
    $cases[] = ['relative' => 'g', 'result' => 'http://a/b/c/g'];
    $cases[] = ['relative' => './g', 'result' => 'http://a/b/c/g'];
    $cases[] = ['relative' => 'g/', 'result' => 'http://a/b/c/g/'];
    $cases[] = ['relative' => '/g', 'result' => 'http://a/g'];
    $cases[] = ['relative' => '//g', 'result' => 'http://g'];
    $cases[] = ['relative' => 'g?y', 'result' => 'http://a/b/c/g?y'];
    $cases[] = ['relative' => '#s', 'result' => 'http://a/b/c/d;p?q#s'];
    $cases[] = ['relative' => 'g#s', 'result' => 'http://a/b/c/g#s'];
    $cases[] = ['relative' => 'g?y#s', 'result' => 'http://a/b/c/g?y#s'];
    $cases[] = ['relative' => ';x', 'result' => 'http://a/b/c/;x'];
    $cases[] = ['relative' => 'g;x', 'result' => 'http://a/b/c/g;x'];
    $cases[] = ['relative' => 'g;x?y#s', 'result' => 'http://a/b/c/g;x?y#s'];
    $cases[] = ['relative' => '.', 'result' => 'http://a/b/c/'];
    $cases[] = ['relative' => './', 'result' => 'http://a/b/c/'];
    $cases[] = ['relative' => '..', 'result' => 'http://a/b/'];
    $cases[] = ['relative' => '../', 'result' => 'http://a/b/'];
    $cases[] = ['relative' => '../g', 'result' => 'http://a/b/g'];
    $cases[] = ['relative' => '../..', 'result' => 'http://a/'];
    $cases[] = ['relative' => '../../', 'result' => 'http://a/'];
    $cases[] = ['relative' => '../../g', 'result' => 'http://a/g'];
    $cases[] = ['relative' => 'g.', 'result' => 'http://a/b/c/g.'];
    $cases[] = ['relative' => '.g', 'result' => 'http://a/b/c/.g'];
    $cases[] = ['relative' => 'g..', 'result' => 'http://a/b/c/g..'];
    $cases[] = ['relative' => '..g', 'result' => 'http://a/b/c/..g'];
    $cases[] = ['relative' => './../g', 'result' => 'http://a/b/g'];
    $cases[] = ['relative' => './g/.', 'result' => 'http://a/b/c/g/'];
    $cases[] = ['relative' => 'g/./h', 'result' => 'http://a/b/c/g/h'];
    $cases[] = ['relative' => 'g/../h', 'result' => 'http://a/b/c/h'];
    $cases[] = ['relative' => 'g;x=1/./y', 'result' => 'http://a/b/c/g;x=1/y'];
    $cases[] = ['relative' => 'g;x=1/../y', 'result' => 'http://a/b/c/y'];
    $cases[] = ['relative' => 'g?y/./x', 'result' => 'http://a/b/c/g?y/./x'];
    $cases[] = ['relative' => 'g?y/../x', 'result' => 'http://a/b/c/g?y/../x'];
    $cases[] = ['relative' => 'g#s/./x', 'result' => 'http://a/b/c/g#s/./x'];
    $cases[] = ['relative' => 'g#s/../x', 'result' => 'http://a/b/c/g#s/../x'];

    foreach ($cases as $case)
    {
      $url = Url::combine('http://a/b/c/d;p?q', $case['relative']);
      $this->assertEquals($case['result'], $url);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
