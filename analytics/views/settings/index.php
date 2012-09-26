<?php if (validation_errors()) : ?>
<div class="alert alert-block alert-error fade in">
  <a class="close" data-dismiss="alert">&times;</a>
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>

<div class="admin-box">
	<h3>Google Analytics Settings</h3>

<?php echo form_open($this->uri->uri_string(), 'class="ajax-form form-horizontal"'); ?>
  <fieldset>
    <legend>Google analytics API <em style="color: #aaa;font-weight:normal;">must be defined to use on site reports</em></legend>

		<div class="control-group <?php echo form_has_error('ga_username') ? 'error' : ''; ?>">
			<label class="control-label" for="ga_username">Analytics username<span class="required">*</span></label>
			<div class='controls'>
				<input id="ga_username" type="text" name="ga_username" maxlength="100" value="<?php echo set_value('ga_username', isset($settings['ga_username']) ? $settings['ga_username'] : ''); ?>">
				<span class="help-inline"><?php echo form_error('ga_username'); ?></span>
			</div>
		</div>

		<div class="control-group <?php echo form_has_error('ga_new_password') ? 'error' : ''; ?>">
			<label class="control-label" for="ga_new_password">Analytics password<span class="required">*</span></label>
			<div class='controls'>
				<input id="ga_password" type="hidden" name="ga_password" value="<?php echo set_value('ga_password', isset($settings['ga_password']) ? $settings['ga_password'] : ''); ?>" />
				<input id="ga_new_password" type="password" name="ga_new_password" maxlength="100" >
				<span class="help-inline"><?php echo form_error('ga_new_password'); ?></span>
			</div>
		</div>

				<div class="control-group <?php echo form_has_error('ga_code') ? 'error' : ''; ?>">
			<label class="control-label" for="ga_code">Analytics code<span class="required">*</span></label>
			<div class='controls'>
				<input id="ga_code" type="text" name="ga_code" maxlength="100" value="<?php echo set_value('ga_code', isset($settings['ga_code']) ? $settings['ga_code'] : ''); ?>">
				<span class="help-inline"><?php echo form_error('ga_code'); ?></span>
			</div>
		</div>

		<div class="control-group <?php echo form_has_error('ga_enabled') ? 'error' : ''; ?>">
			<label class="control-label" for="ga_enabled">Enabled<span class="required">*</span></label>
			<div class='controls'>
				<?php echo form_dropdown('ga_enabled', array('0'=>'No','1'=>'Yes'), set_value('ga_enabled', ( ( !empty($settings['ga_enabled']) ) ? $settings['ga_enabled'] : 0 ) ), 'id="ga_enabled" class="span2"'); ?>
				<span class="help-inline"><?php echo form_error('ga_enabled'); ?></span>
			</div>
		</div>

		<div class="control-group <?php echo form_has_error('ga_profile') ? 'error' : ''; ?>">
			<label class="control-label" for="ga_profile">Profile ID<span class="required">*</span></label>
			<div class='controls'>
				<input id="ga_profile" type="text" name="ga_profile" maxlength="100" value="<?php echo set_value('ga_profile', isset($settings['ga_profile']) ? $settings['ga_profile'] : ''); ?>">
				<span class="help-inline"><?php echo form_error('ga_profile'); ?></span>
			</div>
		</div>


	<div class="form-actions">
		<br>
		<input type="submit" name="submit" class="btn btn-primary" value="Save Settings">
 </div>


		</fieldset>

<?php echo form_close()?>

</div>
