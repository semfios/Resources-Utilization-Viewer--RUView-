<?php
	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");
	include("$currDir/header.php");

	if($_POST['signUp']!=''){
		// receive data
		$memberID=makeSafe(strtolower($_POST['newUsername']));
		$email=isEmail($_POST['email']);
		$password=$_POST['password'];
		$confirmPassword=$_POST['confirmPassword'];
		$groupID=intval($_POST['groupID']);
		$custom1=makeSafe($_POST['custom1']);
		$custom2=makeSafe($_POST['custom2']);
		$custom3=makeSafe($_POST['custom3']);
		$custom4=makeSafe($_POST['custom4']);

		// validate data
		if($memberID==''){
			?><div class="Error"><?php echo $Translation['username empty']; ?></div><?php
			exit;
		}
		if(strlen($password)<4 || trim($password)!=$password){
			?><div class="Error"><?php echo $Translation['password invalid']; ?></div><?php
			exit;
		}
		if($password!=$confirmPassword){
			?><div class="Error"><?php echo $Translation['password no match']; ?></div><?php
			exit;
		}
		if(sqlValue("select count(1) from membership_users where lcase(memberID)='$memberID'")){
			?><div class="Error"><?php echo $Translation['username exists']; ?></div><?php
			exit;
		}
		if(!$email){
			?><div class="Error"><?php echo $Translation['email invalid']; ?></div><?php
			exit;
		}
		if(!sqlValue("select count(1) from membership_groups where groupID='$groupID' and allowSignup=1")){
			?><div class="Error"><?php echo $Translation['group invalid']; ?></div><?php
			exit;
		}

		// save member data
		$needsApproval=sqlValue("select needsApproval from membership_groups where groupID='$groupID'");
		sql("INSERT INTO `membership_users` set memberID='$memberID', passMD5='".md5($password)."', email='$email', signupDate='".@date('Y-m-d')."', groupID='$groupID', isBanned='0', isApproved='".($needsApproval==1 ? '0' : '1')."', custom1='$custom1', custom2='$custom2', custom3='$custom3', custom4='$custom4', comments='member signed up through the registration form.'", $eo);

		// admin mail notification
		if($adminConfig['notifyAdminNewMembers']==2 && !$needsApproval){
			@mail($adminConfig['senderEmail'], '[resources_utilization] New member signup', "A new member has signed up for resources_utilization.\n\nMember name: $memberID\nMember group: ".sqlValue("select name from membership_groups where groupID='$groupID'")."\nMember email: $email\nIP address: {$_SERVER['REMOTE_ADDR']}\nCustom fields:\n" . ($adminConfig['custom1'] ? "{$adminConfig['custom1']}: $custom1\n" : '') . ($adminConfig['custom2'] ? "{$adminConfig['custom2']}: $custom2\n" : '') . ($adminConfig['custom3'] ? "{$adminConfig['custom3']}: $custom3\n" : '') . ($adminConfig['custom4'] ? "{$adminConfig['custom4']}: $custom4\n" : ''), "From: {$adminConfig['senderEmail']}\r\n\r\n");
		}elseif($adminConfig['notifyAdminNewMembers']>=1 && $needsApproval){
			@mail($adminConfig['senderEmail'], '[resources_utilization] New member awaiting approval', "A new member has signed up for resources_utilization.\n\nMember name: $memberID\nMember group: ".sqlValue("select name from membership_groups where groupID='$groupID'")."\nMember email: $email\nIP address: {$_SERVER['REMOTE_ADDR']}\nCustom fields:\n" . ($adminConfig['custom1'] ? "{$adminConfig['custom1']}: $custom1\n" : '') . ($adminConfig['custom2'] ? "{$adminConfig['custom2']}: $custom2\n" : '') . ($adminConfig['custom3'] ? "{$adminConfig['custom3']}: $custom3\n" : '') . ($adminConfig['custom4'] ? "{$adminConfig['custom4']}: $custom4\n" : ''), "From: {$adminConfig['senderEmail']}\r\n\r\n");
		}

		// hook: member_activity
		if(function_exists('member_activity')){
			$args=array();
			member_activity(getMemberInfo($memberID), ($needsApproval ? 'pending' : 'automatic'), $args);
		}

		// redirect to thanks page
		$redirect=($needsApproval ? '' : '?redir=1');
		redirect("membership_thankyou.php$redirect");

		// exit
		exit;
	}

	if(!$cg=sqlValue("select count(1) from membership_groups where allowSignup=1")){
		$noSignup=TRUE;
		?>
		<div class="Error"><?php echo $Translation['sign up disabled']; ?></div>
		<?php
	}

	// drop-down of groups allowing self-signup
	$groupsDropDown = preg_replace('/<option.*?value="".*?><\/option>/i', '', htmlSQLSelect('groupID', "select groupID, concat(name, if(needsApproval=1, ' *', ' ')) from membership_groups where allowSignup=1 order by name", ($cg==1 ? sqlValue("select groupID from membership_groups where allowSignup=1 order by name limit 1") : 0 )));
?>

