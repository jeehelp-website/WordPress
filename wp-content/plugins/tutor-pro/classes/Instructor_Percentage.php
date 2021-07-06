<?php

namespace TUTOR_PRO;

if (!defined('ABSPATH'))
    exit;

class Instructor_Percentage {
    private $amount = 'tutor_instructor_amount';
    private $amount_type = 'tutor_instructor_amount_type';

    private $amount_type_options = [
        'default',
        'fixed',
        'percent'
    ];

    function __construct() {
        add_filter('tutor_pro_earning_calculator', array($this, 'payment_percent_modifier'));
        add_filter('tutor_pro_instructor_commission_string', array($this, 'instructor_commission_string'));

        add_action('edit_user_profile', array($this, 'input_field_in_profile_setting'));
        add_action('edit_user_profile_update', array($this, 'save_input_data'));

        add_action('admin_enqueue_scripts', array($this, 'register_script'));
    }

    public function register_script() {
        if (strpos(($_SERVER['REQUEST_URI'] ?? ''), 'user-edit.php')) {
            // Load only if it user edit page
            wp_enqueue_script('instructor-percentage-manager-js', tutor_pro()->url . 'assets/js/instructor-rate.js');
        }
    }

    public function input_field_in_profile_setting($user) {

        if (!current_user_can('manage_options') || !tutor_utils()->is_instructor($user->ID)) {
            // Make sure only privileged user can cange payment percentage
            return;
        }

?>
        <h2><?php _e('Instructor Settings', 'tutor-pro'); ?></h2>
        <table class="form-table">
            <tr>
                <th>
                    <label><?php _e('Commission', 'tutor-pro'); ?></label>
                </th>
                <td>
                    <select id="tutor_pro_instructor_amount_type_field" name="<?php echo $this->amount_type; ?>">
                        <?php
                        $amount_type = get_the_author_meta($this->amount_type, $user->ID);
                        empty($amount_type) ? $amount_type = 'default' : 0;

                        foreach ($this->amount_type_options as $option) {
                            $selected = $amount_type == $option ? 'selected="selected"' : '';

                            echo '<option value="' . $option . '" ' . $selected . '>
                                            ' . ucfirst($option) . '
                                        </option>';
                        }
                        ?>
                    </select>
                    <input id="tutor_pro_instructor_amount_field" name="<?php echo $this->amount; ?>" type="number" value="<?php echo esc_attr(get_the_author_meta($this->amount, $user->ID)); ?>" style="position:relative;top:3px;"/>
                </td>
            </tr>
        </table>
<?php
    }

    public function save_input_data($user_id) {

        if (!current_user_can('manage_options')) {
            // Make sure only privileged user can cange payment percentage
            return;
        }

        $amount = $_POST[$this->amount];
        $type = $_POST[$this->amount_type];

        if (!is_numeric($amount) || $amount < 0 || ($type == 'percent' && $amount > 100)) {
            // Percentage can not be greater than 100 and less than 0
            return;
        }

        update_user_meta($user_id, $this->amount, $amount);
        update_user_meta($user_id, $this->amount_type, $type);
    }

    public function instructor_commission_string($user_id) {

        $type = get_the_author_meta($this->amount_type, $user_id);
        $amount = get_the_author_meta($this->amount, $user_id);

        return (($type == 'percent' || $type == 'fixed') ? $amount .' '. ucfirst($type) : __('Default', 'tutor-pro'));
    }

    public function payment_percent_modifier(array $data) {

        /* 
            '$data' must provide following array keys
            user_id
            instructor_rate
            admin_rate
            instructor_amount
            admin_amount
            course_price_grand_total
            commission_type 
        */

        extract($data);

        // $user_id is instructor ID
        $custom_amount = get_the_author_meta($this->amount, $user_id);
        $custom_type = get_the_author_meta($this->amount_type, $user_id);

        if (is_numeric($custom_amount) && $custom_amount >= 0) {
            if ($custom_type == 'fixed') {

                $commission_type = 'fixed';

                // Make sure custom amount is less than or equal to grand total
                $custom_amount > $course_price_grand_total ? $custom_amount = $course_price_grand_total : 0;

                // Set clculated amount
                $instructor_amount = $custom_amount;
                $admin_amount = $course_price_grand_total - $instructor_amount;
                $admin_amount < 0 ? $admin_amount = 0 : 0;

                // Set calculated rate
                $admin_rate = ($admin_amount / $course_price_grand_total) * 100;
                $instructor_rate = 100 - $admin_rate;
            } else if ($custom_type == 'percent') {

                $commission_type = 'percent';

                // Set calculated rate
                $instructor_rate = $custom_amount;
                $admin_rate = 100 - $instructor_rate;

                // Set calculated amount
                $instructor_amount = ($instructor_rate / 100) * $course_price_grand_total;
                $admin_amount = $course_price_grand_total - $instructor_amount;
            }
        }

        return [
            'user_id' => $user_id,
            'instructor_rate' => $instructor_rate,
            'admin_rate' => $admin_rate,
            'instructor_amount' => $instructor_amount,
            'admin_amount' => $admin_amount,
            'course_price_grand_total' => $course_price_grand_total,
            'commission_type' => $commission_type
        ];
    }
}
