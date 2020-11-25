<?php
declare(strict_types=1);

namespace Plaisio\Helper;

/**
 * Utility class with functions for manipulating URLs.
 */
class Url
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Combines two URIs to a single URL. In most cases the first URI will the an absolute URL and the second URI a
   * path and optionally a query.
   *
   * @param string $uri1 The first URI.
   * @param string $uri2 The second URI.
   *
   * @return string
   *
   * @since 1.0.0
   * @api
   */
  public static function combine(string $uri1, string $uri2): string
  {
    $parts2 = parse_url($uri2);
    if (isset($parts2['scheme']) || isset($parts2['host']))
    {
      // The second URI is an absolute URI. Take all parts from second URI.
      $combined_uri_parts = $parts2;

      // The scheme might by omitted. The default scheme is http.
      if (!isset($combined_uri_parts['scheme'])) $combined_uri_parts['scheme'] = 'http';
    }
    else
    {
      $parts1             = parse_url($uri1);
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
        unset($void);
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
          $combined_uri_parts['query'] = $parts1['query'] ?? null;
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
   * @param string $url The URL.
   *
   * @return bool
   *
   * @since 1.0.0
   * @api
   */
  public static function isRelative(string $url): bool
  {
    if ($url!=='')
    {
      return ((mb_substr($url, 0, 1)=='/' &&
          (mb_strlen($url)==1 || (mb_substr($url, 1, 1)!='/' && mb_substr($url, 1, 1)!='\\'))) ||
        (mb_strlen($url)>1 && mb_substr($url, 0, 2)=='~/'));
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Normalize path to format with slashes only.
   *
   * @param string|null $path The path.
   *
   * @return string
   *
   * @since 1.0.0
   * @api
   */
  public static function normalizePath(?string $path): string
  {
    // With thanks to monkeysuffrage, see https://github.com/monkeysuffrage/phpuri/blob/master/phpuri.php.

    if ($path===null || $path==='')
    {
      return '';
    }

    $normalized_path = $path;
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
   * @param string $html The HTML code.
   * @param string $root The part of the URLs before the path part without slash.
   *
   * @return string
   *
   * @since 1.0.0
   * @api
   */
  public static function relative2Absolute(string $html, string $root): string
  {
    return preg_replace("#(href|src)=(['\"])([^:'\"]*)(['\"]|(?:(?:%20|\\s|\\+)[^'\"]*))#",
                        '$1=$2'.$root.'$3$4',
                        $html);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns an URL based on the URL parts as returned by [parse_url](http://php.net/manual/function.parse-url.php).
   *
   * @param array       $parts         The URL parts.
   * @param string|null $defaultScheme The scheme to be used when scheme is not in $theParts.
   *
   * @return string
   *
   * @since 1.0.0
   * @api
   */
  public static function unParseUrl(array $parts, ?string $defaultScheme = null): string
  {
    if (!isset($parts['scheme']) && !isset($parts['host']) && isset($parts['path']))
    {
      $i = strpos($parts['path'], '/');
      if ($i===false)
      {
        $parts['host'] = $parts['path'];
        unset($parts['path']);
      }
      else
      {
        $parts['host'] = substr($parts['path'], 0, $i);
        $parts['path'] = substr($parts['path'], $i);
      }
    }

    if (isset($parts['scheme']))
    {
      // The scheme must be in lowercase.
      $parts['scheme'] = strtolower($parts['scheme']);
    }
    elseif (isset($defaultScheme))
    {
      $parts['scheme'] = strtolower($defaultScheme);
    }

    // We assume that all URLs must have a path except for 'mailto'.
    if (!isset($parts['path']) && isset($parts['scheme']) && $parts['scheme']!='mailto')
    {
      $parts['path'] = '/';
    }

    // Recompose the URL starting with the scheme.
    if (isset($parts['scheme']))
    {
      if ($parts['scheme']=='mailto')
      {
        $url = 'mailto:';
      }
      else
      {
        $url = $parts['scheme'];
        $url .= '://';
      }
    }
    else
    {
      $url = '';
    }

    if (isset($parts['pass']) && isset($parts['user']))
    {
      $url .= $parts['user'].':'.$parts['pass'].'@';
    }
    elseif (isset($parts['user']))
    {
      $url .= $parts['user'].'@';
    }

    if (isset($parts['host'])) $url .= $parts['host'];
    if (isset($parts['port'])) $url .= ':'.$parts['port'];
    if (isset($parts['path'])) $url .= $parts['path'];
    if (isset($parts['query'])) $url .= '?'.$parts['query'];
    if (isset($parts['fragment'])) $url .= '#'.$parts['fragment'];

    return $url;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
