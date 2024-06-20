<?php
echo "--------------------------------------------------------<br />";
echo "--------------------------------------------------------<br />";
echo "--------------------------------------------------------<br />";
echo "--------------------------------------------------------<br />";

echo "Entra al siguiente link: <a href=\"https://hashkiller.co.uk/ntlm-decrypter.aspx\"> https://hashkiller.co.uk/ntlm-decrypter.aspx </a>, copia los códigos de la parte de abajo e insertalos en la página antes proporcionada.<br />";
$code = '';
// password in table users
foreach( $datas[0] as $key => $val )
	if( !is_null( $val->password ) )
		$code .= $val->password . '<br />';
// password in table sellers
foreach( $datas[1] as $key => $val )
	if( !is_null( $val->password ) )
		$code .= $val->password . '<br />';
echo $code;
echo "--------------------------------------------------------<br />";
echo "--------------------------------------------------------<br />";
echo "Una vez ya convertidos, crea un archivo de texto llamado 'passwords.txt', y guardalo en la ruta 'storage/app'.<br />";
echo "--------------------------------------------------------<br />";
echo "--------------------------------------------------------<br />";
echo "--------------------------------------------------------<br />";
?>