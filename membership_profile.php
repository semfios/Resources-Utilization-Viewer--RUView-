<?php
	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");

	/* no access for guests */
	$mi = getMemberInfo();
	if(!$mi['username'] || $mi['group'] == $adminConfig['anonymousGroup']){
		@header('Location: index.php'); exit;
	}

	/* save profile */
	if($_POST['action'] == 'saveProfile'){
		/* process inputs */
		$email=isEmail($_POST['email']);
		$custom1=makeSafe($_POST['custom1']);
		$custom2=makeSafe($_POST['custom2']);
		$custom3=makeSafe($_POST['custom3']);
		$custom4=makeSafe($_POST['custom4']);

		/* validate email */
		if(!$email){
			echo "{$Translation['error:']} {$Translation['email invalid']}";
			echo "<script>$$('label[for=\"email\"]')[0].pulsate({ pulses: 10, duration: 4 }); $('email').activate();</script>";
			exit;
		}

		/* update profile */
		$updateDT = date($adminConfig['PHPDateTimeFormat']);
		sql("UPDATE `membership_users` set email='$email', custom1='$custom1', custom2='$custom2', custom3='$custom3', custom4='$custom4', comments=CONCAT_WS('\\n', comments, 'member updated his profile on $updateDT from IP address {$mi[IP]}') WHERE memberID='{$mi['username']}'", $eo);

		// hook: member_activity
		if(function_exists('member_activity')){
			$args=array();
			member_activity($mi, 'profile', $args);
		}

		exit;
	}

	/* change password */
	if($_POST['action'] == 'changePassword' && $mi['username'] != $adminConfig['adminUsername']){
		/* process inputs */
		$oldPassword=$_POST['oldPassword'];
		$newPassword=$_POST['newPassword'];

		/* validate password */
		if(md5($oldPassword) != sqlValue("SELECT `passMD5` FROM `membership_users` WHERE memberID='{$mi['username']}'")){
			echo "{$Translation['error:']} {$Translation['Wrong password']}";
			echo "<script>$$('label[for=\"old-password\"]')[0].pulsate({ pulses: 10, duration: 4 }); $('old-password').activate();</script>";
			exit;
		}
		if(strlen($newPassword) < 4){
			echo "{$Translation['error:']} {$Translation['password invalid']}";
			echo "<script>$$('label[for=\"new-password\"]')[0].pulsate({ pulses: 10, duration: 4 }); $('new-password').activate();</script>";
			exit;      
		}

		/* update password */
		$updateDT = date($adminConfig['PHPDateTimeFormat']);
		sql("UPDATE `membership_users` set `passMD5`='".md5($newPassword)."', `comments`=CONCAT_WS('\\n', comments, 'member changed his password on $updateDT from IP address {$mi[IP]}') WHERE memberID='{$mi['username']}'", $eo);

		// hook: member_activity
		if(function_exists('member_activity')){
			$args=array();
			member_activity($mi, 'password', $args);
		}

		exit;
	}

	/* get profile info */
	/* 
		$mi already contains the profile info, as documented at: 
		http://bigprof.com/appgini/help/working-with-generated-web-database-application/hooks/memberInfo

		custom field names are stored in $adminConfig['custom1'] to $adminConfig['custom4']
	*/
	$permissions = array();
	$userTables = getTableList();
	if(is_array($userTables))  foreach($userTables as $tn => $tc){
		$permissions[$tn] = getTablePermissions($tn);
	}

	/* the profile page view */
	include("$currDir/header.php"); ?>

	<style>
		#content {font-family: arial; font-size: large;}
		#content input{font-family: arial; font-size: large; color: navy;}
		#content legend{font-size: x-large; color: navy; padding: 0 5px;}
		#content fieldset{border-radius: 10px; border: solid 1px silver; margin: 0 10px 10px; padding: 10px;}
		#content fieldset:hover{background-color: lightyellow;}
		#content input:focus{background-color: lightgoldenrodyellow;}
		#content label{display: block; cursor: pointer; margin-top: 0.75em;}
		#permissions span{display: block; float: left; width: 17em; margin: 0 0.5em;}
		#notify,#loader{ width: 60%; margin: 10px auto; text-align: center; border: solid 1px Green; padding: 3px; border-radius: 3px;}
		#notify{background-color: LightGreen;}
		#loader{background-color: LightYellow;}
	</style>

	<div id="content" style="max-width: 94%; margin: 20px auto;">
		<h1><?php echo sprintf($Translation['Hello user'], $mi['username']); ?></h1>

		<div id="notify" style="display: none;"></div>
		<div id="loader" style="display: none;"><img src="loading.gif" align="top" hspace="3" /> <?php echo $Translation['Loading ...']; ?></div>

		<div id="left-column" style="margin: 0 10px; width: 29em; float: left;">

			<fieldset id="profile">
				<legend><?php echo $Translation['Your info']; ?></legend>

				<label for="email"><?php echo $Translation['email']; ?></label>
				<input type="text" id="email" name="email" value="<?php echo $mi['email']; ?>" size="50" />

				<?php for($i=1; $i<5; $i++){ ?>
					<label for="custom<?php echo $i; ?>"><?php echo $adminConfig['custom'.$i]; ?></label>
					<input type="text" id="custom<?php echo $i; ?>" name="custom<?php echo $i; ?>" value="<?php echo $mi['custom'][$i-1]; ?>" size="50" />
				<?php } ?>

				<div class="buttons" style="float: right; margin-top: 10px;">
					<button id="update-profile" class="positive" type="button"><img src="update.gif"><?php echo $Translation['Update profile']; ?></button>
				</div>
			</fieldset>

			<fieldset id="permissions">
				<legend><?php echo $Translation['Your access permissions']; ?></legend>

				<div style="font-size: small; line-height: 1.5em;">
					<div><strong><?php echo $Translation['Legend']; ?></strong></div>
					<span><img src="admin/images/stop_icon.gif" hspace="3" align="top" /><?php echo $Translation['Not allowed']; ?></span>
					<span><img src="admin/images/member_icon.gif" hspace="3" align="top" /><?php echo $Translation['Only your own records']; ?></span>
					<span><img src="admin/images/members_icon.gif" hspace="3" align="top" /><?php echo $Translation['All records owned by your group']; ?></span>
					<span><img src="admin/images/approve_icon.gif" hspace="3" align="top" /><?php echo $Translation['All records']; ?></span>
				</div>

				<table width="100%">
					<tr>
						<td class="TableHeader"><?php echo $Translation['Table']; ?></td>
						<td class="TableHeader"><?php echo $Translation['View']; ?></td>
						<td class="TableHeader"><?php echo $Translation['Add New']; ?></td>
						<td class="TableHeader"><?php echo $Translation['Edit']; ?></td>
						<td class="TableHeader"><?php echo $Translation['Delete']; ?></td>
					</tr>

					<?php foreach($permissions as $tn => $perm){ ?>
						<tr class="colorize">
							<td class="TableHeader"><img src="<?php echo $userTables[$tn][2]; ?>" hspace="3" align="top" /><a href="<?php echo $tn; ?>_view.php"><?php echo $userTables[$tn][0]; ?></a></td>
							<td class="TableBody" style="text-align: center;"><img src="admin/images/<?php echo permIcon($perm[2]); ?>" /></td>
							<td class="TableBody" style="text-align: center;"><img src="admin/images/<?php echo ($perm[1] ? 'approve' : 'stop'); ?>_icon.gif" /></td>
							<td class="TableBody" style="text-align: center;"><img src="admin/images/<?php echo permIcon($perm[3]); ?>" /></td>
							<td class="TableBody" style="text-align: center;"><img src="admin/images/<?php echo permIcon($perm[4]); ?>" /></td>
						</tr>
					<?php } ?>
				</table>
			</fieldset>

		</div>

		<div id="right-column" style="margin: 0 10px; width: 29em; float: left;">

			<fieldset id="ip-address">
				<legend><?php echo $Translation['Your IP address']; ?></legend>
				<strong><?php echo $mi['IP']; ?></strong>
			</fieldset>

			<fieldset id="group">
				<legend><?php echo $Translation['group']; ?></legend>
				<strong><?php echo $mi['group']; ?></strong>
			</fieldset>

			<?php if($mi['username'] != $adminConfig['adminUsername']){ ?>
				<fieldset id="change-password">
					<legend><?php echo $Translation['Change your password']; ?></legend>

					<div id="password-change-form">
						<label for="old-password"><?php echo $Translation['Old password']; ?></label>
						<input type="password" id="old-password" size="20" />

						<label for="new-password"><?php echo $Translation['new password']; ?></label>
						<input type="password" id="new-password" size="20" />
						<span id="password-strength" style="font-size: small;"></span>

						<label for="confirm-password"><?php echo $Translation['confirm password']; ?></label>
						<input type="password" id="confirm-password" size="20" />
						<span id="confirm-status"></span>

						<div class="buttons" style="float: right; margin-top: 10px;">
							<button id="update-password" class="positive" type="button" style="width: 160px;"><img src="update.gif"><?php echo $Translation['Update password']; ?></button>
						</div>
					</div>
				</fieldset>
			<?php }?>
		</div>

	</div>


	<script>
		document.observe("dom:loaded", function() {
			if('<?php echo addslashes($_GET['notify']); ?>' != '') notify('<?php echo addslashes($_GET['notify']); ?>');

			$('update-profile').observe('click', function(){
				post2(
					'<?php echo basename(__FILE__); ?>',
					{ action: 'saveProfile', email: $F('email'), custom1: $F('custom1'), custom2: $F('custom2'), custom3: $F('custom3'), custom4: $F('custom4') },
					'notify', 'profile', 'loader', 
					'<?php echo basename(__FILE__); ?>?notify=<?php echo urlencode($Translation['Your profile was updated successfully']); ?>'
				);
			});

			<?php if($mi['username'] != $adminConfig['adminUsername']){ ?>
				$('update-password').observe('click', function(){
					/* make sure passwords match */
					if($F('new-password') != $F('confirm-password')){
						$('notify').addClassName('Error');
						notify('<?php echo "{$Translation['error:']} ".addslashes($Translation['password no match']); ?>');
						$$('label[for="confirm-password"]')[0].pulsate({ pulses: 10, duration: 4 });
						$('confirm-password').activate();
						return false;
					}

					post2(
						'<?php echo basename(__FILE__); ?>',
						{ action: 'changePassword', oldPassword: $F('old-password'), newPassword: $F('new-password') },
						'notify', 'password-change-form', 'loader', 
						'<?php echo basename(__FILE__); ?>?notify=<?php echo urlencode($Translation['Your password was changed successfully']); ?>'
					);
				});

				/* password strength feedback */
				$('new-password').observe('keyup', function(){
					ps = passwordStrength($F('new-password'), '<?php echo addslashes($mi['username']); ?>');

					if(ps == 'strong')
						$('password-strength').update('<?php echo $Translation['Password strength: strong']; ?>').setStyle({color: 'Green'});
					else if(ps == 'good')
						$('password-strength').update('<?php echo $Translation['Password strength: good']; ?>').setStyle({color: 'Gold'});
					else
						$('password-strength').update('<?php echo $Translation['Password strength: weak']; ?>').setStyle({color: 'Red'});
				});

				/* inline feedback of confirm password */
				$('confirm-password').observe('keyup', function(){
					if($F('confirm-password') != $F('new-password') || !$F('confirm-password').length){
						$('confirm-status').update('<img align="top" src="Exit.gif"/>');
					}else{
						$('confirm-status').update('<img align="top" src="Update.gif"/>');
					}
				});
			<?php } ?>
		});

		function notify(msg){
			$('notify').update(msg).appear();
			window.setTimeout(function(){ $('notify').fade(); }, 15000);
		}
	</script>

	<?php
		/* return icon file name based on given permission value */
		function permIcon($perm){
			switch($perm){
				case 1:
					return 'member_icon.gif';
				case 2:
					return 'members_icon.gif';
				case 3:
					return 'approve_icon.gif';
				default:
					return 'stop_icon.gif';
			}
		}
	?>

	<?php include("$currDir/footer.php"); ?>