<?php
namespace byTorsten\React\Fusion\FusionObjects;

use byTorsten\React\Core\View\ReactView;
use byTorsten\React\Fusion\Domain\Model\CodeSplitting;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

abstract class AbstractReactRenderable extends AbstractFusionObject
{

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
     * @param ReactView $view
     * @return string
     */
    protected function buildScriptTag(string $identifier, ReactView $view): string
    {

        $uriBuilder = $this->runtime->getControllerContext()->getUriBuilder();
        $uri = $uriBuilder->uriFor('index', ['identifier' =>  $identifier, 'chunkname' => $view->getScriptName()], 'Chunk', 'byTorsten.React');

        return sprintf('<script defer src="%s"></script>', $uri);
    }

    /**
     * @param string $identifier
     * @param string $serverFilePattern
     * @param null|string $clientFilePattern
     * @return ReactView
     */
    protected function createView(string $identifier, string $serverFilePattern, ?string $clientFilePattern): ReactView
    {
        $view = new ReactView([
            'identifier' => $identifier,
            'reactServerFilePattern' => $serverFilePattern,
            'reactClientFilePattern' => $clientFilePattern
        ]);
        $view->addAdditionalDependency(__FILE__);
        $view->setControllerContext($this->runtime->getControllerContext());

        $client = $view->client();

        foreach ($this->getCodeSplitting()->getPackages() as $packageName) {
            $client->addHypotheticalFile($packageName, sprintf('
                const pkg = window.__PACKAGES__[\'%s\'];
                module.exports = pkg;
            ', $packageName));
        }

        return $view;
    }
}
