<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program (see LICENSE.txt in the base directory.  If
 * not, see:.
 *
 * @link      <http://www.gnu.org/licenses/>.
 * @author    niel
 * @copyright 2017 nZEDb
 */

namespace nntmux\build;

use Composer\Script\Event;
use Illuminate\Foundation\Application;

class ComposerScripts
{
    public static function postInstallCmd()
    {
        $last = $output = $return = null;
        if ((int) getenv('COMPOSER_DEV_MODE') === 1) {
            echo 'Updating git hooks... ';
            $last = exec('build/git-hooks/addHooks.sh', $output, $return);
            if ($return > 0) {
                echo PHP_EOL;
                exit($last);
            }
            echo 'done'.PHP_EOL;
        }
    }

    /**
     * Handle the post-install Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postInstall(Event $event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

        static::clearCompiled();
    }

    /**
     * Handle the post-update Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postUpdate(Event $event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

        static::clearCompiled();
    }

    /**
     * Clear the cached Laravel bootstrapping files.
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $nntmux = new Application(getcwd());

        if (file_exists($servicesPath = $nntmux->getCachedServicesPath())) {
            @unlink($servicesPath);
        }
    }
}
