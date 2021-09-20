<?php
    global $plugin_page;
    function display_membership_summary_table_hsb(){
        global $wpdb;
        $prefix_hsb=$wpdb->base_prefix;
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
    COALESCE(completed_renewals_last_month.completed_renewal, 0) AS 'Last Month\'s Completed Orders: Renewal',
    COALESCE(completed_renewals_last_month2.completed_renewal2, 0) AS 'This Month\'s Completed Orders So Far: Renewal',
    COALESCE(completed_new_last_month.completed_new_or_expired, 0) AS 'Last Month\'s Completed Orders: New or Expired',
    COALESCE(completed_new_last_month2.completed_new_or_expired2, 0) AS 'This Month\'s Completed Orders So Far: New or Expired'
    FROM
        (
        SELECT
            t1.product_id,
            COUNT(t1.product_id) AS active
        FROM
            {$prefix_hsb}mepr_transactions t1
        WHERE
            t1.created_at =(
            SELECT
                MAX(t2.created_at)
            FROM
                {$prefix_hsb}mepr_transactions t2
            WHERE
                t2.user_id = t1.user_id AND t2.status = 'complete'
        ) AND t1.expires_at >= NOW()
    GROUP BY
        t1.product_id) AS custom
        -- RENEWAL OVERDUE JOIN:
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
                {$prefix_hsb}mepr_transactions t1
            WHERE
                t1.created_at =(
                SELECT
                    MAX(t2.created_at)
                FROM
                    {$prefix_hsb}mepr_transactions t2
                WHERE
                    t2.user_id = t1.user_id AND t2.status = 'complete' AND t1.expires_at <= NOW() AND t1.expires_at <= DATE_ADD(NOW(), INTERVAL 60 DAY))) AS mtran
                GROUP BY
                    mtran.product_id
            ) AS joined2
        ON
            custom.product_id = joined2.product_id 
            -- LAPSED JOIN:
        LEFT JOIN(
            SELECT
                t3.product_id,
                COUNT(t3.product_id) AS lapsed
            FROM
                (
                SELECT
                    t1.product_id,
                    t1.created_at,
                    t1.expires_at
                FROM
                    {$prefix_hsb}mepr_transactions t1
                WHERE
                    t1.created_at =(
                    SELECT
                        MAX(t2.created_at)
                    FROM
                        {$prefix_hsb}mepr_transactions t2
                    WHERE
                        t2.user_id = t1.user_id AND t2.status = 'complete' AND t1.expires_at < NOW() AND t1.expires_at < DATE_ADD(NOW(), INTERVAL 60 DAY))
                ) t3
            GROUP BY
                t3.product_id
            ) AS lap1
        ON
            custom.product_id = lap1.product_id
            -- Pending Orders for membership purchases of type: New or Expired
        LEFT JOIN(
            SELECT CASE
                oim.meta_value WHEN 4220 THEN 1917 WHEN 3632 THEN 3966 WHEN 3151 THEN 1916 WHEN 3138 THEN 1914 WHEN 2574 THEN 1915
        END AS product_id,
        COUNT(oim.meta_value) AS pending_new_or_expired
    FROM
        {$prefix_hsb}postmeta AS pm
    LEFT JOIN {$prefix_hsb}posts AS p
    ON
        pm.post_id = p.ID
    LEFT JOIN {$prefix_hsb}woocommerce_order_items oi ON
        oi.order_id = pm.post_id
    LEFT JOIN {$prefix_hsb}woocommerce_order_itemmeta oim ON
        oim.order_item_id = oi.order_item_id
    WHERE
        p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-pending' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3138, 2574, 3632, 4220, 3151)
    GROUP BY
        oim.meta_value
        ) AS pending1
    ON
        custom.product_id = pending1.product_id
        -- Pending Orders for membership purchases of type: Renewal
    LEFT JOIN(
        SELECT CASE
            oim.meta_value WHEN 3148 THEN 1916 WHEN 3143 THEN 1914 WHEN 3050 THEN 1915
    END AS product_id,
    COUNT(oim.meta_value) AS pending_renewed
    FROM
        {$prefix_hsb}postmeta AS pm
    LEFT JOIN {$prefix_hsb}posts AS p
    ON
        pm.post_id = p.ID
    LEFT JOIN {$prefix_hsb}woocommerce_order_items oi ON
        oi.order_id = pm.post_id
    LEFT JOIN {$prefix_hsb}woocommerce_order_itemmeta oim ON
        oim.order_item_id = oi.order_item_id
    WHERE
        p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-pending' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3148, 3143, 3050)
    GROUP BY
        oim.meta_value
        ) pending2
    ON
        custom.product_id = pending2.product_id
        -- renewal product completed orders for last month JOIN:
    LEFT JOIN(
        SELECT CASE
            oim.meta_value WHEN 3148 THEN 1916 WHEN 3143 THEN 1914 WHEN 3050 THEN 1915
    END AS product_id,
    COUNT(oim.meta_value) AS completed_renewal
    FROM
        {$prefix_hsb}postmeta AS pm
    LEFT JOIN {$prefix_hsb}posts AS p
    ON
        pm.post_id = p.ID
    LEFT JOIN {$prefix_hsb}woocommerce_order_items oi ON
        oi.order_id = pm.post_id
    LEFT JOIN {$prefix_hsb}woocommerce_order_itemmeta oim ON
        oim.order_item_id = oi.order_item_id
              LEFT JOIN(
                    SELECT
                        post_id,
                        DATE(meta_value) AS paid_date
                    FROM
                        {$prefix_hsb}postmeta
                    WHERE
                        meta_key = '_paid_date'
                ) AS pm2_fil
                ON
                    pm.post_id = pm2_fil.post_id
    WHERE
        p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-pending' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3148, 3143, 3050) AND DATE(pm2_fil.paid_date) <= LAST_DAY(now() - INTERVAL 1 MONTH) AND DATE(pm2_fil.paid_date) >= LAST_DAY(now() - INTERVAL 2 MONTH) + INTERVAL 1 DAY
    GROUP BY
        oim.meta_value
        ) completed_renewals_last_month
    ON
        custom.product_id = completed_renewals_last_month.product_id
        -- renewal product completed orders for this month JOIN:
    LEFT JOIN(
        SELECT CASE
            oim.meta_value WHEN 3148 THEN 1916 WHEN 3143 THEN 1914 WHEN 3050 THEN 1915
    END AS product_id,
    COUNT(oim.meta_value) AS completed_renewal2
    FROM
        {$prefix_hsb}postmeta AS pm
    LEFT JOIN {$prefix_hsb}posts AS p
    ON
        pm.post_id = p.ID
    LEFT JOIN {$prefix_hsb}woocommerce_order_items oi ON
        oi.order_id = pm.post_id
    LEFT JOIN {$prefix_hsb}woocommerce_order_itemmeta oim ON
        oim.order_item_id = oi.order_item_id
              LEFT JOIN(
                    SELECT
                        post_id,
                        DATE(meta_value) AS paid_date
                    FROM
                        {$prefix_hsb}postmeta
                    WHERE
                        meta_key = '_paid_date'
                ) AS pm2_fil
                ON
                    pm.post_id = pm2_fil.post_id
    WHERE
        p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-pending' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3148, 3143, 3050) AND DATE(pm2_fil.paid_date) >= LAST_DAY(now() - INTERVAL 1 MONTH) + INTERVAL 1 DAY
    GROUP BY
        oim.meta_value
        ) completed_renewals_last_month2
    ON
        custom.product_id = completed_renewals_last_month2.product_id
        -- NEW or Expired product completed orders for last month JOIN:
    LEFT JOIN(
            SELECT CASE
                oim.meta_value WHEN 4220 THEN 1917 WHEN 3632 THEN 3966 WHEN 3151 THEN 1916 WHEN 3138 THEN 1914 WHEN 2574 THEN 1915
        END AS product_id,
        COUNT(oim.meta_value) AS completed_new_or_expired
    FROM
        {$prefix_hsb}postmeta AS pm
    LEFT JOIN {$prefix_hsb}posts AS p
    ON
        pm.post_id = p.ID
    LEFT JOIN {$prefix_hsb}woocommerce_order_items oi ON
        oi.order_id = pm.post_id
    LEFT JOIN {$prefix_hsb}woocommerce_order_itemmeta oim ON
        oim.order_item_id = oi.order_item_id
                LEFT JOIN(
                    SELECT
                        post_id,
                        DATE(meta_value) AS paid_date
                    FROM
                        {$prefix_hsb}postmeta
                    WHERE
                        meta_key = '_paid_date'
                ) AS pm2_fil
                ON
                    pm.post_id = pm2_fil.post_id
    WHERE
        p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-completed' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3138, 2574, 3632, 4220, 3151) AND DATE(pm2_fil.paid_date) <= LAST_DAY(now() - INTERVAL 1 MONTH) AND DATE(pm2_fil.paid_date) >= LAST_DAY(now() - INTERVAL 2 MONTH) + INTERVAL 1 DAY
    GROUP BY
        oim.meta_value
        ) AS completed_new_last_month
    ON
        custom.product_id = completed_new_last_month.product_id
    
        -- NEW or Expired product completed orders for this month JOIN:
    LEFT JOIN(
            SELECT CASE
                oim.meta_value WHEN 4220 THEN 1917 WHEN 3632 THEN 3966 WHEN 3151 THEN 1916 WHEN 3138 THEN 1914 WHEN 2574 THEN 1915
        END AS product_id,
        COUNT(oim.meta_value) AS completed_new_or_expired2
    FROM
        {$prefix_hsb}postmeta AS pm
    LEFT JOIN {$prefix_hsb}posts AS p
    ON
        pm.post_id = p.ID
    LEFT JOIN {$prefix_hsb}woocommerce_order_items oi ON
        oi.order_id = pm.post_id
    LEFT JOIN {$prefix_hsb}woocommerce_order_itemmeta oim ON
        oim.order_item_id = oi.order_item_id
                LEFT JOIN(
                    SELECT
                        post_id,
                        DATE(meta_value) AS paid_date
                    FROM
                        {$prefix_hsb}postmeta
                    WHERE
                        meta_key = '_paid_date'
                ) AS pm2_fil
                ON
                    pm.post_id = pm2_fil.post_id
    WHERE
        p.post_type = 'shop_order' AND pm.meta_key = '_customer_user' AND p.post_status = 'wc-completed' AND oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id' AND oim.meta_value IN(3138, 2574, 3632, 4220, 3151) AND DATE(pm2_fil.paid_date) >= LAST_DAY(now() - INTERVAL 1 MONTH) + INTERVAL 1 DAY
    GROUP BY
        oim.meta_value
        ) AS completed_new_last_month2
    ON
        custom.product_id = completed_new_last_month2.product_id
        -- TOTAL JOIN:
    LEFT JOIN(
        SELECT
            t1.product_id,
            COUNT(t1.product_id) AS total
        FROM
            {$prefix_hsb}mepr_transactions t1
        WHERE
            t1.created_at =(
            SELECT
                MAX(t2.created_at)
            FROM
                {$prefix_hsb}mepr_transactions t2
            WHERE
                t2.user_id = t1.user_id AND t2.status = 'complete'
        )
    GROUP BY
        t1.product_id
    ) AS joined1
    ON
        custom.product_id = joined1.product_id";
        $results = $wpdb->get_results($active_users_query, ARRAY_A);
        // print_r($results);
?>
        <?php if (!empty($results)): ?>
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
            <br/>
            <hr/>
        <?php endif; ?>
<?php 
    }
?>