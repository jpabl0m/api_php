<?php
/**
 * Probador de la generación de Token para la página de POST
 * Pasos:
 *   1 - Generar el token
 *   2 - Invocar al formulario de POST con ese token y los otros parámetros necesarios,
 *       puede invocarse al formulario intermedio de tests/inicio.php para completar
 *       los otros parámetros desde una interfaz más cómoda.
 */
define ("SANDBOX", 1); // colocar otro valor para apuntar a producción

function post($url, $fields){
  $post_field_string = http_build_query($fields, '', '&');
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field_string);
  curl_setopt($ch, CURLOPT_POST, true);
  $response = curl_exec($ch);

  // control de error HTTP
  if ($response === FALSE) {
    echo 'fallo<br>'.$url.'<br>';
    die(curl_error($ch));
  }

  curl_close($ch);
  return $response;
}

function post_conredireccion($url, $fields){
  $s_fields = "";
  foreach ($fields as $field_key => $field_value){
    $s_fields .= "<input type='hidden' name='".$field_key."' value='".$field_value."' />";
  }
  exit("<html>
              <body>
                <form name='f' method='post' action='".$url."'>".
    $s_fields."</form>
              <script>document.forms.f.submit();</script>  
              </body>
            </html>");
}

if (SANDBOX){
  $url = 'http://sandbox.epagos.com.ar/post.php';
} else {  
  $url = 'http://api.epagos.com.ar/post.php';
  //TODO: reemplazar con las credenciales asignadas
  $id_organismo = 0;
  $id_usuario   = 0;
  $password     = '';
  $hash         = '';

  //TODO: reemplazar con los suyos
  $ok_url       = 'http://postsandbox.epagos.com.ar/tests/ok.php';
  $error_url    = 'http://postsandbox.epagos.com.ar/tests/error.php';

} else {
  exit("");
}

$output = post($url, array(
  'id_usuario'   => $id_usuario,
  'id_organismo' => $id_organismo,
  'password'     => $password,
  'hash'         => $hash
));

$respuesta = json_decode($output);
// control de error en la respuesta
if (empty($respuesta->token)){
  echo 'fallo<br>'.$url.'<pre>';
  print_r($respuesta);
  echo '</pre>';
  exit;
}

$token = $respuesta->token;

if (SANDBOX)
  $url = 'http://post.epagos.com.ar';
else
  $url = "http://postsandbox.epagos.com.ar";

//TODO: personalizar con sus valores y detalles a enviar
$detalle_op = urlencode(json_encode(array(array('id_item'=>'1','desc_item'=>'DescripCion','monto_item'=>'120','cantidad_item'=>'1'))));
$datos_post = array(
  'version'             => '1.0',
  'operacion'           => 'op_pago',
  'id_organismo'        => $id_organismo,
  'token'               => $token,
  'numero_operacion'    => '1',
  'id_moneda_operacion' => '1',
  'monto_operacion'     => '120',
  'detalle_operacion'   => $detalle_op,
  'ok_url'              => $ok_url,
  'error_url'           => $error_url,
);

post_conredireccion($url, $datos_post);
