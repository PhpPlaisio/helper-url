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
   * @param string $theUri1 The part of the URLs before the path part without slash.
   * @param string $theUr2  The path part of the URLs.
   *
   * @return string
   */
  public static function combine($theUri1, $theUr2)
  {
    $uri2_parts = parse_url($theUr2);
    if (isset($uri2_parts['scheme']) || isset($uri2_parts['host']))
    {
      // The second URI is an absolute URI. Take all parts from second URI.
      $combined_uri_parts = $uri2_parts;

      // The scheme might by omitted. The default scheme is http.
      if (!isset($combined_uri_parts['scheme'])) $combined_uri_parts['scheme'] = 'http';
    }
    else
    {
      $uri1_parts         = parse_url($theUri1);
      $combined_uri_parts = array_merge($uri1_parts, $uri2_parts);

      // Handle spacial cases for the path part of the URI.
      if (!isset($uri2_parts['path']))
      {
        // Checking path in $uri2_parts and if path is empty, getting path from $_uri using [normalize_path]
        $combined_uri_parts['path'] = self::normalizePath($uri1_parts['path']);
      }
      elseif (strpos($uri2_parts['path'], '/')===0)
      {
        // Checking path in $uri2_parts and if path have '/', do nothing.
      }
      else
      {
        // Else create path using $_uri['path'] and $uri2_parts['path'].With using [normalize_path].
        $_path = $uri1_parts['path'];
        if (strpos($_path, '/')===false)
        {
          $_path = '';
        }
        else
        {
          $_path = preg_replace('/\/[^\/]+$/', '/', $_path);
        }
        if (!isset($_path) && !isset($uri1_parts['host']))
        {
          $_path = '/';
        }
        $combined_uri_parts['path'] = self::normalizePath($_path.$uri2_parts['path']);
      }

      // Handle spacial cases for the query part of the URI.
      if (!isset($uri2_parts['path']))
      {
        if (!isset($uri2_parts['query']))
        {
          $combined_uri_parts['query'] = $uri1_parts['query'];
        }
      }
      elseif (strpos($uri2_parts['path'], '/')===0)
      {
        if (isset($uri2_parts['query']))
        {
          $combined_uri_parts['query'] = $uri2_parts['query'];
        }
        else
        {
          $combined_uri_parts['query'] = null;
        }
      }
      else
      {
        if (isset($uri2_parts['query']))
        {
          $combined_uri_parts['query'] = $uri2_parts['query'];
        }
        else
        {
          $combined_uri_parts['query'] = null;
        }
      }
    }

    return self::unparseUrl($combined_uri_parts);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Creates a complete Url from parts of $theParsedUrl
   *
   * @param array $theParsedUrl
   *
   * @return string
   */
  private static function unparseUrl($theParsedUrl)
  {
    $scheme   = isset($theParsedUrl['scheme']) ? $theParsedUrl['scheme'] . '://' : '';
    $host     = isset($theParsedUrl['host']) ? $theParsedUrl['host'] : '';
    $port     = isset($theParsedUrl['port']) ? ':' .$theParsedUrl['port'] : '';
    $user     = isset($theParsedUrl['user']) ? $theParsedUrl['user'] : '';
    $pass     = isset($theParsedUrl['pass']) ? ':' .$theParsedUrl['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($theParsedUrl['path']) ? $theParsedUrl['path'] : '';
    $query    = isset($theParsedUrl['query']) ? '?' .$theParsedUrl['query'] : '';
    $fragment = isset($theParsedUrl['fragment']) ? '#' .$theParsedUrl['fragment'] : '';

    return "$scheme$user$pass$host$port$path$query$fragment";
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Normalize path to format with slashes only
   *
   * @param string $thePath
   *
   * @return string
   */
  public static function normalizePath($thePath)
  {
    if (empty($thePath))
    {
      return '';
    }
    $normalized_path = $thePath;
    $normalized_path = preg_replace('`//+`', '/', $normalized_path, -1, $c0);
    $normalized_path = preg_replace('`^/\\.\\.?/`', '/', $normalized_path, -1, $c1);
    $normalized_path = preg_replace('`/\\.(/|$)`', '/', $normalized_path, -1, $c2);
    $normalized_path = preg_replace('`/[^/]*?/\\.\\.(/|$)`', '/', $normalized_path, 1, $c3);
    $_num_matches     = $c0 + $c1 + $c2 + $c3;

    return ($_num_matches>0) ? self::normalizePath($normalized_path) : $normalized_path;
  }
}

//----------------------------------------------------------------------------------------------------------------------
