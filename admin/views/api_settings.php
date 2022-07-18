<?php
/** @var $arrayKeys wp-content\plugin\Lovat\lovat-admin\lovat_settings_page */
/** @var $arrayCountries wp-content\plugin\Lovat\lovat-admin\lovat_settings_page */
/** @var $lovatData wp-content\plugin\Lovat\lovat-admin\lovat_settings_page */
?>

<div id="wrap-lovat">
	<?php if (!is_null(self::show_warning_message($user->ID))): ?>
		<?php echo self::show_warning_message() ?>
	<?php endif; ?>
	<?php if (!is_null(self::show_success_message())): ?>
		<?php echo self::show_success_message() ?>
	<?php endif; ?>
	<?php if (!is_null(self::show_error_message())): ?>
		<?php echo self::show_error_message() ?>
	<?php endif; ?>
    <div class="button-generate">
        <form name="save_settings" method="post">
            <button name="generate-key" class="button-primary admin-generate-key-button" type="submit"
                    value="<?php esc_attr_e('Generate key', 'lovat'); ?>"><?php esc_html_e('Generate key', 'lovat'); ?></button>
        </form>
    </div>

    <div class="departure-address lovat-white-block">
        <form name="save-lovat-departure-country" method="post">
            <label for="departure-select-country">Please select a shipping country</label>
            <select id="departure-select-country" class="departure-select-country" name="departure-select-country">
                <option value="" selected disabled hidden>Choose the country of dispatch</option>
				<?php foreach ($arrayCountries as $key => $countries): ?>
					<?php if (!is_null($lovatData->country) && $lovatData->country == $key): ?>
                        <option value="<?php echo $key ?>" selected><?php echo $countries ?></option>
					<?php else: ?>
                        <option value="<?php echo $key ?>"><?php echo $countries ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
            </select>
            <br>
            <label for="departure_zip">Please enter a shipping zip</label>
            <input id="departure_zip" type="text" class="departure_zip" name="departure_zip" style="margin-top:10px;"
                   placeholder="44143"
                   value="<?php echo(!empty($lovatData->departureZip) ? $lovatData->departureZip : ''); ?>"/>
            <br>
            <input type="submit" class="button-primary lovat-generate-departure-country" name="save-departure-country"
                   value="Save">
        </form>
    </div>

    <div class="table-data-keys lovat-data-table lovat-white-block">
        <table id="lovat-api-generated-keys" class="display">
            <thead>
            <tr>
                <th>User ID</th>
                <th>Key</th>
                <th>Key creation date</th>
            </tr>
            </thead>
			<?php if (!empty($arrayKeys)): ?>
            <tbody>
			<?php foreach ($arrayKeys as $data): ?>
                <tr>
                    <td><?php echo $data->user_id ?></td>
                    <td><?php echo $data->token ?></td>
                    <td><?php echo $data->created ?></td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
		<?php else: ?>
            <h4>At the moment, there has not been a single Bearer Token key created. Please click on the button
                "Generate Key" to get access key to Lovat Api Requests</h4>
		<?php endif; ?>
    </div>
</div>

<script>
    jQuery('.departure_zip').keyup(function () {
        if (this.value >= 10) {
            this.value = this.value.substr(0, 10);
        }

        this.value = this.value.replace(/[^0-9\.]/g, '');

    });
</script>

