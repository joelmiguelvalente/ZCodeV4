<?php
declare(strict_types=1);

namespace App\Plugins\Services;

use App\Helpers\Appearance;
use App\Plugins\Services\UserPermission;
use App\Repository\AvatarRepository;
use App\Services\ImageService;
use App\Utils\Avatar;

class FrontendPayloadBuilder
{
	private array $data = [];
	private array $scope;
	private ?array $profileData = null;
	private UserPermission $permission;

	public function __construct(array $scope)
	{
		$this->scope = $scope;
		$this->permission = new UserPermission($scope);
	}

	private function add(string $key, $value): void
	{
		if ($value !== null) {
			$this->data[$key] = $value;
		}
	}

	public function addUser(): self
	{
		$tsUser = $this->scope['tsUser'] ?? null;

		if ($tsUser && $this->permission->isLogged()) {
			$this->add('user_key', (int) $tsUser->uid);
		}
		return $this;
	}

	public function addPost(): self
	{
		$tsPost  = $this->scope['tsPost'] ?? [];
		$tsPages = $this->scope['tsPages'] ?? [];

		$this->add('postid', $tsPost['post_id'] ?? null);
		$this->add('autor',  $tsPages['autor'] ?? null);

		return $this;
	}

	public function addFoto(): self
	{
		$foto = $this->scope['tsFoto']['foto'] ?? [];
		$this->add('fotoid', $foto['foto_id'] ?? null);
		return $this;
	}

	public function addConfig(): self
	{
		$cfg = $this->scope['tsConfig'] ?? [];

		foreach (['url', 'titulo', 'slogan', 'version'] as $k) {
			$this->add($k, $cfg[$k] ?? null);
		}
		return $this;
	}

	public function addPage(): self
	{
		$tsPage = $this->scope['tsPage'] ?? null;

		if ($tsPage === 'access' && ($_GET['action'] ?? null) === 'registro') {
			$this->add('pkey', $this->scope['tsConfig']['pkey'] ?? null);
		}

		if ($tsPage === 'admin' || $tsPage === 'moderacion') {
			$this->add('ajax', $this->permission->getRoute('url', 'dashboard'));
		}

		return $this;
	}

	public function addRoutes(): self
	{
		$this->data['images'] = [
			'assets' => $this->permission->getRoute('assets:images'),
			'tema'   => $this->permission->getRoute('tema:images')
		];

		$this->data['domain'] = $this->permission->getRoute('domain');
		$this->data['tema']   = $this->permission->getRoute('tema:base');
		$this->data['assets'] = $this->permission->getRoute('assets:base');

		return $this;
	}

	public function get(): array
	{
		ksort($this->data);
		return $this->data;
	}

	/**
    * Si el usuario existe y la página es 'cuenta', devuelve el bloque DOMContentLoaded
    * que seteaba avatar.uid y avatar.current, usando tus clases Avatar/ImageService/AvatarRepository.
    *
    * Devuelve cadena vacía si no corresponde.
    */
   private function buildAvatarScriptIfNeeded(): string {
      $tsUser = $this->scope['tsUser'] ?? null;
      $tsPage = $this->scope['tsPage'] ?? null;

      if (! $tsUser || (($this->permission->userId() ?? 0) === 0)) {
         return '';
      }
      if ($tsPage !== 'cuenta') {
         return '';
      }
      // Usamos las mismas clases que en tu código original. Ajustá namespaces si es necesario.
      try {
         $uid = $this->permission->userId();

         // instanciamos Avatar y dependencias como en tu ejemplo
         $Avatar = new Avatar(new ImageService(), new AvatarRepository());
         $Avatar->routes = $this->scope['Core']->route() ?? ($this->scope['Core'] ?? null);
         $avatarCurrent = $Avatar->loadAvatar($uid);

         // portada, si la tuvieras en el scope (lo dejé como antes, pero ahora se toma de scope)
         $portada = $this->scope['tsPerfil']['user_portada'] ?? null;
         $portadaJs = $portada ? "\n\tavatar.cover = '{$this->escapeJs($portada)}';" : '';

         $avatarCurrentEsc = $this->escapeJs((string)$avatarCurrent);
         // construimos el bloque JS (sin <script>, lo agregamos arriba)
         $script = <<<JS
				document.addEventListener("DOMContentLoaded", function() {
				avatar.uid = {$uid};
				avatar.current = '{$avatarCurrentEsc}';{$portadaJs}
			});
			JS;
      	return $script;
   	} catch (\Throwable $e) {
         // En caso de error al instanciar Avatar u otros, devolvemos string vacío para que no rompa la página.
         // Podés loguearlo si tenés un logger global.
         return '';
       }
   }

