<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 06.12.2016
 * Time: 09:41
 */
declare(strict_types=1);

namespace Alpipego\AWP\Assets;

/**
 * Class AssetsCollection
 * @package Alpipego\AWP\Assets
 *
 * @method add(AssetInterface $asset): ?AssetInterface
 * @method remove(AssetInterface $asset): ?AssetInterface
 * @method inline(AssetInterface $asset): ?AssetInterface
 * @method update(AssetInterface $asset): ?AssetInterface
 */
class AssetsCollection implements AssetsCollectionInterface, AssetsResolverInterface
{
    use AssetsCollectionTrait;
    use AssetsResolverTrait;

    private $assets = [];
    private $type;

    public function __construct($type = 'wp')
    {
        $this->type = $type;
    }

    public function run()
    {
        array_walk($this->assets, function (array $assets, string $group) {
            $classname = __NAMESPACE__ . '\\' . $group . 's';
            if (class_exists($classname)) {
                (new $classname($assets, $this->type))->run();
            }
        });
    }

    public function __call(string $name, Asset $asset): ?AssetInterface
    {
        $asset->action = $name;
        $type          = $this->getType($asset);

        return $this->assets[$type][$asset->handle] = $asset;
    }

    private function getType(Asset $asset)
    {
        $class = explode('\\', get_class($asset));

        return end($class);
    }

    public function get(string $handle, string $group = 'script'): ?Asset
    {
        $group = ucfirst($group);

        if ($this->arrayKeyExistsRecursive($this->assets, $group, $handle)) {
            return $this->assets[$group][$handle];
        }

        return null;
    }

    private function arrayKeyExistsRecursive(array $array, string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $array)) {
                return false;
            }
            $array = $array[$key];
        }

        return true;
    }
}
