<?php

namespace Silpion\Cicero\Model;

/**
 * Object for a Project.
 *
 * Contains all information to run cicero.
 *
 * @author Julius Beckmann <beckmann@silpion.de>
 */
class Project
{
    /**
     * Current working dir.
     *
     * @var string
     */
    private $cwd;

    /**
     * Path of the project, where the .cicero.yml file is.
     *
     * @var string
     */
    private $dir;

    /**
     * Path to be used as a target for build output by tools.
     *
     * @var string
     */
    private $buildPath;

    /**
     * Parsed configuration values.
     *
     * @var array
     */
    private $config;

    /**
     * Flag, if the project has failed to process.
     *
     * @var bool
     */
    private $isFailed = false;

    /**
     * @param $dir  string Path of the project, where the .cicero.yml file is.
     * @param $buildPath    string Path to be used as a target for build output by tools.
     * @param array $config array Parsed configuration values.
     */
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
        return $this->dir . '/' . $this->buildPath;
    }

    /**
     * Returns the configuration part for composer.
     *
     * @return array
     */
    public function getComposerConfig()
    {
        return $this->config['composer'];
    }

    /**
     * Returns the configuration part for php.
     *
     * @return array
     */
    public function getPhpConfig()
    {
        return $this->config['php'];
    }

    public function setFailed($flag = true)
    {
        $this->isFailed = (boolean)$flag;
    }

    public function isFailed()
    {
        return $this->isFailed;
    }
}