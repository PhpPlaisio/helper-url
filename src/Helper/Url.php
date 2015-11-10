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
    $_uri         = parse_url($theUri);
    $_relativeUri = parse_url($theRelativeUri);

    if (!empty($_relativeUri['scheme']) || !empty($_relativeUri['host']))
    {
      /**
       * Checking scheme or host in $_relativeUri and if one of they not empty, do nothing.
       */
    }
    elseif (empty($_relativeUri['path']))
    {
      /**
       * Checking path in $_relativeUri and if path is empty, getting path from $_uri using [normalize_path]
       */
      $_relativeUri['path'] = self::normalize_path($_uri['path']);
      if (empty($_relativeUri['query']))
      {
        $_relativeUri['query'] = $_uri['query'];
      }
    }
    elseif (strpos($_relativeUri['path'], '/')===0)
    {
      /**
       * Checking path in $_relativeUri and if path have '/', do nothing.
       */
    }
    else
    {
      /**
       * Else create path using $_uri['path'] and $_relativeUri['path'].
       * With using [normalize_path].
       */
      $_path = $_uri['path'];
      if (strpos($_path, '/')===false)
      {
        $_path = '';
      }
      else
      {
        $_path = preg_replace('/\/[^\/]+$/', '/', $_path);
      }
      if (empty($_path) && empty($_uri['host']))
      {
        $_path = '/';
      }
      $_relativeUri['path'] = self::normalize_path($_path.$_relativeUri['path']);
    }
    if (empty($_relativeUri['scheme']))
    {
      /**
       * Checking scheme and host in $_relativeUri and if they are empty, get from $_uri.
       */
      $_relativeUri['scheme'] = $_uri['scheme'];
      if (empty($_relativeUri['host']))
      {
        $_relativeUri['host'] = $_uri['host'];
      }
    }
    /**
     * Checking user,pass and port in $_relativeUri and if they are empty, get from $_uri.
     */
    if (empty($_relativeUri['user']) || $_relativeUri['user']=='0' || $_relativeUri['user']=='0.0')
    {
      $_relativeUri['user'] = !empty($_uri['user']) && $_uri['user']!='0' && $_uri['user']!='0.0' ? $_uri['user'] : '';
    }
    if (empty($_relativeUri['pass']) || $_relativeUri['pass']=='0' || $_relativeUri['pass']=='0.0')
    {
      $_relativeUri['pass'] = !empty($_uri['pass']) && $_uri['pass']!='0' && $_uri['pass']!='0.0' ? $_uri['pass'] : '';
    }
    if (empty($_relativeUri['port']) || $_relativeUri['port']=='0' || $_relativeUri['port']=='0.0')
    {
      $_relativeUri['port'] = !empty($_uri['port']) && $_uri['port']!='0' && $_uri['port']!='0.0' ? $_uri['port'] : '';
    }

    return self::unparse_url($_relativeUri);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Get parsed_url from [parse_url] and return full Url
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
    $_normalized_path = $path;
    $_normalized_path = preg_replace('`//+`', '/', $_normalized_path, -1, $c0);
    $_normalized_path = preg_replace('`^/\\.\\.?/`', '/', $_normalized_path, -1, $c1);
    $_normalized_path = preg_replace('`/\\.(/|$)`', '/', $_normalized_path, -1, $c2);
    $_normalized_path = preg_replace('`/[^/]*?/\\.\\.(/|$)`', '/', $_normalized_path, 1, $c3);
    $_num_matches     = $c0 + $c1 + $c2 + $c3;

    return ($_num_matches>0) ? self::normalize_path($_normalized_path) : $_normalized_path;
  }
}

//----------------------------------------------------------------------------------------------------------------------
