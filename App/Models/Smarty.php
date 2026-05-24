<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
 */

declare(strict_types=1);

namespace App\Models;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use Smarty\Smarty as SmartyEngine;
use App\Extensiones\SmartyExtensiones;
//
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class Smarty extends SmartyEngine
{
    private string $theme;

    private string $page;

    public string $templateError = 't.error.tpl';

    public function __construct(string $theme = '', string $page = '')
    {
        parent::__construct();
        $this->theme = $theme;
        $this->page = $page;
        $this->cache();
        $this->extensions();
        $this->muteUndefinedOrNullWarnings();
    }

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
        $this->cache(TS_CACHE . '/' . $theme);
    }

    public function setPage(string $page): void
    {
        $this->page = $page;
    }

    public function cache()
    {
        $this->setCompileCheck(true);
        $this->setCompileDir(TS_CACHE . '/' . $this->theme . date('dmy'));
    }

    /**
     * Registra automáticamente plugins de Smarty.
     */
    private function extensions(): void
    {
        $this->addExtension(new SmartyExtensiones());
        $pluginDirs = [
            'function' => PLUGINS . '/function.*.php',
            'modifier' => PLUGINS . '/modifier.*.php'
        ];
        foreach ($pluginDirs as $type => $pattern) {
            foreach (glob($pattern) as $file) {
                require_once $file;
                $pluginName = explode('.', basename($file, '.php'))[1];
                $this->registerPlugin($type, $pluginName, "smarty_{$type}_{$pluginName}");
            }
        }
    }

    /**
     * Aplica filtros de salida opcionales.
     */
    public function output(bool $loadFilter = false): void
    {
        if ($loadFilter) {
            $this->loadFilter('output', 'trimwhitespace');
        }
    }

    private function resolvePage(string $page, bool $useExtension): string {
        $file = match ($page) {
            'registro', 'login'   => 'base.tpl',
            'admin', 'moderacion' => 'main.tpl',
            'suspension'          => 'views/output/suspension.tpl',
            'mantenimiento'       => 'views/output/mantenimiento.tpl',
            'saliendo'            => 'themes/html/saliendo.html',
            default => ($useExtension ? "t.$page.tpl" : "$page.tpl")
        };
        return $this->templateExists($file) ? $file : $this->templateError;
    }

    private function recursiveDirectories(string $path): array {
        $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        $iterators = [];
        foreach ($iterator as $item) {
           if ($item->isDir()) {
              $iterators[$item->getFilename()] = $item->getPathname();
           }
        }
        return $iterators;
    }

    /**
     * Mapea rutas del sistema y módulos.
     */
    private function mapDirectories(): array {
        $directories = [
            'root'       => BASEPATH,
            'auth'       => TS_AUTH,
            'api'        => TS_VIEWS . '/api',
            'error'      => TS_VIEWS . '/error',
            'output'     => TS_OUTPUT . '/',
            'dashboard'  => TS_ADMIN,
            'admin_mods' => TS_ADMIN . '/admin_mods',
        ];
        return $directories;
    }

    /**
     * Carga todos los directorios utilizados por el tema.
     */
    private function loadAllTemplates(): void {
        $theme = isset($_SESSION['theme_path']) ? $_SESSION['theme_path'] : $this->theme;
        $templates = TS_THEMES . "/{$theme}/templates";
        $map = array_merge(
            [
                'tema'        => TS_THEMES . "/{$theme}",
                'templates'   => $templates
            ],
            $this->recursiveDirectories($templates),
            $this->recursiveDirectories(TS_COMPONENTS),
            $this->mapDirectories()
         );
        $this->addTemplateDir($map);
    }

    /**
     * Renderiza una plantilla.
     */
    public function load(string $page = '', bool $useExtension = true): void
    {
        $this->loadAllTemplates();

        try {
            $template = $this->resolvePage($page, $useExtension);
            $this->display($template);
        } catch (Exception $e) {
            $mensaje = preg_replace_callback(
                "/'([^']+)'/",
                fn ($message) => "'<strong>{$message[1]}</strong>'",
                $e->getMessage()
            );

            $show = "
                Lo sentimos, se produjo un error al cargar la plantilla <strong>t.$page.tpl</strong>.
                <br>Debido al error:<br>
                <code style=\"font-size:1rem;line-height: 1.3rem;color: #d971ad;
                word-wrap: break-word;background: rgba(217, 113, 173, .12);
                display:block;padding:.5em;\">$mensaje</code>
            ";

            ShowError($show, 'plantilla');
        }
    }

    /**
     * Borra el archivo compilado asociado a una plantilla.
     */
    public function clearCompiled(string $template = ''): void
    {
        $this->clearCompiledTemplate($template);
    }
}
