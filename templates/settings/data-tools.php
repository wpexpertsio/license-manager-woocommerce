<?php 
/**
 * Some of the code written, maintained by Darko Gjorgjijoski
 */
?>
<h3><?php esc_html_e( 'Database Migration', 'license-manager-for-woocommerce' ); ?></h3>
<p><?php esc_html_e( 'This is one-click migration tool that makes it possible to migrate from other plugins easily. Please take database backups before starting this operation.', 'license-manager-for-woocommerce' ); ?></p>
<form class="lmfwc-tool-form" id="lmfwc-migrate-tool" method="POST">
<table class="form-table">
    
    <tbody>
        <tr>
           
        <td>
           <div class="lmfwc-tool-form-row">
        <label for="identifier"><?php esc_html_e( 'Select plugin', 'license-manager-for-woocommerce' ); ?></label>
        <select id="identifier" name="plugin_name">
            <option value="dlm">
                <?php esc_html_e('Digital License Manager', 'license-manager-for-woocommerce' ); ?>
                </option>
            </select>
        </div>
        <div class="lmfwc-tool-form-row">
            <label>
                <input type="checkbox" name="preserve_ids" value="1">
                <small style="color:red;"><?php esc_html_e( 'Preserve old IDs. If checked, your existing Digital License Manager database will be wiped to remove/free used IDs. Use this ONLY if you are absolutely sure what you are doing and if your app depend on the existing license/generator IDs.', 'license-manager-for-woocommerce' ); ?></small>
            </label>
        </div>
        <div class="lmfwc-tool-form-row lmfwc-tool-form-row-progress" style="display: none;">
            <div class="lmfwc-tool-progress-bar">
                <p class="lmfwc-tool-progress-bar-inner">&nbsp;</p>
            </div>
            <div class="lmfwc-tool-progress-info"><?php esc_html_e( 'Initializing...', 'license-manager-for-woocommerce' ); ?></div>
        </div>
        <div class="lmfwc-tool-form-row">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="identifier" value="migrate">
            <button type="submit" class="button button-small button-primary"><?php esc_html_e( 'Migrate', 'license-manager-for-woocommerce' ); ?></button>
        </div> 

        </td>
    </tr>
    </tbody>
</table>

    
    </form>



    <h3><?php esc_html_e( 'Past Orders License Generator', 'license-manager-for-woocommerce' ); ?></h3>
    <p><?php esc_html_e( 'This tool generates licenses for all past orders that doesn\'t have license assigned. Useful if you already have established shop and want to assign licenses to your existing orders.', 'license-manager-for-woocommerce' ); ?></p>
    <form class="lmfwc-tool-form" id="lmfwc-generate-tool" method="POST">
        <table class="form-table">
            <tbody>
                <tr>
                    <td>
                        <div class="lmfwc-tool-form-row">
        <label for="generator"><?php esc_html_e( 'Generator', 'license-manager-for-woocommerce' ); ?> <span class="required">*</span></label>
        <select id="generator" name="generator" required>
        </select>
    </div>
    <div class="lmfwc-tool-form-row">
        <label>
            <input type="checkbox" name="use_product_licensing_configuration" value="1">
            <small><?php esc_html_e( 'Use product settings where possible, e.g some products have their own licensing configuration settings.', 'license-manager-for-woocommerce' ); ?></small>
        </label>
    </div>
    
    <div class="lmfwc-tool-form-row lmfwc-tool-form-row-progress" style="display: none;">
        <div class="lmfwc-tool-progress-bar">
            <p class="lmfwc-tool-progress-bar-inner">&nbsp;</p>
        </div>
        <div class="lmfwc-tool-progress-info"><?php esc_html_e( 'Initializing...', 'license-manager-for-woocommerce' ); ?></div>
    </div>
    <div class="lmfwc-tool-form-row">
        <input type="hidden" name="id" value=""/>
        <input type="hidden" name="identifier" value="generate"/>
        <input type="hidden" name="tool" value="">
        <button type="submit" class="button button-small button-primary"><?php esc_html_e( 'Process', 'license-manager-for-woocommerce' ); ?></button>
    </div>
                    </td>
                </tr>
            </tbody>
     
</form>