   private function escapeJs(string $s): string {
      $s = str_replace(["\\", "'"], ["\\\\", "\\'"], $s);
      $s = str_replace(["\r", "\n"], ['\r', '\n'], $s);
      return $s;
   }

   public function setScriptInLine(): string {
  		$isNots = (int)$this->scope['tsNots'];
  		$isMps = (int)$this->scope['tsMPs'];
  		$isNews = $this->scope['tsNews'];
  		$line = [];
  		
		$line[] = "notifica.popup($isNots);";
		$line[] = "\tmensaje.popup($isMps);";
		if(count($isNews) > 0 && $this->scope['tsPage'] !== 'admin') {
			$line[] = "\tnews.total = $('#top_news .news--item').length;";
			$line[] = "\tnews.slider();";
		}

  		$newLine = implode("\n", $line);
  		$lineNews = (count($isNews) > 0 && $this->scope['tsPage'] !== 'admin') ? "import { news } from \"{$this->permission->getRoute('url')}/assets/js/core/noticia.js\";" : "";
		$script = <<<JS
		<script type="module">
			import { $ } from '{$this->permission->getRoute('url')}/assets/js/app/zcode.app.js';
			import { notifica } from "{$this->permission->getRoute('url')}/assets/js/core/notifica.js";
			import { mensaje } from "{$this->permission->getRoute('url')}/assets/js/core/mensaje.js";
			$lineNews
			document.addEventListener("DOMContentLoaded", function() {
				$newLine
			});
		</script>
		JS;
	
		return trim($script);
  	}

	/**
    * Genera el JS final (string) con el objeto y, si corresponde, el script del avatar.
    *
    * @param string $dataParam el contenido de datos.php / string con formato "colores;themes" o similares
    * @return string JS final (lista para imprimir dentro de <script>...</script>)
    */
   public function toJavascript(string $dataParam = ''): string {
      // $dataParam: formato "colores;themes" (lo que usabas con explode(';', $data))
      $quitar = explode(';', $dataParam . ';'); // siempre al menos 2 elementos
      $base = $this->get();
      $base['permissions'] = $this->permission->isAdmod();
      // Añadimos colores y themes según lo que NO se pida quitar.
      // Si $quitar[0] === 'colores'  -> NO añadimos colores (como tu lógica original)
      if (!isset($quitar[0]) || $quitar[0] !== 'colores') {
         $base['colores'] = Appearance::getColors('base');
      }
      if (!isset($quitar[1]) || $quitar[1] !== 'themes') {
         $base['themes'] = Appearance::getSchemes();
      }

  		if(isset($this->scope['tsMuro']['total'])) {
			$base['muro']['stream']['total'] = $this->scope['tsMuro']['total'];
  		}
     
      // Usamos json_encode para evitar concatenaciones manuales.
      $json = json_encode($base, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
      // Si por alguna razón json_encode falla, volvemos a un fallback simple.
      if ($json === false) {
         $json = json_encode($base);
      }
      $jsObject = "const ZCodeApp = {$json};";
      // Posible script adicional para avatar (si corresponde)
      $avatarScript = $this->buildAvatarScriptIfNeeded();
      // Armamos el bloque <script>. Si querés sólo devolver JS sin tag, cambiar aquí.
      $script = "<script>\n{$jsObject}\n";
      if ($avatarScript !== '') {
         $script .= "\n{$avatarScript}\n";
      }
      $script .= "</script>";
      return $script;
   }

}