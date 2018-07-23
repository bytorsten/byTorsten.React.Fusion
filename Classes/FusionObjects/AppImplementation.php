<?php
namespace byTorsten\React\Fusion\FusionObjects;

use byTorsten\React\Core\View\ReactView;
use byTorsten\React\Fusion\Domain\Model\AppContext;
use byTorsten\React\Fusion\Domain\Model\Widget;

class AppImplementation extends AbstractReactRenderable
{
    /**
     * @var string
     */
    protected $defaultServerScript = 'resource://byTorsten.React.Fusion/Private/React/src/app.server.js';

    /**
     * @var string
     */
    protected $defaultClientScript = 'resource://byTorsten.React.Fusion/Private/React/src/app.js';

    /**
     * @param Widget[] $widgets
     * @return string
     */
    protected function buildIdentifier(array $widgets): string
    {
        $widgetIdentifiers = array_map(function (Widget $widget) {
            $widget->getIdentifier();
        }, $widgets);

        return md5('app' . $this->getCodeSplitting()->toIdentifier() . implode($widgetIdentifiers) . $this->getServerScript() . $this->getClientScript());
    }

    /**
     * @param string $content
     * @param array $markups
     * @return string
     */
    protected function replaceMarker(string $content, array $markups): string
    {
        foreach ($markups as $identifier => $markup) {
            $content = str_replace('<!--REACT_MARKER_' . $identifier . '-->', $markup, $content);
        }

        return $content;
    }

    /**
     * @param array $renderResult
     * @return string
     */
    protected function renderAdditionalContent(array $renderResult): string
    {
        $this->runtime->pushContextArray($renderResult);
        $content = $this->fusionValue('additionalContent');
        $this->runtime->popContext();

        return $content ?: '';
    }

    /**
     * @param ReactView $view
     * @param Widget[] $widgets
     * @return string
     */
    protected function buildComponentsCode(ReactView $view, array $widgets): string
    {
        $imports = [];
        $exports = [];

        foreach ($widgets as $widget) {
            $identifier = $widget->getIdentifier();
            $packageAlias = 'pkg_' . $widget->getIdentifier();
            $view->addAlias($packageAlias, $widget->getComponentPath());
            $imports[] = sprintf('import %s from \'%s\';', $packageAlias, $packageAlias);
            $exports[] = sprintf('{ identifier: \'%s\', component: %s }', $identifier, $packageAlias);
        }

        return implode(PHP_EOL, array_merge(
            $imports,
            [sprintf('export default [%s];', implode(',', $exports))]
        ));
    }

    /**
     * @return mixed|string
     */
    public function evaluate()
    {
        $appContext = new AppContext();

        $this->runtime->pushContext(AppContext::APP_CONTEXT, $appContext);
        $content = $this->fusionValue('content');
        $this->runtime->popContext();

        $widgets = $appContext->getWidgets();
        $identifier = $this->buildIdentifier($widgets);
        $stateKey = 'widget-' . $identifier;

        $view = $this->createView($identifier, $this->getServerScript(), $this->getClientScript());
        $view->addHypotheticalFile('@fusion/components', $this->buildComponentsCode($view, $widgets));
        $view->addHypotheticalFile('@fusion/meta', sprintf('export const stateKey = \'%s\';', $stateKey));

        $props = [];
        foreach ($widgets as $widget) {
            $props[$widget->getIdentifier()] = $widget->getProps();
        }

        $view->assign('__props', $props);

        $renderResult = $view->render();
        $content = $this->replaceMarker($content, $renderResult['markups']) . PHP_EOL . $this->renderAdditionalContent($renderResult);

        if ($this->shouldRenderScripts() === false) {
            return $content;
        }

        return implode(PHP_EOL, [
            $content,
            $this->buildStateTag($stateKey, $renderResult['state']),
            $this->buildScriptTag($identifier, $view)
        ]);
    }
}
