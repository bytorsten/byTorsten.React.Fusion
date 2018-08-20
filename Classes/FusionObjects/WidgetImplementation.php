<?php
namespace byTorsten\React\Fusion\FusionObjects;

use byTorsten\React\Core\Service\FilePathResolver;
use byTorsten\React\Fusion\Domain\Model\AppContext;
use byTorsten\React\Fusion\Domain\Model\Widget;

class WidgetImplementation extends AbstractReactRenderable
{
    /**
     * @var string
     */
    protected $defaultServerScript = 'resource://byTorsten.React.Fusion/Private/React/src/component.server.js';

    /**
     * @var string
     */
    protected $defaultClientScript = 'resource://byTorsten.React.Fusion/Private/React/src/component.js';

    /**
     * @return string
     */
    protected function getComponentPath(): string
    {
        $filePathResolver = new FilePathResolver();
        return $filePathResolver->resolveFilePath($this->fusionValue('component'));
    }

    /**
     * @return array
     */
    protected function getProps(): array
    {
        return $this->fusionValue('props');
    }

    /**
     * @return AppContext|null
     */
    protected function getAppContext(): ?AppContext
    {
        return $this->runtime->getCurrentContext()[AppContext::APP_CONTEXT] ?? null;
    }

    /**
     * @return string
     */
    protected function renderWidget(): string
    {
        $componentPath = $this->getComponentPath();
        $identifier = md5('standalone-widget' . $this->getCodeSplitting()->toIdentifier() . $componentPath);
        $stateKey = 'widget-' . $identifier;
        $containerId = 'container-' . $identifier;

        $view = $this->createView($identifier, $this->getServerScript(), $this->getClientScript());

        $view->addAlias('@fusion/component', $componentPath);
        $view->addHypotheticalFile('@fusion/meta', implode(PHP_EOL, [
            sprintf('export const stateKey = \'%s\';', $stateKey),
            sprintf('export const containerId = \'%s\';', $containerId)
        ]));

        $view->assignMultiple($this->getProps());

        ['content' => $content, 'state' => $state ] = $view->render();

        $contentTag = sprintf('<div id="%s">%s</div>', $containerId, $content);

        if ($this->shouldRenderScripts() === false) {
            return $contentTag;
        }

        return implode(PHP_EOL, [
            $contentTag,
            $this->buildStateTag($stateKey, $state),
            $this->buildScriptTag($identifier)
        ]);
    }

    /**
     * @return mixed|string
     */
    public function evaluate()
    {
        $appContext = $this->getAppContext();

        if ($appContext === null) {
            return $this->renderWidget();
        }

        $componentPath = $this->getComponentPath();
        $identifier = md5('widget' . $componentPath);
        $containerId = 'container-' . $identifier;

        $widget = new Widget($identifier, $componentPath, $this->getProps());
        $appContext->registerWidget($widget);

        return sprintf('<div id="%s"><!--REACT_MARKER_%s--></div>', $containerId, $identifier);
    }
}
