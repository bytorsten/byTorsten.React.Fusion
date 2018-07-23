<?php
namespace byTorsten\React\Fusion\Domain\Model;

class Widget
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $componentPath;

    /**
     * @var array
     */
    protected $props;

    /**
     * @param string $identifier
     * @param string $componentPath
     * @param array $props
     */
    public function __construct(string $identifier, string $componentPath, array $props = [])
    {
        $this->identifier = $identifier;
        $this->componentPath = $componentPath;
        $this->props = $props;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getComponentPath(): string
    {
        return $this->componentPath;
    }

    /**
     * @return array
     */
    public function getProps(): array
    {
        return $this->props;
    }
}
