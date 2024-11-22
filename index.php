<?php
    require_once("config/validaciones_seguridad_raiz.php");
    require_once("config/conexion_db.php");
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(isset($_SESSION["usu_id"]) AND $_SESSION['usu_inicio_sesion']!=0){
        header("Location:contenido.php");
    } else {
        if(isset($_POST["form_login"])){
            //obtiene variable de captcha
            $captcha_validacion = validar_input($_POST['captcha_validacion']);
            $captcha_original = validar_input($_COOKIE['captcha']);
            //obtiene variable usuario y contraseña
            $usuario_acceso=validar_input($_POST['usuario_acceso']);
            $contrasena=validar_input($_POST['contrasena']);
            
            //valida el captcha correcto
            if ($captcha_original == sha1($captcha_validacion)) {
                $consulta_string = "SELECT `usu_id`, `usu_acceso`, `usu_contrasena`, `usu_nombres_apellidos`, `usu_correo_corporativo`, `usu_sede`, `usu_estado`, `usu_inicio_sesion`, `usu_area` FROM `tb_configuracion_usuario` WHERE `usu_acceso`= ?";
            	
            	$consulta_registros = $enlace_db->prepare($consulta_string);
			    $consulta_registros->bind_param("s", $usuario_acceso);
			    $consulta_registros->execute();
			    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

                if (count($resultado_registros)>0) {
                	if($usuario_acceso==$resultado_registros[0][1] AND $resultado_registros[0][6]=='Activo' AND crypt($contrasena, $resultado_registros[0][2]) == $resultado_registros[0][2]){
                        $_SESSION["usu_id"]=$resultado_registros[0][0];
                        $_SESSION["usu_acceso"]=$resultado_registros[0][1];
                        $_SESSION["usu_nombre_completo"]=$resultado_registros[0][3];
                        $_SESSION["usu_area"]=$resultado_registros[0][8];
                        $_SESSION["usu_sede"]=$resultado_registros[0][5];
                        $_SESSION["usu_estado_usuario"]=$resultado_registros[0][6];
                        $_SESSION["usu_inicio_sesion"]=$resultado_registros[0][7];

                        registro_log($enlace_db, 'Login', 'inicio_sesion', 'Inicio de sesión Intranet');
               
                        if ($resultado_registros[0][7]==0) {
                            header("Location: config_seguridad.php");
                        } else {
                            header("Location: contenido.php");
                        }
                    } else {
                        $respuesta_accion = "<p class='alert alert-danger'>Inicio de sesión fallido, verifique e intente nuevamente!</p>";
                    }
	            } else {
                    $respuesta_accion = "<p class='alert alert-danger'>Inicio de sesión fallido, verifique e intente nuevamente!</p>";
                }
            } else {
                $respuesta_accion = "<p class='alert alert-danger'>Inicio de sesión fallido, verifique e intente nuevamente!</p>";
            }
        }

        $alphabeth ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_-";
	    $codigo = "";
	    for($i=0;$i<5;$i++){
	        $codigo .= $alphabeth[rand(0,strlen($alphabeth)-1)];
	    }

?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="x-ua-compatible" content="ie-edge">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/login.css?v=2">
	<link href="fonts/css/all.css" rel="stylesheet">
	<!-- favicon link-->
    <link rel="shortcut icon" type="image/icon" href="images/favicon.ico?v=2"/>
	<title>Intranet | Pozo de Donato S.A.S.</title>
</head>
<body>
	<meta http-equiv='refresh' content='120; URL=https://www.garpermedica.com/intranet'/>
	<div class="container-fluid">
		<div class="login-form">
			<div class="form-header">
				<div class="row">
					<div class="col-6">
						<img src="images/logo_pozo.png" class="img-fluid">
					</div>
					<div class="col-6 form-titulo-2 border-left pt-3">
						<h4>Intranet</h4>
					</div>
				</div>
			</div>
			<form id="login-form" method="post" class="form-signin fluid" role="form" action="">
				<div class="row">
					<div class="col-md-12">
						<h4 class="form-titulo">Iniciar sesión</h4>
						<?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
					</div>
					<div class="col-md-12">
						<input name="usuario_acceso" id="usuario_acceso" type="text" class="form-control" value="<?php if(isset($_POST["form_login"])){ echo validar_output($usuario_acceso); } ?>" placeholder="Usuario" maxlength="50" autofocus autocomplete="off" required> 
					</div>
					<div class="col-md-12">
						<input name="contrasena" id="contrasena" type="password" class="form-control" placeholder="Contraseña" maxlength="20" autocomplete="off" required>
					</div>
				</div>
				<div class="row">
					<div class="col-md-9 ">
						<input name="captcha_validacion" id="captcha_validacion" type="text" class="form-control" placeholder="Escriba los caracteres de la imagen" maxlength="5" autocomplete="off" required> 
					</div>
					<div class="col-md-3">
						<center><img src="captcha_imagen.php?ran=<?php echo $codigo; ?>" title="Código aleatorio"></center> 
					</div>
					<div class="col-md-12 pt-1">
						<button class="btn btn-block bt-login" type="submit" name="form_login" id="submit_btn" data-loading-text="Iniciando....">Ingresar</button>
					</div>
					
				</div>	
			</form>
			<div class="form-footer">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12">
						<p>Pozo de Donato S.A.S.<br>Intranet | &copy; Copyright 2024-<?php echo date("Y"); ?></p> 
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="js/jquery-3.4.1.min.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</body>
</html>
<?php
    }
?>
