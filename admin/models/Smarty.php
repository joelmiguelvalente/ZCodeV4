<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
 */

declare(strict_types=1);

namespace Admin\models;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use Smarty\Smarty as SmartyEngine;
use App\extensiones\SmartyExtensiones;
use Exception;

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
        $this->setCompileDir(TS_CACHE . $theme);
    }

    public function setPage(string $page): void
    {
        $this->page = $page;
    }

    public function cache()
    {
        $this->setCompileCheck(true);
        $this->setCompileDir(TS_CACHE . '/admin-' . date('dmy'));
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

    /**
     * Resuelve la plantilla según la página.
     */
    private function resolvePage(string $page): string
    {
        $file = match ($page) {
            'main' => 'main.tpl',
            'saliendo' => 'views/html/saliendo.html',
            default => "t.$page.tpl"
        };
        return $this->templateExists($file) ? $file : $this->templateError;
    }

    /**
     * Mapea rutas del sistema y módulos.
     */
    private function mapDirectories(): array
    {
        $directories = [
            'root'       => BASEPATH,
            'assets'     => TS_ASSETS,
            'components' => BASEPATH . 'views/components/',
            'html'       => BASEPATH . 'views/html/',
            'api'        => BASEPATH . 'views/api/',
            'dashboard'  => TS_ADMIN . '/templates/',
            'admin'          => TS_ADMIN . '/templates/admin/',
            'mod'        => TS_ADMIN . '/templates/moderacion/',
            'cache'      => TS_CACHE
        ];
        foreach (scandir($directories['components']) as $component) {
            if ($component === '.' || $component === '..') {
                continue;
            }
            $directories[$component] = $directories['components'] . $component . '/';
        }
        return $directories;
    }

    /**
     * Renderiza una plantilla.
     */
    public function load(string $page = ''): void
    {
        $this->addTemplateDir($this->mapDirectories());
        try {
            $template = $this->resolvePage($page);
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
