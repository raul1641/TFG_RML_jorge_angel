<?php
// recogemos en unas variables los datos de la conexión

// a "mysqli()" le tengo que pasar:
// -> el servidor al que me quiero conectar
// -> el usuario
// -> la contraseña
// -> esquema (shema) que quiero utilizar

$servidor="127.0.0.1";
$usuario="jorge";
$contraseña="666666.j";
$shema="registro_libros";

//**********************************************************************
// CONEXIÓN A LOCALHOST
//**********************************************************************
// si en el código que hay dentro de un "try" -> se produce un error->
// se ejecuta el código que haya en el "catch"
try
{
	// establecemos conexión al servidor de base de Datos
	$conexion=new mysqli($servidor, $usuario, $contraseña, $shema);
	
	// visualizamos mensaje éxito
	//echo "<font color='blue' size='5'>
	//<b>MENSAJE:</b><br> La conexión (localhost) al Servidor de Base de Datos se ha establecido correctamente !!</font><br><br>";
	
	//para evitar problemas con acentos y ñ configuramos las querys de esta manera 
	$conexion->query("SET NAMES 'utf8'");
	
} 
//**********************************************************************
// en "$e" tenemos la descripción del Error
// podemos utilizarla o no utilizarla
catch (exception $e)
{
    //echo "Error capturado: ".$e->getMessage()."<br>";
	//echo "<font color='red' size='5'>
	//<b>ERROR:</b><br> No se pudo realizar la conexión al Servidor de Base de Datos!!</font><br><br>";
}
//**********************************************************************
?>