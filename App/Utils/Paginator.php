<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Utils;

use App\Traits\Url;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Paginator
{
    use Url;

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = $this->url();
    }

    # Establece el limite de paginas y el inicio para la paginacion. (quitar luego)
    public function setPageLimit(int $tsLimit = 0, bool $start = false, int $tsMax = 0)
    {
        // Inicializar el inicio de la paginaci�n
        $tsStart = 0;
        // Establecer el inicio de la paginaci�n si es necesario
        if ($start !== false) {
            $tsStart = isset($_GET['s']) ? (int) $_GET['s'] : 0;
            // Establecer el inicio en 0 si se excede el l�mite m�ximo
            if ($this->setMaximos($tsLimit, $tsMax)) {
                $tsStart = 0;
            }
        } else {
            // Calcular el inicio basado en el n�mero de p�gina
            $pageNumber = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
            $tsStart = ($pageNumber - 1) * $tsLimit;
        }
        // Retornar el inicio y el l�mite de resultados
        return "$tsStart,$tsLimit";
    }

    # Verifica si se excede el limite maximo de paginas. (quitar luego)
    public function setMaximos(int $tsLimit = 0, int $tsMax = 0)
    {
        // MAXIMOS || PARA NO EXEDER EL NUMERO DE PAGINAS
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
        $ban1 = ($page * $tsLimit);
        if ($tsMax < $ban1) {
            $ban2 = $ban1 - $tsLimit;
            if ($tsMax < $ban2) {
                return true;
            }
        }
        //
        return false;
    }

    # Genera informacion sobre la paginacion de un conjunto de resultados. (quitar luego)
    public function getPages(int $tsTotal = 0, int $tsLimit = 0)
    {
        // Verificar si el l�mite es v�lido
        if ($tsLimit <= 0) {
            return []; // Devolver un array vac�o si el l�mite es cero o negativo
        }
        // Calcular el n�mero total de p�ginas
        $tsPages = ceil($tsTotal / $tsLimit);
        // Obtener el n�mero de p�gina actual
        $tsPage = isset($_GET['page']) ? max(1, min($_GET['page'], $tsPages)) : 1;
        // Verificar si el n�mero de p�gina actual excede el total de p�ginas
        if ($tsPage > $tsPages) {
            $tsPage = $tsPages;
        }
        // Construir el array de informaci�n de paginaci�n
        $pages = [
            'current' => $tsPage,
            'pages' => $tsPages,
            'section' => $tsPages + 1,
            'prev' => max(1, $tsPage - 1),
            'next' => min($tsPages, $tsPage + 1),
            'max' => $this->setMaximos($tsLimit, $tsTotal)
        ];
        // Retornar la informaci�n de paginaci�n
        return $pages;
    }

    # Obtenemos paginacion (quitar luego)
    public function getPagination(int $total = 0, int $per_page = 10)
    {
        // PAGINA ACTUAL
        $page = empty($_GET['page']) ? 1 : (int) $_GET['page'];
        // NUMERO DE PAGINAS
        $num_pages = ceil($total / $per_page);
        // ANTERIOR
        $prev = $page - 1;
        $pages['prev'] = ($page > 0) ? $prev : 0;
        // SIGUIENTE
        $next = $page + 1;
        $pages['next'] = ($next <= $num_pages) ? $next : 0;
        // LIMITE DB
        $pages['limit'] = (($page - 1) * $per_page) . ',' . $per_page;
        // TOTAL
        $pages['total'] = $total;
        //
        return $pages;
    }

    # Creamos el html del paginador (quitar luego)
    public function pageIndex(string $base_url = '', int $max_value = 0, int $num_per_page = 0, bool $flexible_start = false)
    {
        // Remove the 's' parameter from the base URL
        $base_url = $this->baseUrl . $base_url;
        $base_url = preg_replace('/[?&]s=\d*/', '', $base_url);
        // Ensure $start is a non-negative integer and a multiple of $num_per_page
        $start = max(0, (isset($_GET['s']) ? (int)$_GET['s'] : 0));
        $start -= $start % $num_per_page;
        $morepages = '<div class="page-item off"><span class="page-numbers">...</span></div>';
        // Initialize the page index string
        $pageindex = '';
        $pageindex .= '<nav class="pagination">';
        // Generate the link format based on whether flexible_start is enabled or not
        $flexstart = $base_url . ($flexible_start ? '' : '&s=%d');
        $base_link = "<div class=\"page-item\"><a class=\"page-numbers\" href=\"$flexstart\">%s</a></div> ";
        // Calculate the number of contiguous page links to show
        $PageContiguous = 2;
        // Helper function to generate page links
        $generatePageLink = function ($pageNumber) use ($base_link, $num_per_page) {
            return sprintf($base_link, $pageNumber * $num_per_page, $pageNumber + 1);
        };
        // Add the link to the first page if necessary
        if ($start > $num_per_page * $PageContiguous) {
              $pageindex .= $generatePageLink(0) . ' ';
        }
        // Add '...' before the first page link if necessary
        if ($start > $num_per_page * ($PageContiguous + 1)) {
              $pageindex .= $morepages;
        }
        // Add page links before the current page
        for ($i = $PageContiguous; $i >= 1; $i--) {
              $pageNumber = $start / $num_per_page - $i;
            if ($pageNumber >= 0) {
                  $pageindex .= $generatePageLink($pageNumber);
            }
        }
        // Add the link to the current page
        $pageindex .= '<div class="page-item"><span aria-current="page" class="page-numbers current">' . ($start / $num_per_page + 1) . '</span></div> ';
        // Add page links after the current page
        for ($i = 1; $i <= $PageContiguous; $i++) {
              $pageNumber = $start / $num_per_page + $i;
              // Ensure the link is within the valid page range
            if ($pageNumber * $num_per_page < $max_value) {
                  $pageindex .= $generatePageLink($pageNumber);
            }
        }
        // Add '...' near the end if necessary
        if ($start + $num_per_page * ($PageContiguous + 1) < $max_value - $num_per_page) {
              $pageindex .= $morepages;
        }
        // Add the link to the last page if necessary
        if ($start + $num_per_page * $PageContiguous < $max_value - $num_per_page) {
              $pageNumber = (int) (($max_value - 1) / $num_per_page);
              $pageindex .= $generatePageLink($pageNumber);
        }
        $pageindex .= '</nav>';
        return $pageindex;
    }

    /**
     * Sistema de paginación automática [2023]
     * @author Miguel92
     * basados completamente en estos mods de ellos
     * @author mdulises
     * @author KMario
     * @author ReModWrite
    */
    public function systemPagination(int $totalItems = 0, int $itemsPerPage = 0, string $inPage = '')
    {
        // Obtenemos la pagina actual
        $currentPage = !isset($_GET['page']) ? 1 : (int)$_GET['page'];
        // Si no existe devolvemos algo vacío
        if ($totalItems <= 0) {
            return 0;
        }
        $page = (empty($inPage) ? '' : $inPage) . "?page=";
        $pagination['current'] = $currentPage;
        // Empezamos con la estructura de la paginación
        $pagination['item'] = '<nav class="pagination">';
        // Calculamos el total de páginas necesarias.
        $totalPages = ceil($totalItems / $itemsPerPage);
        // Limitamos el valor de $currentPage para asegurarnos de que no se exceda el rango.
        $currentPage = max(1, min($currentPage, $totalPages));
        // Enlace a página anterior.
        if ($currentPage > 1) {
            $pagination['item'] .= "<div class=\"page-item\"><a class=\"prev page-numbers\" href=\"{$this->baseUrl}/$page" . ($currentPage - 1) . "\" title=\"P&aacute;gina anterior\">&laquo;</a></div>";
        }
        // Enlaces de primera y última página.
        if ($currentPage > 3) {
            $pagination['item'] .= "<div class=\"page-item\"><a class=\"page-numbers\" href=\"{$this->baseUrl}/$page1\">1</a></div>";
            if ($currentPage > 6) {
                $pagination['item'] .= "<div class=\"page-item off\"><span class=\"page-numbers\">...</span></div>";
            }
        }
        // Mostramos los enlaces de la paginación.
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        //
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($currentPage === $i) {
                $pagination['item'] .= "<div class=\"page-item\"><span aria-current=\"page\" class=\"page-numbers current\">{$i}</span></div>";
            } else {
                $pagination['item'] .= "<div class=\"page-item\"><a class=\"page-numbers\" href=\"{$this->baseUrl}/$page{$i}\">{$i}</a></div>";
            }
        }
        // Enlaces después del número 6.
        if ($currentPage < $totalPages - 4) {
            $pagination['item'] .= "<div class=\"page-item off\"><span class=\"page-numbers\">...</span></div>";
            $pagination['item'] .= "<div class=\"page-item\"><a class=\"page-numbers\" href=\"{$this->baseUrl}/$page{$totalPages}\">{$totalPages}</a></div>";
        }
        // Enlace a página siguiente.
        if ($currentPage < $totalPages) {
            $pagination['item'] .= "<div class=\"page-item\"><a class=\"next page-numbers\" href=\"{$this->baseUrl}/$page" . ($currentPage + 1) . "\" title=\"P&aacute;gina siguiente\">&raquo;</a></div>";
        }
        // Finalizamos la paginación
        $pagination['item'] .= '</nav>';
        return $pagination;
    }
}
