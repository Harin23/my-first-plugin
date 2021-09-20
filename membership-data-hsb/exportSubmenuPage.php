<?php 
    require_once(dirname(__FILE__)."/queriesHSB.php");

    function membership_data_download_csv_hsb(){

        if ( isset($_POST['download_csv_hsb']) ) {
            global $wpdb;
            $prefix_hsb = $wpdb->prefix;
            $query = get_mp_table_query_hsb($prefix_hsb );
            $result = $wpdb->get_results($query, ARRAY_A);
    
            $filename = 'Full-Members-'.date("d-M-Y");

            $fp = fopen("php://output", "w");
            header("Content-type: text/csv");
            header("Content-disposition: csv" . date("Y-m-d") . ".csv");
            header( "Content-disposition: filename=".$filename.".csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            if(!empty($result)){
                fputcsv( $fp, array('ID', 'Last Name', 'First Name', 'Display Name', 'Email', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Expires On'));
                foreach ( $result as $row ) {
                    fputcsv( $fp, $row );
                }
            }
            exit;
        }elseif (isset($_POST['download_quarterly_report_hsb'])) {
            $from_date_hsb=$_POST['qreport_from_date_hsb'];
            $to_date_hsb=$_POST['qreport_to_date_hsb'];
            if(empty($from_date_hsb) || empty($to_date_hsb)){
                $from_date_hsb=date('Y-m-d', strtotime('-1 months'));
                $to_date_hsb= date('Y-m-d');
            }

            $filename=$_POST['file_name_hsb'];
            if(empty($filename)){
                $filename = 'Full-Members-Report_'.$from_date_hsb.'_to_'.$to_date_hsb;
            }

            global $wpdb;

            $prefix_hsb = $wpdb->prefix;
        
            $query_new = get_quarterly_reports_query_hsb($prefix_hsb, '2574', $from_date_hsb, $to_date_hsb);
            $result_new = $wpdb->get_results($query_new, ARRAY_A);

            $query_renew = get_quarterly_reports_query_hsb($prefix_hsb, '3050', $from_date_hsb, $to_date_hsb);
            $result_renew = $wpdb->get_results($query_new, ARRAY_A);

            $header_new_hsb=array('NEW/EXISTING', 'PURCHASES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
            $header_hsb=array('ID', 'Last Name', 'First Name', 'Display Name', 'Email', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Paid Date', 'Payment Method');
            $header_space_hsb=array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
            $header_renewal_hsb=array('RENEWAL', 'PURCHASES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

            $fp = fopen("php://output", "w");
            header("Content-type: text/csv");
            header("Content-disposition: csv" . date("Y-m-d") . ".csv");
            header( "Content-disposition: filename=".$filename.".csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            fputcsv( $fp, $header_new_hsb);
            fputcsv( $fp, $header_hsb);
            if(!empty($result_new)){
                foreach ( $result_new as $row ) {
                    fputcsv( $fp, $row );
                }
            }

            fputcsv( $fp, $header_space_hsb);
            fputcsv( $fp, $header_space_hsb);
            fputcsv( $fp, $header_space_hsb);
            fputcsv( $fp, $header_renewal_hsb);
            fputcsv( $fp, $header_hsb);

            if(!empty($result_renew)){
                foreach ( $result_renew as $row2 ) {
                    fputcsv( $fp, $row2 );
                }
            }
            exit;
        }
    }

    function render_export_submenu_page_html_hsb(){
?>
    <div class="align-center-hsb">
    <hr>
        <form method="post" id="download_csv_form_hsb" action="">
            <h3>Please Use this to export a list of all current full members from the Memberpress Table:</h3>
            <label>Disabled, because this feature hasn't been fully tested/completed yet.</label>
            <input type="submit" name="download_csv_hsb" class="button-primary" disabled="true" value="Export Report" />
        </form>
        <hr>
        <form method="post" id="download_quarterly_report_form_hsb" action="">
            <h3>Please Use this form to export full membership purchase data from WooCommerce:</h3>
            <table class="form-table-hsb">
                <tr>
                    <td><label>Inclusive Date Range:</label></td>
                    <td><label for="year">From:</label> <input type="date" name="qreport_from_date_hsb" id="from-date-hsb"> <label for="year">To:</label> <input type="date" name="qreport_to_date_hsb" id="to-date-hsb"></td>
                </tr>
            </table>
            <h4>The following settings can be used to auto fill the date range and to set a custom name for the file:</h6>
            <table class="form-table-hsb" id="date-range-table-hsb">
                <tr>
                    <td><label>Select Year:</label></td>
                    <td><label for="last-year-hsb">Last Year</label> <input type="radio" name="year_hsb" id="last-year-hsb" value="<?php echo date('Y') - 1 ?>"> <label for="current-year-hsb">Current Year</label> <input type="radio" name="year_hsb" id="current-year-hsb" checked="checked" value="<?php echo date('Y')?>"></td>
                </tr>
                <tr>
                    <td><label>Select Quarter:</label></td>
                    <td><input type="button" id='q1-hsb' value="Quarter 1"><input type="button" id='q2-hsb' value="Quarter 2"><input type="button" id='q3-hsb' value="Quarter 3"><input type="button" id='q4-hsb' value="Quarter 4"><input type="button" id='clear-dates' value="clear dates"></td>
                </tr>
                <tr>
                    <td><label for="file-name-hsb">Custom File Name:</label></td>
                    <td><input type="text" name="file_name_hsb" id="file-name-hsb"></td>
                </tr>
            </table>
            <input type="submit" name="download_quarterly_report_hsb" class="button-primary" value="Export Report" />
        </form>
    </div>
<?php
    }
?>