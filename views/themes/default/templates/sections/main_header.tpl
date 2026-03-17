<!DOCTYPE html>
<html lang="es" {$Theme->getSettingsTheme()}>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$tsTitle}</title>
{metatag seo=["facebook","twitter"] analytics=true robots=[
	"name" => "robots",
	"content" => "index, follow"
]}
{$Theme->preloadFont()}
{stylesheets files=["fonts.css","base.css","theme.css"]}
{lines vars="global"}
{scripts files=["acciones.js","dropdown.js"]}
{lines vars="aditional"}
</head>
<body>
	
	<div class="UIBeeper" id="BeeperBox"></div>

	<div id="pagebox-one" class="{if $tsThemeBox}no-{/if}container">
		<main id="brandday" class="{if !$tsThemeBox}my-3 rounded{/if}">
			{include "head_header.tpl"}
			<section id="pagebox-two" class="container{if $tsThemeBox}-fluid{/if} py-3 px-3">
				{include "head_noticias.tpl"}
				<a name="cielo"></a>