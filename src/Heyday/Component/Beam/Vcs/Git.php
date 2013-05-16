<?php

namespace Heyday\Component\Beam\Vcs;

use Heyday\Component\Beam\Utils;
use Symfony\Component\Process\Process;

class Git implements VcsProvider
{
    /**
     * @var
     */
    protected $srcdir;
    /**
     * @param $srcdir
     */
    public function __construct($srcdir)
    {
        $this->srcdir = $srcdir;
    }
    /**
     * @{inheritDoc}
     */
    public function getCurrentBranch()
    {
        $process = $this->process('git rev-parse --abbrev-ref HEAD');

        return trim($process->getOutput());
    }
    /**
     * @{inheritDoc}
     */
    public function getAvailableBranches()
    {
        $process = $this->process('git branch -a');
        $matches = array();
        preg_match_all('/[^\n](?:[\s\*]*)([^\s]*)(?:.*)/', $process->getOutput(), $matches);

        return $matches[1];
    }
    /**
     * @{inheritDoc}
     */
    public function exists()
    {
        return file_exists($this->srcdir . DIRECTORY_SEPARATOR . '.git');
    }
    /**
     * @{inheritDoc}
     */
    public function exportBranch($branch, $location)
    {
        Utils::removeDirectory($location);

        mkdir($location, 0755);

        $this->process(
            sprintf(
                '(git archive %s) | (cd %s && tar -xf -)',
                $branch,
                $location
            )
        );
    }
    /**
     * @{inheritDoc}
     */
    public function updateBranch($branch)
    {
        $parts = $this->getRemoteName($branch);
        if (!$parts) {
            throw new \InvalidArgumentException('The git vcs provider can only update remotes');
        }
        $this->process(sprintf('git remote update --prune %s', $parts[0]));
    }
    /**
     * A helper method that returns a process with some defaults
     * @param $command
     * @throws \RuntimeException
     * @return Process
     */
    protected function process($command)
    {
        $process = new Process(
            $command,
            $this->srcdir
        );
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process;
    }
    /**
     * @param $branch
     * @return mixed
     */
    public function getLog($branch)
    {
        $process = $this->process(
            sprintf(
                'git log -1 --format=medium %s',
                $branch
            )
        );
        return sprintf(
            "Deployer: %s\nBranch: %s\n%s\n",
            get_current_user(),
            $branch,
            $process->getOutput()
        );
    }
    /**
     * @param $branch
     * @return bool
     */
    public function isRemote($branch)
    {
        return (bool) $this->getRemoteName($branch);
    }
    /**
     * @param $branch
     * @return array|bool
     */
    public function getRemoteName($branch)
    {
        $matches = array();
        if (1 === preg_match('{^remotes/(.+)/(.+)}', $branch, $matches)) {
            return array_slice($matches, 1);
        } else {
            return false;
        }
    }
}
