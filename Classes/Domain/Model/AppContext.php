<?php
namespace byTorsten\React\Fusion\Domain\Model;

class AppContext
{
    const APP_CONTEXT = '__reactAppContext';

    /**
     * @var Widget[]
     */
    protected $widgets = [];

    /**
     * @param Widget $widget
     */
    public function registerWidget(Widget $widget): void
    {
        $this->widgets[] = $widget;
    }

    /**
     * @return Widget[]
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }
}
