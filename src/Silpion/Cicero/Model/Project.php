<?php

namespace Silpion\Cicero\Model;

class Project
{
    private $cwd;

    private $dir;

    private $buildPath;

    private $config;

    private $isFailed = false;

    public function __construct($dir, $buildPath, array $config)
    {
        $this->cwd = getcwd();
        $this->dir = $dir;
        $this->buildPath = $buildPath;
        $this->config = $config;
    }

    /**
     * @param string $cwd
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;
    }

    /**
     * @return string
     */
    public function getCwd()
    {
        return $this->cwd;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function getBuildPath()
    {
        return $this->cwd . '/' . $this->buildPath;
    }

    public function getComposerConfig()
    {
        return $this->config['composer'];
    }

    public function getPhpConfig()
    {
        return $this->config['php'];
    }

    public function setFailed($flag = true)
    {
        $this->isFailed = $flag;
    }

    public function isFailed()
    {
        return $this->isFailed;
    }
}