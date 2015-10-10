<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\Helper;

//----------------------------------------------------------------------------------------------------------------------
/**
 * Static class with helper functions for manipulating URLs.
 */
class Url
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if and only if an URL is a relative URL.
   *
   * Examples of relative URLs:
   * * /
   * * /foo
   * * ~/
   * * ~/foo
   * Counter examples:
   * * //
   * * /\
   * * https://www.setbased.nl/
   *
   * @param string $theUrl The URL.
   *
   * @return bool
   */
  public static function isRelative($theUrl)
  {
    if (is_string($theUrl) && $theUrl!='')
    {
      return ((mb_substr($theUrl, 0, 1)=='/' &&
          (mb_strlen($theUrl)==1 || (mb_substr($theUrl, 1, 1)!='/' && mb_substr($theUrl, 1, 1)!='\\'))) ||
        (mb_strlen($theUrl)>1 && mb_substr($theUrl, 0, 2)=='~/'));
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Replaces in HTML code relative URLs with absolute URLs.
   *
   * @param string $theHtml The HTML code.
   * @param string $theRoot The part of the URL's before the path part without slash.
   *
   * @return string
   */
  public static function relative2Absolute($theHtml, $theRoot)
  {
    return preg_replace("#(href|src)=(['\"])([^:'\"]*)(['\"]|(?:(?:%20|\\s|\\+)[^'\"]*))#",
                        "$1=$2".$theRoot."$3$4",
                        $theHtml);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
