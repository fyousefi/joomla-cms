<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;

return new class () implements InstallerScriptInterface {

    private string $minimumJoomla = '4.4.0';
    private string $minimumPhp    = '7.4.0';

    /** Relative path inside the module package */
    private const LAYOUT_REL = 'layouts/mod_spotlight/subform/numbered-table.php';

    public function install(InstallerAdapter $adapter): bool
    {
        echo "mod_spotlight install<br>";
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {

        echo "mod_spotlight update<br>";
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
		$this->removeAdminLayoutOverride();
        echo "mod_spotlight uninstall<br>";
        return true;
    }

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        //echo "mod_spotlight preflight<br>";

        if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp), 'error');
            return false;
        }

        if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla), 'error');
            return false;
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        // Safety net: run after install/update
        $this->syncAdminLayoutOverride($adapter);
        return true;
    }

    /** Copy numbered-table.php into the active admin template override */
    private function syncAdminLayoutOverride(InstallerAdapter $adapter): void
    {
        $parent  = $adapter->getParent();

        // Module base path (site module)
        $srcBase = $parent->getPath('extension_site') ?: $parent->getPath('extension_root');
        if (!$srcBase) {
            return;
        }

        $src = rtrim($srcBase, '/\\') . '/' . self::LAYOUT_REL;
        if (!File::exists($src)) {
            return; // layout not shipped
        }

        // Active admin template (usually 'atum')
        $adminApp      = Factory::getApplication();
        $adminTemplate = $adminApp->getTemplate(true)->template;

        $destDir = JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/layouts/mod_spotlight/subform';
        $dest    = $destDir . '/numbered-table.php';

        if (!Folder::exists($destDir)) {
            Folder::create($destDir);
        }

        // Copy if missing or source is newer
        if (!File::exists($dest) || @filemtime($src) > @filemtime($dest)) {
            File::copy($src, $dest);
        }
    }

	/** Remove the override from all admin templates and tidy empty dirs */
	private function removeAdminLayoutOverride(): void
	{
		$templatesRoot = JPATH_ADMINISTRATOR . '/templates';
		if (!\Joomla\CMS\Filesystem\Folder::exists($templatesRoot)) {
			return;
		}

		// Loop all admin templates (e.g., atum, atum-child, etc.)
		$templates = \Joomla\CMS\Filesystem\Folder::folders($templatesRoot);
		foreach ($templates as $tmpl) {
			$subformDir = $templatesRoot . '/' . $tmpl . '/html/layouts/mod_spotlight/subform';
			$file       = $subformDir . '/numbered-table.php';

			if (\Joomla\CMS\Filesystem\File::exists($file)) {
				\Joomla\CMS\Filesystem\File::delete($file);
			}

			// Prune empty folders: .../subform → .../mod_spotlight → .../layouts
			$this->deleteDirIfEmpty($subformDir);
			$this->deleteDirIfEmpty(\dirname($subformDir));
			$this->deleteDirIfEmpty(\dirname(\dirname($subformDir)));
		}
	}

	private function deleteDirIfEmpty(string $dir): void
	{
		if (!\Joomla\CMS\Filesystem\Folder::exists($dir)) {
			return;
		}
		$files   = \Joomla\CMS\Filesystem\Folder::files($dir);
		$folders = \Joomla\CMS\Filesystem\Folder::folders($dir);
		if (empty($files) && empty($folders)) {
			\Joomla\CMS\Filesystem\Folder::delete($dir);
		}
	}
};