<?php if(!$noSignup){ ?>
	<div style="margin: 0 auto; width: 550px;">
		<form method="post" action="membership_signup.php" onSubmit="return jsValidateSignup();" id="login-form">
			<h1 class="buttons">
				<?php echo $Translation['sign up here']; ?>
				<a href="index.php?signIn=1"><?php echo $Translation['sign in']; ?></a>
			</h1>
			<fieldset id="inputs">
				<label for="username"><?php echo $Translation['username']; ?></label>
				<input type="text" required="" placeholder="<?php echo $Translation['username']; ?>" id="username" name="newUsername">
				<span id="usernameAvailable" style="display: none;"><img title="<?php echo str_ireplace(array("'", '"', '<memberid>'), '', $Translation['user available']); ?>" src="update.gif" /></span>
				<span id="usernameNotAvailable" style="display: none;"><img title="<?php echo str_ireplace(array("'", '"', '<memberid>'), '', $Translation['username exists']); ?>" src="delete.gif" /></span>

				<div style="float: left;">
					<label for="password"><?php echo $Translation['password']; ?></label>
					<input style="width: 200px;" type="password" required="" placeholder="<?php echo $Translation['password']; ?>" id="password" name="password">
				</div>
				<div style="float: right;">
					<label for="confirmPassword"><?php echo $Translation['confirm password']; ?></label>
					<input style="width: 200px;" type="password" required="" placeholder="<?php echo $Translation['confirm password']; ?>" id="confirmPassword" name="confirmPassword">
				</div>
				<div style="clear: both;"></div>

				<label for="email"><?php echo $Translation['email']; ?></label>
				<input type="text" required="" placeholder="<?php echo $Translation['email']; ?>" id="email" name="email">

				<label for="group"><?php echo $Translation['group']; ?></label>
				<?php echo $groupsDropDown; ?>
				<label><?php echo $Translation['groups *']; ?></label><br/>

				<?php
					for($cf = 1; $cf <= 4; $cf++){
						if($adminConfig['custom'.$cf] != ''){
							?>
							<label for="custom<?php echo $cf; ?>"><?php echo $adminConfig['custom'.$cf]; ?></label>
							<input type="text" placeholder="<?php echo $adminConfig['custom'.$cf]; ?>" id="custom<?php echo $cf; ?>" name="custom<?php echo $cf; ?>">
							<?php
						}
					}
				?>

				<div class="buttons"><button class="positive" value="signUp" id="submit" type="submit" name="signUp"><?php echo $Translation['sign up']; ?></button></div>
			</fieldset>
		</form>
	</div>

	<script>
		document.observe("dom:loaded", function() {
			$('username').focus();

			$$('#usernameAvailable, #usernameNotAvailable').invoke('observe', 'click', function(){ $('username').focus(); });

			$('username').observe('keyup', function(){
				if($F('username').length >= 4){
					checkUser();
				}
			});

			$('username').observe('blur', function(){
				checkUser();
			});

			/* password strength feedback */
			$('password').observe('keyup', function(){
				ps = passwordStrength($F('password'), $F('username'));

				if(ps == 'strong'){
					$('password').removeClassName('redBG').removeClassName('yellowBG').addClassName('greenBG');
					$('password').title = '<?php echo htmlspecialchars($Translation['Password strength: strong']); ?>';
				}else if(ps == 'good'){
					$('password').removeClassName('redBG').removeClassName('greenBG').addClassName('yellowBG');
					$('password').title = '<?php echo htmlspecialchars($Translation['Password strength: good']); ?>';
				}else{
					$('password').removeClassName('greenBG').removeClassName('yellowBG').addClassName('redBG');
					$('password').title = '<?php echo htmlspecialchars($Translation['Password strength: weak']); ?>';
				}
			});

			/* inline feedback of confirm password */
			$('confirmPassword').observe('keyup', function(){
				if($F('confirmPassword') != $F('password') || !$F('confirmPassword').length){
					$('confirmPassword').removeClassName('greenBG').addClassName('redBG');
				}else{
					$('confirmPassword').removeClassName('redBG').addClassName('greenBG');
				}
			});

			/* inline feedback of email */
			$('email').observe('change', function(){
				if(validateEmail($F('email'))){
					$('email').removeClassName('redBG').addClassName('greenBG');
				}else{
					$('email').removeClassName('greenBG').addClassName('redBG');
				}
			});
		});

		var uaro; // user availability request object
		function checkUser(){
			// abort previous request, if any
			if(uaro != undefined) uaro.transport.abort();

			uaro = new Ajax.Request(
				'checkMemberID.php', {
					method: 'get',
					parameters: { 'memberID': $F('username') },
					onCreate: function(){
						$('usernameAvailable').hide();
						$('usernameNotAvailable').hide();
					},
					onSuccess: function(resp){
						var ua=resp.responseText;
						if(ua.match(/\<!-- AVAILABLE --\>/)){
							$('usernameAvailable').style.display='inline';
						}else{
							$('usernameNotAvailable').style.display='inline';
						}
					}
				}
			);
		}

		/* validate data before submitting */
		function jsValidateSignup(){
			var p1 = $F('password');
			var p2 = $F('confirmPassword');
			var user = $F('username');
			var email = $F('email');

			/* passwords not matching? */
			if(p1 != p2){
				Modalbox.show('<div class="Error" style="width: 90%; margin: 0;"><?php echo addslashes($Translation['password no match']); ?></div>', { title: "<?php echo addslashes($Translation['error:']); ?>", afterHide: function(){ $('confirmPassword').focus(); } });
				return false;
			}

			/* user exists? */
			if($('usernameNotAvailable').visible()){
				Modalbox.show('<div class="Error" style="width: 90%; margin: 0;"><?php echo addslashes($Translation['username exists']); ?></div>', { title: "<?php echo addslashes($Translation['error:']); ?>", afterHide: function(){ $('username').focus(); } });
				return false;
			}

			return true;
		}

	</script>

	<style>
		#login-form{ width: 500px; }
		#email,#custom1,#custom2,#custom3,#custom4{ width: 450px !important; }
		#usernameAvailable,#usernameNotAvailable{ cursor: pointer; }
		.greenBG{ border-color: Green !important; background-color: LightGreen !important; }
		.yellowBG{ border-color: Gold !important; background-color: LightYellow !important; }
		.redBG{ border-color: Red !important; background-color: LighRed !important; }
	</style>

<?php } ?>

<?php include("$currDir/footer.php"); ?>