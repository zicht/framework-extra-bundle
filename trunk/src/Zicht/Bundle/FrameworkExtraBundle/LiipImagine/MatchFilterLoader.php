<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\LiipImagine;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Imagine\Image\ImageInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Custom filter that applies sub-filters *only* when it matches certain criteria.
 *
 * For example:
 * liip_imagine:
 *   filter_sets:
 *     # GENERIC HOMEPAGE HEADER FILTER
 *     homepage_header:
 *       filters:
 *         match:
 *           ratio: 'landscape'
 *           filters: homepage_header__landscape
 *           fallback: homepage_header__portrait
 *
 *     # SPECIFIC FILTER FOR LANDSCAPE IMAGES
 *     homepage_header__landscape:
 *       filters:
 *          # WHATEVER FILTER APPLIES TO THE LANDSCAPE IMAGE
 *
 *     # SPECIFIC FILTER FOR PORTRAIT IMAGES
 *     homepage_header__portrait:
 *       filters:
 *          # WHATEVER FILTER APPLIES TO THE PORTRAIT IMAGE
 *
 * Ratio matching:
 * match: {ratio: square, filters: some_filter}
 * match: {ratio: landscape, filters: some_filter}
 * match: {ratio: portrait, filters: some_filter}
 * match: {ratio: {comparator: >, value: 1.0}, filters: some_filter}
 * match: {ratio: {comparator: <, value: 1.0}, filters: some_filter}
 * match: {ratio: {comparator: =, value: 1.0}, filters: some_filter}
 */
class MatchFilterLoader implements LoaderInterface
{
    /**
     * @var FilterManager
     */
    protected $filterManager;

    function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @{inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        if (array_key_exists('ratio', $options)) {
            if ($this->isRatioMatch($image, $options)) {
                if (array_key_exists('filters', $options)) {
                    $image = $this->applyFilters($image, $options['filters']);
                }
            } else {
                if (array_key_exists('fallback', $options)) {
                    $image = $this->applyFilters($image, $options['fallback']);
                }
            }
        }

        return $image;
    }

    /**
     * Returns true when the image ratio matches the configured parameters
     *
     * @param ImageInterface $image
     * @param $options
     * @return bool
     */
    private function isRatioMatch(ImageInterface $image, $options)
    {
        $size = $image->getSize();
        $ratio = $size->getWidth() / $size->getHeight();

        switch ($options['ratio']) {
            case 'landscape':
                $comparator = '>';
                $value = 1.0;
                break;
            case 'portrait':
                $comparator = '<';
                $value = 1.0;
                break;
            case 'square':
                $comparator = '=';
                $value = 1.0;
                break;
            default:
                $comparator = array_key_exists('comparator', $options['ratio']) ? $options['ratio']['comparator'] : '=';
                $value = array_key_exists('value', $options['ratio']) ? $options['ratio']['value'] : 1.0;
                break;
        }

        switch ($comparator) {
            case '=':
            case '==':
                return $ratio == $value;
            case '<':
                return $ratio < $value;
            case '<=':
                return $ratio <= $value;
            case '>':
                return $ratio > $value;
            case '>=':
                return $ratio >= $value;
            default:
                return false;
        }
    }

    /**
     * Applies one or more filters to the image
     *
     * @param ImageInterface $image
     * @param array|string $filters
     * @return ImageInterface
     */
    private function applyFilters(ImageInterface $image, $filters)
    {
        if (is_string($filters)) {
            $filters = [$filters];
        }
        foreach ($filters as $filter) {
            if (!is_string($filter)) {
                throw new InvalidConfigurationException('The MatchFilterLoader configuration is invalid.  The destination filter must be a string');
            }
            $image = $this->filterManager->applyFilter($image, $filter);
        }
        return $image;
    }
}
