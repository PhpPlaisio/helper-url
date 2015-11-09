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
   * @param string $theRoot The part of the URLs before the path part without slash.
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
  /**
   * Combine URL and RelativeURL to single URL and return.
   *
   * @param string $theUri         The part of the URLs before the path part without slash.
   * @param string $theRelativeUri The path part of the URLs.
   *
   * @return string
   */
  public static function combine($theUri, $theRelativeUri)
  {
    $Uri         = parse_url($theUri);
    $RelativeUri = parse_url($theRelativeUri);

    switch (true)
    {
      case !empty($RelativeUri['scheme']) || !empty($RelativeUri['host']):
        break;
      case empty($RelativeUri['path']):
        $RelativeUri['path'] = self::normalize_path($Uri['path']);
        if (empty($RelativeUri['query']))
        {
          $RelativeUri['query'] = $Uri['query'];
        }
        break;
      case strpos($RelativeUri['path'], '/')===0:
        break;
      default:
        $path = $Uri['path'];
        if (strpos($path, '/')===false)
        {
          $path = '';
        }
        else
        {
          $path = preg_replace('/\/[^\/]+$/', '/', $path);
        }
        if (empty($path) && empty($Uri['host']))
        {
          $path = '/';
        }
        $RelativeUri['path'] = self::normalize_path($path.$RelativeUri['path']);
    }
    if (empty($RelativeUri['scheme']))
    {
      $RelativeUri['scheme'] = $Uri['scheme'];
      if (empty($RelativeUri['host']))
      {
        $RelativeUri['host'] = $Uri['host'];
      }
    }
    if(empty($RelativeUri['user'])){$RelativeUri['user'] = !empty($Uri['user']) ? $Uri['user'] : '';}
    if(empty($RelativeUri['pass'])){$RelativeUri['pass'] = !empty($Uri['pass']) ? $Uri['pass'] : '';}
    if(empty($RelativeUri['port'])){$RelativeUri['port'] = !empty($Uri['port']) ? $Uri['port'] : '';}

    return self::unparse_url($RelativeUri);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Get parsed_url from parse_url('Url') and return full Url
   *
   * @param Array $parsed_url
   *
   * @return string
   */
  public static function unparse_url($parsed_url)
  {
    $scheme   = !empty($parsed_url['scheme']) ? "{$parsed_url['scheme']}://" : '';
    $host     = !empty($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = !empty($parsed_url['port']) ? ":{$parsed_url['port']}" : '';
    $user     = !empty($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = !empty($parsed_url['pass']) ? ":{$parsed_url['pass']}" : '';
    $pass     = ($user || $pass) ? "{$pass}@" : '';
    $path     = !empty($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = !empty($parsed_url['query']) ? "?{$parsed_url['query']}" : '';
    $fragment = !empty($parsed_url['fragment']) ? "#{$parsed_url['fragment']}" : '';

    return "$scheme$user$pass$host$port$path$query$fragment";
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Normalize path to format with slashes only
   *
   * @param string $path
   *
   * @return string
   */
  public static function normalize_path($path)
  {
    if (empty($path))
    {
      return '';
    }
    $normalized_path = $path;
    $normalized_path = preg_replace('`//+`', '/', $normalized_path, -1, $c0);
    $normalized_path = preg_replace('`^/\\.\\.?/`', '/', $normalized_path, -1, $c1);
    $normalized_path = preg_replace('`/\\.(/|$)`', '/', $normalized_path, -1, $c2);
    $normalized_path = preg_replace('`/[^/]*?/\\.\\.(/|$)`', '/', $normalized_path, 1, $c3);
    $num_matches     = $c0 + $c1 + $c2 + $c3;

    return ($num_matches>0) ? self::normalize_path($normalized_path) : $normalized_path;
  }
}

//----------------------------------------------------------------------------------------------------------------------
