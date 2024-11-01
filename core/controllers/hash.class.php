<?php
namespace O10n;

/**
 * Hash Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Hash extends Controller implements Controller_Interface
{
    private $seed_url = 2910; // MurMurHash3 seed for URL

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
    }

    /**
     * MurMurHash3 method
     *
     * @param  string $key  String to hash
     * @param  int    $seed Seed for hash
     * @return int    Hash integer
     */
    final private function murmurhash3_int($key, $seed = 0)
    {
        $key = array_values(unpack('C*', (string) $key));
        $klen = count($key);
        $h1 = (int)$seed;
        for ($i = 0, $bytes = $klen - ($remainder = $klen & 3) ; $i < $bytes ;) {
            $k1 = $key[$i] | ($key[++$i] << 8) | ($key[++$i] << 16) | ($key[++$i] << 24);
            ++$i;
            $k1 = (((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16))) & 0xffffffff;
            $k1 = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
            $k1 = (((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16))) & 0xffffffff;
            $h1 ^= $k1;
            $h1 = $h1 << 13 | ($h1 >= 0 ? $h1 >> 19 : (($h1 & 0x7fffffff) >> 19) | 0x1000);
            $h1b = (((($h1 & 0xffff) * 5) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 5) & 0xffff) << 16))) & 0xffffffff;
            $h1 = ((($h1b & 0xffff) + 0x6b64) + ((((($h1b >= 0 ? $h1b >> 16 : (($h1b & 0x7fffffff) >> 16) | 0x8000)) + 0xe654) & 0xffff) << 16));
        }
        $k1 = 0;
        switch ($remainder) {
            case 3: $k1 ^= $key[$i + 2] << 16;
            // no break
            case 2: $k1 ^= $key[$i + 1] << 8;
            // no break
            case 1: $k1 ^= $key[$i];
                $k1 = ((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16)) & 0xffffffff;
                $k1 = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
                $k1 = ((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16)) & 0xffffffff;
                $h1 ^= $k1;
        }
        $h1 ^= $klen;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);
        $h1 = ((($h1 & 0xffff) * 0x85ebca6b) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
        $h1 ^= ($h1 >= 0 ? $h1 >> 13 : (($h1 & 0x7fffffff) >> 13) | 0x40000);
        $h1 = (((($h1 & 0xffff) * 0xc2b2ae35) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);

        return $h1;
    }

    /**
     * Return base10 from integer
     *
     * @param  int    $int Integer to convert
     * @return string Base 10 string
     */
    final public function base10($int, $return = false)
    {
        // base 10 convert
        $base10 = (string)base_convert($int, 10, 32);

        // mimic 32 char MD5 hash
        if ($return === 32) {
            $base10 = str_pad($base10, 32, '~');
        }

        return $base10;
    }

    /**
     * Return short URL hash
     *
     * @param  string  $URL      URL to hash
     * @param  boolean $return32 Return 32 length string to mimic MD5 hash
     * @return string  Base 10 of hash
     */
    final public function url($URL, $return = false)
    {
        // create hash
        $hash = $this->murmurhash3_int($URL, $this->seed_url);

        // integer hash
        if ($return === 'int') {
            return $hash;
        }

        // base 10 hash
        $hash = $this->base10($hash, ($return === 32) ? 32 : false);

        return $hash;
    }
}
