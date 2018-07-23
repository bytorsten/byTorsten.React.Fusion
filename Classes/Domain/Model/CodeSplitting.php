<?php
namespace byTorsten\React\Fusion\Domain\Model;

class CodeSplitting
{
    /**
     * @var array
     */
    protected $packages;

    /**
     * @var string
     */
    protected $baseDirectory;

    /**
     * @param array $packages
     * @param string $baseDirectory
     */
    public function __construct(array $packages, string $baseDirectory)
    {
        $this->packages = $packages;
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @return string
     */
    public function getBaseDirectory(): string
    {
        return $this->baseDirectory;
    }

    /**
     * @return string
     */
    public function toIdentifier(): string
    {
        return implode($this->packages) . $this->baseDirectory;
    }
}
