<?php

// Script is added for demonstration purposes only
script('twofactor_email', 'challenge');

?>


<form method="POST">
			<label for=""> <?php p("Nous avons envoyé un code à 6 chiffre à votre adresse mail.")?></label>
			<input type="hidden" name="redirect_url" value="<?php p($_['redirect_url']); ?>">
			<input type="text" name="challenge" autocomplete="off" autocapitalize="off" required="required" placeholder="Le code à 6 chiffre">
			<hr><input type="submit" class="button" value="Vérifier">
</form> 
	