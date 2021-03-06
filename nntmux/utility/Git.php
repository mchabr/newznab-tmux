<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program (see LICENSE.txt in the base directory.  If
 * not, see:
 *
 * @link <http://www.gnu.org/licenses/>.
 * @author niel
 * @copyright 2014 nZEDb
 */

namespace nntmux\utility;

/**
 * Class Git - Wrapper for various git operations.
 */
class Git extends \GitRepo
{
    private $branch;
    private $mainBranches = ['dev', '0.5.x', '0.x'];

    public function __construct(array $options = [])
    {
        $defaults = [
            'create'        => false,
            'initialise'    => false,
            'filepath'        => NN_ROOT,
        ];
        $options += $defaults;

        parent::__construct($options['filepath'], $options['create'], $options['initialise']);
        $this->branch = parent::active_branch();
    }

    /**
     * Return the number of commits made to repo.
     */
    public function commits()
    {
        $count = 0;
        $log = explode("\n", $this->log());
        foreach ($log as $line) {
            if (preg_match('#^commit#', $line)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param string $options
     *
     * @return string
     */
    public function describe($options = null)
    {
        return $this->run("describe $options");
    }

    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param $gitObject
     *
     * @return bool
     * @throws \Exception
     */
    public function isCommited($gitObject)
    {
        $cmd = "cat-file -e $gitObject";

        try {
            $result = $this->run($cmd);
        } catch (\Exception $e) {
            $message = explode("\n", $e->getMessage());
            if ($message[0] === "fatal: Not a valid object name $gitObject") {
                $result = false;
            } else {
                throw new \Exception($message);
            }
        }

        return $result === '';
    }

    public function log($options = null)
    {
        return $this->run("log $options");
    }

    public function mainBranches()
    {
        return $this->mainBranches;
    }

    /**
     * @param string $options
     *
     * @return string
     */
    public function tag($options = null)
    {
        return $this->run("tag $options");
    }

    public function tagLatest()
    {
        return $this->describe('--tags --abbrev=0 HEAD');
    }
}
