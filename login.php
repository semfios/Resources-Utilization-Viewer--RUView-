<?php if(!isset($Translation)){ @header('Location: index.php?signIn=1'); exit; } ?>
<?php include("$d/header.php"); ?>

<?php if($_GET['loginFailed']){ ?>
	<div class="Error"><?php echo $Translation['login failed']; ?></div>
<?php } ?>

<div id="login-splash">
	<!-- customized splash content here -->
</div>

<form id="login-form" method="post" action="index.php">
	<h1 class="buttons">
		<?php echo $Translation['sign in here']; ?>
		<a href="membership_signup.php"><?php echo $Translation['sign up']; ?></a>
	</h1>
	<fieldset id="inputs">
		<label for="username"><?php echo $Translation['username']; ?></label>
		<input name="username" id="username" type="text" placeholder="<?php echo $Translation['username']; ?>" required/>
		
		<label for="password"><?php echo $Translation['password']; ?></label>
		<input name="password" id="password" type="password" placeholder="<?php echo $Translation['password']; ?>" required style="margin: 0;" />
		<input type="checkbox" name="rememberMe" id="rememberMe" value="1"> <label for="rememberMe" style="display: inline;"><?php echo $Translation['remember me']; ?></label>
		<label style="float: right;"><?php echo $Translation['forgot password']; ?></label>
		<div style="clear: both; margin-bottom: 15px;"></div>

		<div class="buttons"><button name="signIn" type="submit" id="submit" value="signIn" class="positive"><?php echo $Translation['sign in']; ?></button></div>
	</fieldset>
	<fieldset id="actions">
		<label><?php echo $Translation['browse as guest']; ?></label>
	</fieldset>
</form>

<div style="clear: both;"></div>

<div id="login-footer" class="TableFooter">
	<b><a href=http://bigprof.com/appgini/>BigProf Software</a> - <?php echo $Translation['powered by']; ?> AppGini 4.70</b>
</div>

<script>document.getElementById('username').focus();</script>
<?php include("$d/footer.php"); ?>