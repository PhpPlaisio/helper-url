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
}

//----------------------------------------------------------------------------------------------------------------------
