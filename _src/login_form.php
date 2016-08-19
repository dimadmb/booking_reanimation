<style type="text/css">

	#login-form .control-group{
		display: inline-block;
	}

	#login-form {

		float : right;;

		margin-bottom: 0px;
		margin-top: 5px;;
		margin-right: 5px;;
	}

</style>

<?php

$url = JUri::getInstance()->toString();
$return = base64_encode($url);
?>

<form action="<?php echo JRoute::_(htmlspecialchars(JUri::getInstance()->toString()), true, false
//	$params->get('usesecure')
); ?>" method="post" id="login-form" class="form-inline">
<!--	--><?php //if ($params->get('pretext')) : ?>
<!--	<div class="pretext">-->
<!--		<p>--><?php //echo $params->get('pretext'); ?><!--</p>-->
<!--	</div>-->
<?php //endif; ?>
<div class="userdata">
	<div id="form-login-username" class="control-group">
		<div class="controls">
<!--			--><?php //if (!$params->get('usetext')) : ?>
				<div class="input-prepend">
						<span class="add-on">
							<i class="fa fa-envelope" aria-hidden="true"></i>

							<!--							<span class="icon-user hasTooltip" title="--><?php //echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?><!--"></span>-->
							<label for="modlgn-username" class="element-invisible"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
						</span>
					<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="Email" />
				</div>
<!--			--><?php //else: ?>
<!--				<label for="modlgn-username">--><?php //echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?><!--</label>-->
<!--				<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="--><?php //echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?><!--" />-->
<!--			--><?php //endif; ?>
		</div>
	</div>

	<div id="form-login-password" class="control-group">
		<div class="controls">
<!--			--><?php //if (!$params->get('usetext')) : ?>
				<div class="input-prepend">
						<span class="add-on">

							<i class="fa fa-key" aria-hidden="true"></i>

<!--							<span class="icon-lock hasTooltip" title="--><?php //echo JText::_('JGLOBAL_PASSWORD') ?><!--">-->
<!--							</span>-->
								<label for="modlgn-passwd" class="element-invisible"><?php echo JText::_('JGLOBAL_PASSWORD'); ?>
								</label>
						</span>
					<input id="modlgn-passwd" type="password" name="password" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
				</div>
<!--			--><?php //else: ?>
<!--				<label for="modlgn-passwd">--><?php //echo JText::_('JGLOBAL_PASSWORD') ?><!--</label>-->
<!--				<input id="modlgn-passwd" type="password" name="password" class="input-small" tabindex="0" size="18" placeholder="--><?php //echo JText::_('JGLOBAL_PASSWORD') ?><!--" />-->
<!--			--><?php //endif; ?>
		</div>
	</div>

<!--	--><?php //if (JPluginHelper::isEnabled('system', 'remember')) : ?>
<!--		<div id="form-login-remember" class="control-group checkbox">-->
<!--			<label for="modlgn-remember" class="control-label">--><?php //echo JText::_('MOD_LOGIN_REMEMBER_ME') ?><!--</label> <input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>-->
<!--		</div>-->
<!--	--><?php //endif; ?>
	<div id="form-login-submit" class="control-group">
		<div class="controls">
			<button type="submit" tabindex="0" name="Submit" class="uk-button uk-button-primary">
				<i class="fa fa-sign-in" aria-hidden="true"></i> <?php echo JText::_('JLOGIN') ?>
			</button>
		</div>
	</div>
	<?php
	$usersConfig = JComponentHelper::getParams('com_users'); ?>
<!--	<ul class="unstyled">-->
<!--		--><?php //if ($usersConfig->get('allowUserRegistration')) : ?>
<!--			<li>-->
<!--				<a href="--><?php //echo JRoute::_('index.php?option=com_users&view=registration&Itemid=' . UsersHelperRoute::getRegistrationRoute()); ?><!--">-->
<!--					--><?php //echo JText::_('MOD_LOGIN_REGISTER'); ?><!-- <span class="icon-arrow-right"></span></a>-->
<!--			</li>-->
<!--		--><?php //endif; ?>
<!--		<li>-->
<!--			<a href="--><?php //echo JRoute::_('index.php?option=com_users&view=remind&Itemid=' . UsersHelperRoute::getRemindRoute()); ?><!--">-->
<!--				--><?php //echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?><!--</a>-->
<!--		</li>-->
<!--		<li>-->
<!--			<a href="--><?php //echo JRoute::_('index.php?option=com_users&view=reset&Itemid=' . UsersHelperRoute::getResetRoute()); ?><!--">-->
<!--				--><?php //echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?><!--</a>-->
<!--		</li>-->
<!--	</ul>-->
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
<?php //if ($params->get('posttext')) : ?>
<!--	<div class="posttext">-->
<!--		<p>--><?php //echo $params->get('posttext'); ?><!--</p>-->
<!--	</div>-->
<?php //endif; ?>
</form>
