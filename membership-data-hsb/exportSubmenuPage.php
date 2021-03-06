<?php 
    require_once(dirname(__FILE__)."/queriesHSB.php");

    function membership_data_download_csv_hsb(){
        if ( isset($_POST['download_csv_hsb']) ) {
            
            $membership=$_POST['membership_type'];   
            if(empty($membership)){
                $membership = 1915;
            }
            $filename=$_POST['file_name_hsb'];
            if(empty($filename)){
            $filename = $membership.'-membership_'.date("d-M-Y");
            }
            $parent_or_subs=$_POST['parent_or_sub_mepr_export'];
            if(empty($parent_or_subs)){
                $parent_or_subs='parent';
            };

            global $wpdb;
            $prefix_hsb = $wpdb->prefix;

            if($parent_or_subs === 'sub'){
                $query = get_mp_export_query_organizational_hsb($prefix_hsb, $membership, false);
                $result_active = $wpdb->get_results($query, ARRAY_A);
    
                $query = get_mp_export_query_organizational_hsb($prefix_hsb, $membership, true);
                $result_inactive = $wpdb->get_results($query, ARRAY_A);

                $header_hsb=array('ID', 'Last Name', 'First Name', 'Display Name', 'Email', 'Created On', 'Expires On', 'Parent Account');
                $header_membership_hsb=array('Membership id:', $membership, 'Sub Accounts', NULL, NULL, NULL, NULL, NULL);
                $header_active_hsb=array('ACTIVE:', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
                $header_space_hsb=array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
                $header_inactive_hsb=array('INACTIVE:', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
            }else{
                $query = get_mp_export_query_hsb($prefix_hsb, $membership, false);
                $result_active = $wpdb->get_results($query, ARRAY_A);

                $query = get_mp_export_query_hsb($prefix_hsb, $membership, true);
                $result_inactive = $wpdb->get_results($query, ARRAY_A);

                $header_hsb=array('ID', 'Last Name', 'First Name', 'Display Name', 'Designations', 'Email', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Created On', 'Expires On');
                $header_membership_hsb=array('Membership id:', $membership, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
                $header_active_hsb=array('ACTIVE:', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
                $header_space_hsb=array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
                $header_inactive_hsb=array('INACTIVE:', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
            }

            $fp = fopen("php://output", "w");
            header("Content-type: text/csv");
            header("Content-disposition: csv" . date("Y-m-d") . ".csv");
            header( "Content-disposition: filename=".$filename.".csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            fputcsv($fp, $header_membership_hsb);
            fputcsv($fp, $header_space_hsb);
            fputcsv($fp, $header_active_hsb);
            if(!empty($result_active)){
                fputcsv( $fp, $header_hsb);
                foreach ( $result_active as $row ) {
                    fputcsv( $fp, $row );
                }
            }

            fputcsv($fp, $header_space_hsb);
            fputcsv($fp, $header_space_hsb);
            fputcsv($fp, $header_space_hsb);
            fputcsv($fp, $header_inactive_hsb);

            if(!empty($result_inactive)){
                fputcsv( $fp, $header_hsb);
                foreach ( $result_inactive as $row ) {
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

            $membership=explode(',', $_POST['membership_type_wc']);  
            if(empty($membership)){
                $membership = array(0=>2574, 1=>3050);
            }

            $filename=$_POST['file_name_hsb'];
            if(empty($filename)){
                $filename = $membership[0].'-Report_'.$from_date_hsb.'_to_'.$to_date_hsb;
            }

            global $wpdb;

            $prefix_hsb = $wpdb->prefix;
        
            $header_membership_hsb=array('Product Id:', $membership[0], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
            $header_new_hsb=array('NEW/EXISTING', 'PURCHASES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
            $header_hsb=array('ID', 'Last Name', 'First Name', 'Display Name', 'Email', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Paid Date', 'Payment Method');

            $fp = fopen("php://output", "w");
            header("Content-type: text/csv");
            header("Content-disposition: csv" . date("Y-m-d") . ".csv");
            header( "Content-disposition: filename=".$filename.".csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            $query_new = get_wc_export_query_hsb($prefix_hsb, $membership[0], $from_date_hsb, $to_date_hsb);
            $result_new = $wpdb->get_results($query_new, ARRAY_A);
            fputcsv( $fp, $header_membership_hsb);
            fputcsv( $fp, $header_new_hsb);
            fputcsv( $fp, $header_hsb);
            if(!empty($result_new)){
                foreach ( $result_new as $row ) {
                    fputcsv( $fp, $row );
                }
            }

            if(count($membership) === 2){
                $header_membership_hsb=array('Product Id:', $membership[1], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
                $header_space_hsb=array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
                $header_renewal_hsb=array('RENEWAL', 'PURCHASES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

                $query_renew = get_wc_export_query_hsb($prefix_hsb, $membership[1], $from_date_hsb, $to_date_hsb);
                $result_renew = $wpdb->get_results($query_renew, ARRAY_A);
                fputcsv( $fp, $header_space_hsb);
                fputcsv( $fp, $header_space_hsb);
                fputcsv( $fp, $header_space_hsb);
                fputcsv( $fp, $header_membership_hsb);
                fputcsv( $fp, $header_renewal_hsb);
                fputcsv( $fp, $header_hsb);

                if(!empty($result_renew)){
                    foreach ( $result_renew as $row2 ) {
                        fputcsv( $fp, $row2 );
                    }
                }
            }
            exit;
        }
    }

    function render_export_submenu_page_html_hsb($memberships_mepr, $memberships_wc){
        
?>
    <div class="align-center-hsb">
    <hr>
        <form method="post" id="download_csv_form_hsb" action="">
            <h3>Export all members by membership type:</h3>
            <table class="form-table-hsb" id="date-range-table-hsb">
                <tr>
                    <td><label>Select Membership Type:</label></td>
                    <td>
                        <select name="membership_type" id="membership-type">
                            <?php 
                            foreach($memberships_mepr as $membership_id=>$membership){
                                echo '<option value="'.$membership_id.'">'.$membership.'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Parent Or Sub Accounts:</label></td>
                    <td><label for="parent-mepr-export">Parent</label><input type="radio" name="parent_or_sub_mepr_export" id="parent-mepr-export" value="parent" checked><label for="sub-mepr-export">Sub</label><input type="radio" name="parent_or_sub_mepr_export" id="sub-mepr-export" value="sub" disabled></td>
                </tr>
                <tr>
                    <td><label for="file-name2-hsb">Custom File Name:</label></td>
                    <td><input type="text" name="file_name2_hsb" id="file-name2-hsb"></td>
                </tr>
            </table>
            <input type="submit" name="download_csv_hsb" class="button-primary" value="Export Report" />
        </form>
        <hr>
        <form method="post" id="download_quarterly_report_form_hsb" action="">
            <h3>Export membership related transactional data for specific time periods:</h3>
            <table class="form-table-hsb" id="date-range-table-hsb">
                <tr>
                    <td><label>Select Membership Type:</label></td>
                    <td>
                        <select name="membership_type_wc" id="membership-type-wc">
                            <?php 
                            foreach($memberships_wc as $membership_id=>$membership){
                                $values = implode(',',$membership);
                                echo '<option value="'.$values.'">'.$membership_id.'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Inclusive Date Range:</label></td>
                    <td><label for="year">From:</label> <input type="date" name="qreport_from_date_hsb" id="from-date-hsb"> <label for="year">To:</label> <input type="date" name="qreport_to_date_hsb" id="to-date-hsb"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Auto Fill Date:</td>
                </tr>
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