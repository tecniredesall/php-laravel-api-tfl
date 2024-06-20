<?php
return [
    'reports' => [
        'messageMail' => 'El informe está listo, puede descargarlo haciendo clic en el siguiente enlace.',
        'hi' => 'Hola!',
        'download' => 'Descargar',
        'subject' => 'Reporte SiloSys',
        'expired_at' => 'El informe estará disponible durante siete días después de hoy.'

    ],
    'email' => [
        'test_email_subject' => 'Prueba de envío de correo electrónico',
        'test_email_body' => 'Ha sido enviado un Correo electrónico de prueba desde SiloSys, el correo electrónico se ha configurado correctamente',

        'privacy' => "© :year :app - Pol&iacute;tica de privacidad",
        'copyright' => "© :app Grupo | Todos los derechos reservados",
        'new_user_logo_web' => "assets/img/mac-header-ss-es.png",
        'new_user_logo_mobile' => "assets/img/ss-farmer-bg-es.png",

        'new_user_subject' => "Bienvenido a SiloSys.",
        'new_user_previous' => 'Con :app, ahora puede conectar y administrar sus operaciones desde cualquier parte del mundo con vistas en tiempo real de contratos',
        'new_user_body' => '
<p class="pstrong" style="padding-top: 20px; text-align: center; color: :color">Hola :name, </p>
<p style="text-align: center">
<span class="spanbody "> Con :app, ahora puede conectar y administrar sus operaciones desde cualquier parte del mundo con vistas en tiempo real de contratos, entregas, pedidos e inventarios.</span><br /><br />
<span class="spanbody "> ¿Listo para comenzar? Haga clic en el bot&oacute;n de abajo para completar su registro:</span>
</p>
<p style="text-align: center; margin: 40px 0">
<a style="display: inline-block; width: 150px; color: white; background-color: :color; padding: 10px 20px; font-size: 16px; border-radius: 20px; text-decoration: none" href=":hash">Registrar cuenta</a>
</p>
<p style="text-align: center">
<span class="spanbody"> 
Gracias<br />
Equipo :app. 
</span>
</p>',
        'new_user_footer' => '
<span class="spanbody ">Si tiene alguna pregunta o problema, nuestro equipo estar&aacute; encantado de ayudarlo. P&oacute;ngase en contacto con nosotros en  
<a href="mailto::mailto" style="color: #0169fe;">:mailto</a> o <a href="tel::phone" style="color: #0169fe;">:phone</a></span>',

        'reset_password_logo' => "assets/img/ss-logo-gris@3x.png",
        'reset_password_subject' => "Solicitud para restablecer contraseña",
        'reset_password_previous' => "Est&aacute; recibiendo este correo electr&oacute;nico 
porque hemos recibido una solicitud para restablecer su contraseña de :app.",
        'reset_password_body' =>
            '<h1 class="text-color-1" style="margin-top: 30px;">
Restablecer contraseña
</h1>
<p class="pstrong" style="padding-top: 20px; ">Hola :name, </p>
<span class="spanbody "> Est&aacute; recibiendo este correo electr&oacute;nico 
porque hemos recibido una solicitud para restablecer su contraseña de SiloSys.<br/><br/>
Haga clic en el siguiente enlace para completar el proceso:</span>
<h2 class="text-color-1"
style="margin-bottom: 10px; ">
<a href=":hash" style="font-size: 20px !important; color: #0068ff !important;">Recuperar contraseña</a></span>
</h2>
<p style="padding-top: 20px;color: #616677;" class="spanbody"> 
<span class="spanbody">Gracias. <br/>
Equipo :app.
</span> 
</p>',
        'reset_password_footer' => '
<span class="spanbody "> ¿No solicit&oacute; este cambio? Si no solicit&oacute; una nueva contraseña o tiene alguna otra pregunta, 
nuestro equipo estar&aacute;  encantado de ayudarlo.
<a href="mailto::mailto" style="color: #0068ff;">Cont&aacute;ctanos</a> o ll&aacute;manos <a href="tel::phone" style="color: #0068ff;">:phone</a></span>',
    ]
];