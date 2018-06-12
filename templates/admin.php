<div class="wrap">
    <h1 class="wp-heading-inline">Omega Modal Login</h1>
    <hr class="wp-header-end">

    <fieldset class="oml-content-wrapper">
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
        <?php
            settings_fields( 'oml-settings-group' );
            do_settings_sections( 'omega_modal_login' );
        ?>
        </form>
    </fieldset>
</div>
