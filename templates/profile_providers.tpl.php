<h3><?php _e('Connected social network providers', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?></h3>
<?php
if (count($providers) == 0)
{
?>
<p>Not connected to any networks.</p>

<?php
}
else
{
?>
	<table class="form-table">
	<?php
	foreach ($providers as $p)
	{
		$p = htmlspecialchars($p);
	?>
		<tr>
			<th><?php print $p; ?></th>
			<td>
				<input type="checkbox" id="react-social-analytics-<?php print $p; ?>" name="react-social-analytics[<?php print $p; ?>]" />
				<label for="react-social-analytics-<?php print $p; ?>"> <?php printf(__('Remove connection with %s', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), $p); ?></label>
			</td>
		</tr>

	<?php
	}
	?>
		<tr>
			<th></th>
			<td>
				<span class="description">
					<?php _e('After removing the connection with a network you can no longer login to your account using that network.<br />To fully remove the connection between a network and this site, login to the social network site and revoke this site\'s access.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?>
				</span>
			</td>
		</tr>
	</table>

<?php
}
?>