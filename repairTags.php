<?php
/* re-pairs tags in given HTML
 *
 * MIT license
 *
 * spaceboy
 *
 * */

class repairTags {

  private static $tags = array();

  /* Finds the pair HTML tags (as "<b>", "</b>", "<i>", "</em>" etc, but not "<br />", "<hr/>" atc.)
   * and adds them to pool.
   */
  private static function fetchTags ($htmlInput) {
    preg_replace_callback('/<([^>]*)>/i', function ($match) {
      if (!preg_match('/\/$/', $match[1])) {
        self::$tags[] = $match[1];
      }
    }, $htmlInput);
  }

  /* Checks the founded opener tags for their closers.
   * Founded pairs are removed from pool.
   */
  private static function checkTags () {
    foreach (self::$tags as $index => $tag) {
      $found = array_search("/{$tag}", self::$tags);
      if (false === $found) {
        continue;
      }
      unset(self::$tags[$index]);
      unset(self::$tags[$found]);
    }
  }

  /* Completes the HTML:
   * adds missing pair tags to HTML (missing openers to the left, missing closers to the right).
   */
  private static function fixTags ($htmlInput) {
    $html = $htmlInput;
    foreach (self::$tags as $tag) {
      if (!preg_match('/^\//', $tag)) {
        $html = "{$html}</{$tag}>";
        continue;
      }
      $tag = preg_replace('/^\//', '', $tag);
      $html = "<{$tag}>{$html}";
    }
    return $html;
  }

  /* Internal function for re-pairing
   */
  private static function getRepaired ($htmlInput) {
    self::fetchTags($htmlInput);
    if (!sizeof(self::$tags)) {
      return $htmlInput;
    }
    self::checkTags ();
    if (!sizeof(self::$tags)) {
      return $htmlInput;
    }
    return self::fixTags($htmlInput);
  }

  /* re-pairs tags in given HTML ($shtmlInput)
   * Returns [string] re-paired HTML
   * */
  public static function rePair ($htmlInput) {
    self::$tags = array();
    return self::getRepaired($htmlInput);
  }

}

/* QUICK TEST:

echo "Re-pair tags test:\n";
foreach (array(
  '<b>foo</b>',
  '<b>bar<b>',
  '</b>bar</i>',
  'baz<br />',
  '<b>foo <i>bar</i></i><b> baz<br />',
) as $word) {
  echo "--\n{$word}: " . repairTags::rePair($word) . "\n";
}

*/