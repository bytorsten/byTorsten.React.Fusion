<?php
namespace byTorsten\React\Fusion\FusionObjects;

use Neos\Flow\Annotations as Flow;
use byTorsten\React\Core\Cache\FileManager;
use byTorsten\React\Core\View\ReactView;
use byTorsten\React\Fusion\Domain\Model\CodeSplitting;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

abstract class AbstractReactRenderable extends AbstractFusionObject
{
    /**
     * @Flow\Inject
     * @var FileManager
     */
    protected $fileManager;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string
     */
    protected $defaultServerScript;

    /**
     * @var string
     */
    protected $defaultClientScript;

    /**
     * @return CodeSplitting
     */
    protected function getCodeSplitting(): CodeSplitting
    {
        return $this->fusionValue('codeSplitting');
    }

    /**
     * @return bool
     */
    protected function shouldRenderScripts(): bool
    {
        return $this->fusionValue('renderScripts');
    }


    /**
     * @return string
     */
    protected function getServerScript(): string
    {
        return $this->fusionValue('serverScript') ?: $this->defaultServerScript;
    }

    /**
     * @return string
     */
    protected function getClientScript(): string
    {
        return $this->fusionValue('clientScript') ?: $this->defaultClientScript;
    }

    /**
     * @param string $key
     * @param array $state
     * @return string
     */
    protected function buildStateTag(string $key, array $state): string
    {
        return sprintf('<script>window[\'%s\'] = %s;</script>', $key, json_encode($state));
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function buildScriptTag(string $identifier): string
    {
        $uriBuilder = $this->runtime->getControllerContext()->getUriBuilder();
        $uri = $uriBuilder->uriFor('index', ['identifier' =>  $identifier, 'chunkname' => 'bundle.js'], 'Chunk', 'byTorsten.React');


        $scriptTag = sprintf('<script defer src="%s"></script>', $uri);
        if ($this->fileManager->hasClientCode($identifier) === false) {
            $src = $this->resourceManager->getPublicPackageResourceUri('byTorsten.React', 'bundleNotification.js');
            $scriptTag = $scriptTag . sprintf('<script src="%s?%s" data-identifier="%s"></script>', $src, $identifier, $identifier);
        }

        return $scriptTag;
    }

    /**
     * @param string $identifier
     * @param string $serverFile
     * @param null|string $clientFile
     * @return ReactView
     */
    protected function createView(string $identifier, string $serverFile, ?string $clientFile): ReactView
    {
        $view = new ReactView([
            'identifier' => $identifier,
            'serverFile' => $serverFile,
            'clientFile' => $clientFile
        ]);
        $view->addAdditionalDependency(__FILE__);
        $view->setControllerContext($this->runtime->getControllerContext());

        foreach ($this->getCodeSplitting()->getPackages() as $packageName) {
            $view->addExternal($packageName, sprintf('window.__PACKAGES__[\'%s\']', $packageName));
        }

        return $view;
    }
}
