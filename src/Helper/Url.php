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
   * Combines two URIs to a single URL. In most cases the first URI will the an absolute URL and the second URI a
   * path and optionally a query.
   *
   * @param string $theUri1 The first URI.
   * @param string $theUri2 The second URI.
   *
   * @return string
   */
  public static function combine($theUri1, $theUri2)
  {
    $parts2 = parse_url($theUri2);
    if (!is_array($parts2))
    {
      print_r("Parts2 not array");
    }
    if (isset($parts2['scheme']) || isset($parts2['host']))
    {
      // The second URI is an absolute URI. Take all parts from second URI.
      $combined_uri_parts = $parts2;

      // The scheme might by omitted. The default scheme is http.
      if (!isset($combined_uri_parts['scheme'])) $combined_uri_parts['scheme'] = 'http';
    }
    else
    {
      $parts1 = parse_url($theUri1);
      $combined_uri_parts = array_merge($parts1, $parts2);

      // Handle spacial cases for the path part of the URI.
      if (!isset($parts2['path']))
      {
        // Checking path in $uri2_parts and if path is empty, getting path from $_uri using [normalize_path]
        $combined_uri_parts['path'] = self::normalizePath($parts1['path']);
      }
      elseif (strpos($parts2['path'], '/')===0)
      {
        // Checking path in $uri2_parts and if path have '/', do nothing.
      }
      else
      {
        // Else create path using $_uri['path'] and $uri2_parts['path']. With using [normalize_path].
        $_path = $parts1['path'];
        if (strpos($_path, '/')===false)
        {
          $_path = '';
        }
        else
        {
          $_path = preg_replace('/\/[^\/]+$/', '/', $_path);
        }
        if (!isset($_path) && !isset($parts1['host']))
        {
          $_path = '/';
        }
        $combined_uri_parts['path'] = self::normalizePath($_path.$parts2['path']);
      }

      // Handle spacial cases for the query part of the URI.
      if (!isset($parts2['path']))
      {
        if (!isset($parts2['query']))
        {
          $combined_uri_parts['query'] = $parts1['query'];
        }
      }
      elseif (strpos($parts2['path'], '/')===0)
      {
        if (isset($parts2['query']))
        {
          $combined_uri_parts['query'] = $parts2['query'];
        }
        else
        {
          $combined_uri_parts['query'] = null;
        }
      }
      else
      {
        if (isset($parts2['query']))
        {
          $combined_uri_parts['query'] = $parts2['query'];
        }
        else
        {
          $combined_uri_parts['query'] = null;
        }
      }
    }

    return self::unParseUrl($combined_uri_parts);
  }

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
   * Normalize path to format with slashes only.
   *
   * @param string $thePath
   *
   * @return string
   */
  public static function normalizePath($thePath)
  {
    // With thanks to monkeysuffrage, see https://github.com/monkeysuffrage/phpuri/blob/master/phpuri.php.

    if (empty($thePath))
    {
      return '';
    }

    $normalized_path = $thePath;
    $normalized_path = preg_replace('`//+`', '/', $normalized_path, -1, $c0);
    $normalized_path = preg_replace('`^/\\.\\.?/`', '/', $normalized_path, -1, $c1);
    $normalized_path = preg_replace('`/\\.(/|$)`', '/', $normalized_path, -1, $c2);
    $normalized_path = preg_replace('`/[^/]*?/\\.\\.(/|$)`', '/', $normalized_path, 1, $c3);
    $_num_matches    = $c0 + $c1 + $c2 + $c3;

    return ($_num_matches>0) ? self::normalizePath($normalized_path) : $normalized_path;
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
                        '$1=$2'.$theRoot.'$3$4',
                        $theHtml);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns an URL based on the URL parts as returned by [parse_url](http://php.net/manual/function.parse-url.php).
   *
   * @param array       $theParts         The URL parts.
   * @param string|null $theDefaultScheme The scheme to be used when scheme is not in $theParts.
   *
   * @return string
   */
  public static function unParseUrl($theParts, $theDefaultScheme = null)
  {
    if (!isset($theParts['scheme']) && !isset($theParts['host']) && isset($theParts['path']))
    {
      $i = strpos($theParts['path'], '/');
      if ($i===false)
      {
        $theParts['host'] = $theParts['path'];
        unset($theParts['path']);
      }
      else
      {
        $theParts['host'] = substr($theParts['path'], 0, $i);
        $theParts['path'] = substr($theParts['path'], $i);
      }
    }

    if (isset($theParts['scheme']))
    {
      // The scheme must be in lowercase.
      $theParts['scheme'] = strtolower($theParts['scheme']);
    }
    elseif (isset($theDefaultScheme))
    {
      $theParts['scheme'] = strtolower($theDefaultScheme);
    }

    // We assume that all URLs must have a path except for 'mailto'.
    if (!isset($theParts['path']) && isset($theParts['scheme']) && $theParts['scheme']!='mailto')
    {
      $theParts['path'] = '/';
    }

    // Recompose the URL starting with the scheme.
    if (isset($theParts['scheme']))
    {
      if ($theParts['scheme']=='mailto')
      {
        $url = 'mailto:';
      }
      else
      {
        $url = $theParts['scheme'];
        $url .= '://';
      }
    }
    else
    {
      $url = '';
    }

    if (isset($theParts['pass']) && isset($theParts['user']))
    {
      $url .= $theParts['user'].':'.$theParts['pass'].'@';
    }
    elseif (isset($theParts['user']))
    {
      $url .= $theParts['user'].'@';
    }

    if (isset($theParts['host'])) $url .= $theParts['host'];
    if (isset($theParts['port'])) $url .= ':'.$theParts['port'];
    if (isset($theParts['path'])) $url .= $theParts['path'];
    if (isset($theParts['query'])) $url .= '?'.$theParts['query'];
    if (isset($theParts['fragment'])) $url .= '#'.$theParts['fragment'];

    return $url;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
