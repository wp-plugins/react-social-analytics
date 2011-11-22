<?php
/*
 * Available variables:
 * 	$category	-	(string)category of the resource
 * 	$resourceUri-	(string)id of the resource
 * 	$likeUserId	-	(mixed)	user id or null if not logged in
 * 	$hasRated	-	(bool)	boolean indicating if logged user has rated the lemma
 * 	$count		-	(int)	total number of ratings
 * 	$countOthers-	(int)	number of ratings by other users
 */

if ($hasRated)
{
	if ($countOthers)
		$class = 1;
	else
		$class = 2;
}
else
{
	if ($countOthers)
		$class = 3;
	else
		$class = 4;
}
?>
	<p class="react-social-analytics-like-message">
		<span class="your-other-vote<?php if ($class == 1) echo " active";?>">
			<?php
				// $countOthers + 1 is the value after voting
				printf(_n('You and one other person have voted for this.', 'You and %2$d others have voted for this.', $countOthers, REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), $countOthers+1, $countOthers);
			?>
		</span>
		<span class="your-vote<?php if ($class == 2) echo " active";?>">
			<?php _e('You have voted for this.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);?>
		</span>
		<span class="other-vote<?php if ($class == 3) echo " active";?>">
			<?php printf(_n('One person has voted for this.', '%1$d people have voted for this.', $countOthers, REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), $countOthers);?>
		</span>
		<span class="no-vote<?php if ($class == 4) echo " active";?>">
			<?php _e('Be the first to vote.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);?>
		</span>
	</p>

	<?php
	if ($likeUserId)
	{
		if (!$hasRated)
		{
	?>
		<form action="" method="post" class="react-social-analytics-like-form">
			<div>
				<?php wp_nonce_field('react-social-analytics-like-nonce'); ?>
				<input type="hidden" value="1" name="reactSocialAnalyticsLikePost" />
				<input type="hidden" value="<?php echo $category; ?>" name="category" />
				<input type="hidden" value="<?php echo $resourceUri; ?>" name="resourceUri" />
				<a href="javascript:;" class="react-social-analytics-like-link"><?php
					_e('Like', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN)
				?></a>
			</div>
		</form>

		<p class="react-social-analytics-like-success-message"><?php
			_e('Thank you.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
		?></p>
	<?php
		}
	}
	else
	{
	?>
		<p class="react-social-analytics-like-not-logged-in-message"><?php
			printf(__('Please <a href="%s">log in</a> to vote.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), wp_login_url());
		?></p>
		<a href="javascript:;" class="react-social-analytics-like-link need-login" title="<?php
			_e('Please log in to vote', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) ?>"><?php
			_e('Like', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
		?></a>
	<?php
	}
	?>
