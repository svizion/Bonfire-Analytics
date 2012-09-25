<style>
	.note { font-style:italic; font-weight:normal; }
</style>
<?php if (validation_errors()) : ?>
<div class='alert alert-block alert-error fade in'>
	<a class='close' data-dismiss='alert'>&times;</a>
	<h4 class='alert-heading'>Please fix the following errors:</h4>
	<?php echo validation_errors()?>
</div>
<?php endif; ?>
<div class='admin-box'>
	<h3>Google Analytics API <span class='note'>must be defined to use on site reports</span></h3>
	<?php echo form_open('admin/settings/analytics/edit', 'class="form-horizontal"')?>
		<fieldset>
			<div class="control-group<?php echo form_error('ga_username') ? ' error' : ''; ?>">
				<?php echo form_label('Google analytics username', 'ga_username', array('class' => 'control-label')); ?>
				<div class="controls">
					<input id="ga_username" type="text" name="ga_username" value="<?php echo set_value('ga_username', isset($ga_username) ? $ga_username : ''); ?>" />
					<span class="help-inline"><?php echo form_error('ga_username')?></span>
				</div>
			</div>
			<div class="control-group<?php echo form_error('ga_password') ? ' error' : ''; ?>">
				<?php echo form_label('Google analytics password', 'ga_password', array('class' => 'control-label')); ?>
				<div class="controls">
					<input id="ga_password" type="text" name="ga_password" value="<?php echo set_value('ga_password', isset($ga_password) ? $ga_password : ''); ?>" />
					<span class="help-inline"><?php echo form_error('ga_password')?></span>
				</div>
			</div>
			<div class="control-group<?php echo form_error('ga_code') ? ' error' : ''; ?>">
				<?php echo form_label('Google analytics code', 'ga_code', array('class' => 'control-label')); ?>
				<div class="controls">
					<input id="ga_code" type="text" name="ga_code" value="<?php echo set_value('ga_code', isset($ga_code) ? $ga_code : ''); ?>" />
					<span class="help-inline"><?php echo form_error('ga_code')?></span>
				</div>
			</div>
		</fieldset>
	<h3>Google Analytics</h3>
		<fieldset>
		<?php
			$ga_enabled_dropdown	= array('name' => 'ga_enabled', 'id' => 'ga_enabled',);
			$ga_enabled_vals		= array('0' => 'No', '1' => 'Yes',);
			echo form_dropdown($ga_enabled_dropdown, $ga_enabled_vals, set_value('ga_enabled', ( ( !empty($ga_enabled) ) ? $ga_enabled : 0 ) ), 'Enabled');
/*
echo form_dropdown('ga_enabled',array('0'=>'No','1'=>'Yes'),$ga_enabled,'id="ga_enabled"');
<input type="checkbox" name="ga_enabled" id="ga_enabled" value="1" <?php echo ( $ga_enabled == 1 ) ? 'checked="checked"' : set_checkbox('ga_enabled', 1); ?> />
*/
		?>
			<div class="control-group<?php echo form_error('ga_profile') ? ' error' : ''; ?>">
				<?php echo form_label('Profile ID', 'ga_profile', array('class' => 'control-label')); ?>
				<div class="controls">
					<input id="ga_profile" type="text" name="ga_profile" value="<?php echo set_value('ga_profile', isset($ga_profile) ? $ga_profile : ''); ?>" />
					<span class="help-inline"><?php echo form_error('ga_profile')?></span>
				</div>
			</div>
		</fieldset>
		<div class="form-actions">
			<input type="submit" name="submit" class='btn btn-primary' value="Save Settings" />
		</div>
	<?php echo form_close()?>
</div>