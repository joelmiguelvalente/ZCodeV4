<?php

$year = date('Y');

return <<<EMAIL
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* Tipografía segura con fallback */
body, table, td, p, a, h1, h2, h3 {
   font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
   Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
}
</style>

</head>
<body style="margin:0;padding:0;background:#ffffff;">

<table role="presentation" style="width:100%;border-collapse:collapse;">
   <tr>
      <td style="padding:32px;">

         <!-- HEADER -->
         <table role="presentation" style="width:100%;border-collapse:collapse;">
            <tr>
               <td style="text-align:left;">
                  <h1 style="margin:0;font-size:24px;font-weight:700;color:#333;">{titulo}</h1>
                  <p style="margin:4px 0 0 0;font-size:14px;color:#7a7a7a;">{slogan}</p>
               </td>

               <td style="text-align:right;">
                  <img src="cid:logo_cid" alt="logo" style="width:48px;height:48px;border-radius:6px;">
               </td>
            </tr>
         </table>

         <!-- CONTENT BOX -->
         <table role="presentation" style="width:100%;border-collapse:collapse;margin-top:32px;">
            <tr>
               <td style="background:#fafafa;border:1px solid #e5e5e5;padding:32px;border-radius:8px;">
                  <h2 style="margin:0 0 16px 0;font-size:20px;font-weight:600;color:{color_header};">{asunto}</h2>
                  <div style="font-size:15px;color:#333;line-height:1.6;">{contenido}</div>
               </td>
            </tr>
         </table>

         <!-- FOOTER -->
         <table role="presentation" style="width:100%;margin-top:40px;text-align:center;color:#888;font-size:12px;">
            <tr>
               <td>
                  <p style="margin:0 0 8px 0;">El Staff de <strong>{titulo}</strong></p>
                  <p style="margin:0;">© 2024–{$year}</p>
               </td>
            </tr>
         </table>

      </td>
   </tr>
</table>

</body>
</html>
EMAIL;
