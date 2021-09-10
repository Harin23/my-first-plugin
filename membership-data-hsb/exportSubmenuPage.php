<?php 
    function membership_data_download_csv_hsb(){

        if ( isset($_POST['download_csv']) ) {
            global $wpdb;
            $query = "SELECT
                        u1.ID,
                        TRIM(
                            SUBSTR(
                                u1.display_name,
                                CHAR_LENGTH(u1.display_name) +1 - LOCATE(' ', REVERSE(u1.display_name))
                            )
                        ) AS last_name,
                        SUBSTRING_INDEX(
                            SUBSTRING_INDEX(u1.display_name, ' ', 1),
                            ' ',
                            -1
                        ) AS first_name,
                        u1.display_name,
                        u1.user_email,
                        meta2.meta_value AS address_1,
                        meta7.meta_value AS address_2,
                        meta3.meta_value AS city,
                        meta4.meta_value AS state,
                        meta5.meta_value AS zip,
                        meta6.meta_value AS country,
                        meta8.meta_value AS phone_number,
                        DATE(meta.meta_value) AS member_since,
                        DATE(t1.created_at),
                        DATE(t1.expires_at)
                    FROM
                        {$wpdb->prefix}users u1
                    JOIN(
                        SELECT
                            inner_t.*
                        FROM
                            {$wpdb->prefix}mepr_transactions inner_t
                        WHERE
                            inner_t.product_id = 1915 AND inner_t.expires_at > NOW() AND inner_t.status = 'complete') t1
                        ON
                            u1.ID = t1.user_id
                        LEFT JOIN(
                            SELECT
                                user_id,
                                STR_TO_DATE(meta_value, '%m/%d/%Y %H:%i:%s') AS meta_value
                            FROM
                                {$wpdb->prefix}usermeta
                            WHERE
                                meta_key = 'mepr_member_since'
                        ) AS meta
                    ON
                        u1.ID = meta.user_id
                    LEFT JOIN(
                        SELECT
                            *
                        FROM
                            {$wpdb->prefix}usermeta
                        WHERE
                            meta_key = 'mepr-address-one'
                    ) AS meta2
                    ON
                        u1.ID = meta2.user_id
                    LEFT JOIN(
                        SELECT
                            *
                        FROM
                            {$wpdb->prefix}usermeta
                        WHERE
                            meta_key = 'mepr-address-city'
                    ) AS meta3
                    ON
                        u1.ID = meta3.user_id
                    LEFT JOIN(
                        SELECT
                            *
                        FROM
                            {$wpdb->prefix}usermeta
                        WHERE
                            meta_key = 'mepr-address-state'
                    ) AS meta4
                    ON
                        u1.ID = meta4.user_id
                    LEFT JOIN(
                        SELECT
                            *
                        FROM
                            {$wpdb->prefix}usermeta
                        WHERE
                            meta_key = 'mepr-address-zip'
                    ) AS meta5
                    ON
                        u1.ID = meta5.user_id
                    LEFT JOIN(
                        SELECT
                            *
                        FROM
                            {$wpdb->prefix}usermeta
                        WHERE
                            meta_key = 'mepr-address-country'
                    ) AS meta6
                    ON
                        u1.ID = meta6.user_id
                    LEFT JOIN(
                        SELECT
                            *
                        FROM
                            {$wpdb->prefix}usermeta
                        WHERE
                            meta_key = 'mepr-address-two'
                    ) AS meta7
                    ON
                        u1.ID = meta7.user_id
                    LEFT JOIN(
                        SELECT
                            *
                        FROM
                            {$wpdb->prefix}usermeta
                        WHERE
                            meta_key = 'billing_phone'
                    ) AS meta8
                    ON
                        u1.ID = meta8.user_id
                    ORDER BY
                        t1.expires_at ASC";
            $result = $wpdb->get_results($query, ARRAY_A);
    
            $filename = 'Full-Members-'.date("d-M-Y");

            $fp = fopen("php://output", "w");
            header("Content-type: text/csv");
            header("Content-disposition: csv" . date("Y-m-d") . ".csv");
            header( "Content-disposition: filename=".$filename.".csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            if(!empty($result)){
                fputcsv( $fp, array('ID', 'Last Name', 'First Name', 'Display Name', 'Email', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Member Since', 'Created On', 'Expires On'));
                foreach ( $result as $row ) {
                    fputcsv( $fp, $row );
                }
            }
            exit;
        }
    }

    function render_export_submenu_page_html_hsb(){
?>
    <div>
        <form method="post" id="download_form" action="">
            <input type="submit" name="download_csv" class="button-primary" value="Export List of All Full Members" />
        </form>
    </div>
<?php
    }
?>