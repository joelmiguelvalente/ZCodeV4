<?php

namespace App\Extensiones;

use Smarty\Extension\Base;
use Smarty\Compile\Modifier\ModifierCompilerInterface;

class SmartyExtensiones extends Base
{
    private array $compilers = [];

    public function __construct()
    {
        $this->autoloadCompilers();
    }

    /**
     * Carga automáticamente todos los ModifierCompilers del directorio actual.
     */
    private function autoloadCompilers(): void
    {
        $dir = __DIR__;
        foreach (glob($dir . '/*ModifierCompiler.php') as $file) {
            require_once $file;
            $className = basename($file, '.php');     // p.ej. HumanModifierCompiler
            $fqcn = __NAMESPACE__ . '\\' . $className;
            if (class_exists($fqcn)) {
                // Derivar el nombre del modifier
                $modifier = preg_replace('/ModifierCompiler$/', '', $className);
                // Convertir primera letra a minúscula (opcional)
                $modifier = lcfirst($modifier);
                // Crear instancia del compilador
                $this->compilers[$modifier] = new $fqcn();
            }
        }
    }

    /**
     * Retorna el compilador automáticamente cargado.
     */
    public function getModifierCompiler(string $modifier): ?ModifierCompilerInterface
    {
        return $this->compilers[$modifier] ?? null;
    }
}
