<?php
return [
    'reports' => [
        'messageMail' => 'The report is ready you can download it by clicking on the following link',
        'hi' => 'Hi!',
        'download' => 'Download',
        'subject' => 'Report SiloSys',
        'expired_at' => 'The report will be available for seven days after today'
    ],
    'email' => [
        'test_email_subject' => 'Test email',
        'test_email_body' => 'Email has been configured correctly',


        'privacy' => "© :year :app - Privacy Policy",
        'copyright' => "© :app Group | All Rights Reserved",
        'new_user_logo_web' => "assets/img/mac-header-ss-en.png",
        'new_user_logo_mobile' => "assets/img/ss-farmer-bg-en.png",
        'new_user_subject' => "Welcome to SiloSys.",
        'new_user_previous' => 'With :app, you can now connect and manage your operations from anywhere',
        'new_user_body' => '
<p class="pstrong" style="padding-top: 20px; text-align: center; color: :color">Hi :name,</p>
<p style="text-align: center">
<span class="spanbody "> With :app, you can now connect and manage your operations from anywhere in the world with real time views of contracts, deliveries, orders and inventory.</span><br /><br />
<span class="spanbody "> Ready to get started? Click on the button below to complete your registration:</span>
</p>
<p style="text-align: center; margin: 40px 0">
<a style="display: inline-block; width: 150px; color: white; background-color: :color; padding: 10px 20px; font-size: 16px; border-radius: 20px; text-decoration: none" href=":hash">Register account</a>
</p>
<p style="text-align: center" class="spanbody">
<span class="spanbody">
Thank You<br />
The :app Team. 
</span>
</p>',
        'new_user_footer' => '
<span class="spanbody "> If you have any questions or problems, our team will be happy to help you. Please contact us at  
<a href="mailto::mailto" style="color: #0169fe;">:mailto</a> or <a href="tel::phone" style="color: #0169fe">:phone</a></span>',

        'reset_password_logo' => "assets/img/ss-logo-gris@3x.png",
        'reset_password_subject' => "Request for Reset Your Password",
        'reset_password_previous' => "You are receiving this email because we have received a request to reset your :app password.",
        'reset_password_body' =>'
<h1 class="text-color-1" style="margin-top: 30px;">
Reset Your Password
</h1>
<p class="pstrong" style="padding-top: 20px; ">Hi :name, </p>
<span class="spanbody ">You are receiving this email because we have received a request to reset your :app password.<br/><br/>
Click the link below to complete the reset process:</span>
<h2 class="text-color-1"
style="margin-bottom: 10px;">
<a href=":hash" style="font-size: 20px !important; color: #0068ff !important;">Reset password</a></span>
</h2>
<p style="padding-top: 20px;color: #616677;"> Thank You. <br/>
The :app Team. </p>',
        'reset_password_footer' => '
<span class="spanbody "> Didn\'t request this change? If you didn\'t request a new password or have any other questions, 
 our team will be happy to hep you.
<a href="mailto::mailto" style="color: #0169fe;">Contact us</a> or call <a href="tel::phone" style="color: #0169fe">:phone</a></span>',
    ]
];