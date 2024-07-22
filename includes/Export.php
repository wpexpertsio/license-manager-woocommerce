<?php

namespace LicenseManagerForWooCommerce;

use Dompdf\Dompdf;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Settings;

defined('ABSPATH') || exit;

class Export
{
    /**
     * Export Constructor.
     */
    public function __construct()
    {
        add_action('lmfwc_export_license_keys_pdf', array($this, 'exportLicenseKeysPdf'), 10, 1);
        add_action('lmfwc_export_license_keys_csv', array($this, 'exportLicenseKeysCsv'), 10, 1);
     
    }


    /**
     * Creates a PDF of license keys by the given array of ID's.
     *
     * @param array $licenseKeyIds
     */
    public function exportLicenseKeysPdf($licenseKeyIds)
    {   
        $get_logo = Settings::get( 'lmfwc_company_logo', Settings::SECTION_WOOCOMMERCE );
        $logo = is_numeric( $get_logo ) ? wp_get_attachment_image_url( $get_logo, 'full' ) : null;
       
        $licenseKeys = array();

        foreach ($licenseKeyIds as $licenseKeyId) {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->find($licenseKeyId);

            if (!$license) {
                continue;
            }

            $licenseKeys[] = array(
                'id' => $license->getId(),
                'order_id' => $license->getOrderId(),
                'product_id' => $license->getProductId(),
                'license_key' => $license->getDecryptedLicenseKey()
            );
        }

        $header = array(
            'id'          => __('ID', 'license-manager-for-woocommerce'),
            'order_id'    => __('Order ID', 'license-manager-for-woocommerce'),
            'product_id'  => __('Product ID', 'license-manager-for-woocommerce'),
            'license_key' => __('License key', 'license-manager-for-woocommerce')
        );

        $pdf = new DOMPDF();
        ob_clean();
        ob_start();

        echo '<table>';
        echo '<thead>';
        echo '<img src="' . esc_url($logo) . '" alt="Logo" style="width: 100px;">';
        echo '<tr>';
       
        foreach ($header as $columnName => $col) {
            echo '<th>' . esc_html($col) . '</th>';
        }
        echo '</tr></thead><tbody>';
        // Data

        foreach ($licenseKeys as $row) {
            echo '<tr>';
            foreach ($row as $columnName => $col) {
                echo '<th>' . esc_html($col) . '</th>';
            }
            echo '</tr>';
        }
        echo '</tr></tbody></table>';
        $html = ob_get_clean();
        $pdf->set_option('enable_html5_parser', true);
        $pdf->set_option('isRemoteEnabled', true);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();
        $pdf->stream(date('YmdHis') . '_license_keys_export.pdf', array('attachment'=>true));
    }

    /**
     * Creates a CSV of license keys by the given array of ID's.
     *
     * @param array $licenseKeyIds
     */
    public function exportLicenseKeysCsv($licenseKeyIds)
    {
        $licenseKeys = array();

        // Should no columns be defined, we will export all of them
        if (!$columns = Settings::get('lmfwc_csv_export_columns', Settings::SECTION_TOOLS)) {
            $columns = array(
                'id'                  => true,
                'order_id'            => true,
                'product_id'          => true,
                'user_id'             => true,
                'license_key'         => true,
                'expires_at'          => true,
                'valid_for'           => true,
                'status'              => true,
                'times_activated'     => true,
                'times_activated_max' => true,
                'created_at'          => true,
                'created_by'          => true,
                'updated_at'          => true,
                'updated_by'          => true
            );
        }

        foreach ($licenseKeyIds as $licenseKeyId) {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->find($licenseKeyId);
            $data    = array();

            if (!$license) {
                continue;
            }

            foreach (array_keys($columns) as $exportColumn) {

                switch ($exportColumn) {
                    case 'license_key':
                        $data[$exportColumn] = $license->getDecryptedLicenseKey();
                        break;
                    case 'status':
                        $data[$exportColumn] = LicenseStatus::getExportLabel($license->getStatus());
                        break;
                    default:
                        $getter              = 'get' . lmfwc_camelize($exportColumn);
                        $data[$exportColumn] = null;

                        if (method_exists($license, $getter)) {
                            $data[$exportColumn] = $license->{$getter}();
                        }

                        break;
                }
            }

            $licenseKeys[] = $data;
        }

        $licenseKeys = apply_filters('lmfwc_export_license_csv', $licenseKeys);
        $filename    = date('YmdHis') . '_license_keys_export.csv';

        // Disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // Force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // Disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

        ob_clean();
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys($licenseKeys[0]));

        foreach ($licenseKeys as $row) {
            fputcsv($df, $row);
        }

        fclose($df);
        ob_end_flush();

        exit();
    }
}
