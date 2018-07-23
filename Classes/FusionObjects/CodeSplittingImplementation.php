<?php
namespace byTorsten\React\Fusion\FusionObjects;

use byTorsten\React\Fusion\Domain\Model\CodeSplitting;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class CodeSplittingImplementation extends AbstractFusionObject
{

    const PATTERN_MATCH_PACKAGE = '/\w+(?:\.\w+)+/i';

    /**
     * @return array
     */
    protected function getPackages(): array
    {
        return $this->fusionValue('packages');
    }

    /**
     * @return null|string
     */
    protected function getBaseDirectory(): ?string
    {
        $baseDirectory = $this->fusionValue('baseDirectory');
        if ($baseDirectory === null) {
            /** @var ActionRequest $request */
            $request = $this->runtime->getControllerContext()->getRequest();
            return sprintf('resource://%s/Private/React', $request->getControllerPackageKey());
        }

        if (preg_match(static::PATTERN_MATCH_PACKAGE, $baseDirectory) !== false) {
            return 'resource://' . $baseDirectory;
        }

        return $baseDirectory;
    }

    /**
     * @return CodeSplitting
     */
    public function evaluate()
    {
        $packages = $this->getPackages();
        $filteredPackages = array_filter(array_keys($packages), function (string $packageName) use ($packages) {
            return (bool) $packages[$packageName] === true;
        });

        return new CodeSplitting($filteredPackages, $this->getBaseDirectory());
    }
}
