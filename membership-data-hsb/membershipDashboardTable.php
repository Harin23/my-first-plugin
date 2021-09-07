<?php
// echo 'hello';
function display_membership_summary_table_hsb(){
    // echo 'it works';
    global $wpdb;
    // echo "{$wpdb->base_prefix}mepr_transactions";
    $active_users_query = "SELECT CASE
                                custom.product_id WHEN 1914 THEN 'Associate Member' WHEN 1915 THEN 'Full Member' WHEN 1916 THEN 'Link Member' WHEN 1917 THEN 'Organizational Member' WHEN 3966 THEN 'Directory Listing' ELSE 'unkown'
                            END AS Membership,
                            COALESCE(joined1.total, 0) AS 'Total',
                            COALESCE(custom.active, 0) AS 'Active',
                            COALESCE(joined2.renewal_overdue, 0) AS 'Renewal Overdue',
                            COALESCE(lap1.lapsed, 0) AS 'Lapsed',
                            COALESCE(
                                pending1.pending_new_or_expired,
                                0
                            ) AS 'Pending Orders: New Or Expired',
                            COALESCE(pending2.pending_renewed, 0) AS 'Pending Orders: Renewal',
                            COALESCE(member1.new_users, 0) AS 'New Users In The Last Month',
                            COALESCE(member2.new_users, 0) AS 'New Users In The Last 5 Months'
                            FROM
                                (
                                SELECT
                                    t1.user_id,
                                    t1.product_id,
                                    COUNT(t1.product_id) AS active
                                FROM
                                    `{$wpdb->base_prefix}mepr_transactions` t1
                                WHERE
                                    t1.created_at =(
                                    SELECT
                                        MAX(t2.created_at)
                                    FROM
                                        `{$wpdb->base_prefix}mepr_transactions` t2
                                    WHERE
                                        t2.user_id = t1.user_id AND t2.status = 'complete' AND t1.expires_at > NOW())
                                    GROUP BY
                                        t1.product_id
                                ) AS custom
                            LEFT JOIN(
                                SELECT
                                    t1.product_id,
                                    COUNT(t1.product_id) AS total
                                FROM
                                    `{$wpdb->base_prefix}mepr_transactions` t1
                                WHERE
                                    t1.created_at =(
                                    SELECT
                                        MAX(t2.created_at)
                                    FROM
                                        `{$wpdb->base_prefix}mepr_transactions` t2
                                    WHERE
                                        t2.user_id = t1.user_id AND t2.status = 'complete'
                                )
                            GROUP BY
                                t1.product_id
                            ) AS joined1
                            ON
                                custom.product_id = joined1.product_id
                            LEFT JOIN(
                                SELECT
                                    mtran.product_id,
                                    COUNT(mtran.product_id) AS renewal_overdue
                                FROM
                                    (
                                    SELECT
                                        t1.product_id,
                                        t1.expires_at
                                    FROM
                                        `{$wpdb->base_prefix}mepr_transactions` t1
                                    WHERE
                                        t1.created_at =(
                                        SELECT
                                            MAX(t2.created_at)
                                        FROM
                                            `{$wpdb->base_prefix}mepr_transactions` t2
                                        WHERE
                                            t2.user_id = t1.user_id AND t2.expires_at > NOW() AND t2.expires_at < DATE_ADD(NOW(), INTERVAL 30 DAY))) AS mtran
                                        GROUP BY
                                            mtran.product_id
                                    ) AS joined2
                                ON
                                    custom.product_id = joined2.product_id
                                LEFT JOIN(
                                    SELECT
                                        t1.product_id,
                                        COUNT(t1.product_id) AS lapsed
                                    FROM
                                        (
                                        SELECT
                                            t1.product_id,
                                            t1.created_at,
                                            t1.expires_at
                                        FROM
                                            `{$wpdb->base_prefix}mepr_transactions` t1
                                        WHERE
                                            t1.created_at =(
                                            SELECT
                                                MAX(t2.created_at)
                                            FROM
                                                `{$wpdb->base_prefix}mepr_transactions` t2
                                            WHERE
                                                t2.user_id = t1.user_id AND t2.status = 'complete'
                                        )
                                    ) t1
                                WHERE
                                    t1.expires_at < NOW()
                                GROUP BY
                                    t1.product_id) AS lap1
                                ON
                                    custom.product_id = lap1.product_id
                                LEFT JOIN(
                                    SELECT CASE
                                        oim.meta_value WHEN 4220 THEN 1917 WHEN 3632 THEN 3966 WHEN 3151 THEN 1916 WHEN 3138 THEN 1914 WHEN 2574 THEN 1915
                                END AS product_id,
                                COUNT(oim.meta_value) AS pending_new_or_expired
                            FROM
                                {$wpdb->base_prefix}postmeta AS pm
                            LEFT JOIN {$wpdb->base_prefix}posts AS p
                            ON
                                pm.post_id = p.ID
                            LEFT JOIN `{$wpdb->base_prefix}woocommerce_order_items` oi ON
                                oi.order_id = pm.post_id
                            LEFT JOIN `{$wpdb->base_prefix}woocommerce_order_itemmeta` oim ON
                                oim.order_item_id = oi.order_item_id
                            WHERE
                                p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-pending' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3138, 2574, 3632, 4220, 3151)
                            GROUP BY
                                oim.meta_value
                                ) AS pending1
                            ON
                                custom.product_id = pending1.product_id
                            LEFT JOIN(
                                SELECT CASE
                                    oim.meta_value WHEN 3148 THEN 1916 WHEN 3143 THEN 1914 WHEN 3050 THEN 1915
                            END AS product_id,
                            COUNT(oim.meta_value) AS pending_renewed
                            FROM
                                {$wpdb->base_prefix}postmeta AS pm
                            LEFT JOIN {$wpdb->base_prefix}posts AS p
                            ON
                                pm.post_id = p.ID
                            LEFT JOIN `{$wpdb->base_prefix}woocommerce_order_items` oi ON
                                oi.order_id = pm.post_id
                            LEFT JOIN `{$wpdb->base_prefix}woocommerce_order_itemmeta` oim ON
                                oim.order_item_id = oi.order_item_id
                            WHERE
                                p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-pending' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3148, 3143, 3050)
                            GROUP BY
                                oim.meta_value
                                ) pending2
                            ON
                                custom.product_id = pending2.product_id
                            LEFT JOIN(
                                SELECT
                                    t1.product_id,
                                    COUNT(t1.product_id) AS new_users
                                FROM
                                    (
                                    SELECT
                                        t1.user_id,
                                        t1.product_id,
                                        t1.created_at
                                    FROM
                                        `{$wpdb->base_prefix}mepr_transactions` t1
                                    WHERE
                                        t1.created_at =(
                                        SELECT
                                            MAX(t2.created_at)
                                        FROM
                                            `{$wpdb->base_prefix}mepr_transactions` t2
                                        WHERE
                                            t2.user_id = t1.user_id
                                    )
                                ) t1
                            LEFT JOIN(
                                SELECT
                                    user_id,
                                    STR_TO_DATE(meta_value, '%m/%d/%Y %H:%i:%s') AS member_since
                                FROM
                                    `{$wpdb->base_prefix}usermeta`
                                WHERE
                                    meta_key = 'mepr_member_since'
                            ) um1
                            ON
                                t1.user_id = um1.user_id
                            WHERE
                                um1.member_since IS NOT NULL AND DATE(um1.member_since) >= DATE(
                                    DATE_SUB(NOW(), INTERVAL 1 MONTH))
                                GROUP BY
                                    t1.product_id
                                ) AS member1
                            ON
                                custom.product_id = member1.product_id
                            LEFT JOIN(
                                SELECT
                                    t1.product_id,
                                    COUNT(t1.product_id) AS new_users
                                FROM
                                    (
                                    SELECT
                                        t1.user_id,
                                        t1.product_id,
                                        t1.created_at
                                    FROM
                                        `{$wpdb->base_prefix}mepr_transactions` t1
                                    WHERE
                                        t1.created_at =(
                                        SELECT
                                            MAX(t2.created_at)
                                        FROM
                                            `{$wpdb->base_prefix}mepr_transactions` t2
                                        WHERE
                                            t2.user_id = t1.user_id
                                    )
                                ) t1
                            LEFT JOIN(
                                SELECT
                                    user_id,
                                    STR_TO_DATE(meta_value, '%m/%d/%Y %H:%i:%s') AS member_since
                                FROM
                                    `{$wpdb->base_prefix}usermeta`
                                WHERE
                                    meta_key = 'mepr_member_since'
                            ) um1
                            ON
                                t1.user_id = um1.user_id
                            WHERE
                                um1.member_since IS NOT NULL AND DATE(um1.member_since) >= DATE(
                                    DATE_SUB(NOW(), INTERVAL 5 MONTH))
                                GROUP BY
                                    t1.product_id
                                ) AS member2
                            ON
                                custom.product_id = member2.product_id;";
    $results = $wpdb->get_results($active_users_query, ARRAY_A);
    // print_r($results);
    ?>
    <?php if (count($results)>0): ?>
        <div id="membership-dashboard-table-hsb">
            <table>
                <thead>
                    <tr>
                    <th><?php echo implode('</th><th>', array_keys(current($results))); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): array_map('htmlentities', $row); ?>
                        <tr>
                            <td id="table-memberships-hsb"><?php echo implode('</td><td>', $row); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php
}
?>
