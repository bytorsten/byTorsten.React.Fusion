<?php
namespace byTorsten\React\Fusion\FusionObjects;

use byTorsten\React\Core\View\ReactView;
use byTorsten\React\Fusion\Domain\Model\CodeSplitting;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class ScriptImplementation extends AbstractFusionObject
{
    /**
     * @return CodeSplitting
     */
    protected function getCodeSplitting(): CodeSplitting
    {
        return $this->fusionValue('codeSplitting');
    }

    /**
     * @return mixed|string
     */
    public function evaluate()
    {
        $codeSplitting = $this->getCodeSplitting();
        $identifier = md5('header-script-' . $codeSplitting->toIdentifier());
        $view = new ReactView([
            'reactServerFilePattern' => 'resource://byTorsten.React.Fusion/Private/React/src/script.server.js',
            'reactClientFilePattern' => 'bundle.js',
            'identifier' => $identifier
        ]);

        $view->client()->showBundleNotification(false);

        $packages = $codeSplitting->getPackages();
        if (count($packages) > 0) {
            $view->setBaseDirectory($codeSplitting->getBaseDirectory());
            $view->assign('externalPackages', true);

            $imports = [];
            $exports = [];

            foreach ($packages as $index => $includedPackage) {
                $imports[] = sprintf('import pkg%s from \'%s\';', $index, $includedPackage);
                $exports[] = sprintf('window.__PACKAGES__[\'%s\'] = pkg%s', $includedPackage, $index);
            }


            $code = implode(
                PHP_EOL,
                array_merge(
                    $imports,
                    [
                        'window.__PACKAGES__ = window.__PACKAGES__ || {};'
                    ],
                    $exports
                )
            );

            $view->addHypotheticalFile('bundle.js', $code);
            $view->addAdditionalDependency(__FILE__);
        } else {
            $view->assign('externalPackages', false);
        }

        $view->setControllerContext($this->runtime->getControllerContext());

        return $view->render();
    }
}
