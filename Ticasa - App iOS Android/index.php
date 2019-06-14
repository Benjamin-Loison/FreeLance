<?php
	$login = 'HeyBryTica412';
	session_start();
	if(!isset($_SESSION['connection']))
		$_SESSION['connection'] = '';
	$address = null;
	$username = null;
	$action = null;
	$connected = false;
	if(isset($_POST['connection']))
	{
		if($_POST['connection'] == $login)
		{
			$connected = true;
			$_SESSION['connection'] = $login;
		}
		else
			echo 'Mot de passe invalide !';
	}
	else if($_SESSION['connection'] == $login)
		$connected = true;
	if(isset($_POST['address']))
	{
		$address = $_POST['address'];
		$tel = $_POST['tel'];
		$email = $_POST['email'];
	}
	if(isset($_POST['username']))
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
	}
	if(isset($_POST['action']))
		$action = $_POST['action'];
	if(isset($_POST['plumbing']))
	{
		$plumbing = $_POST['plumbing'];
		$electricity = $_POST['electricity'];
		$locksmith = $_POST['locksmith'];
	}

	try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=ticasa;charset=utf8', 'root', '');
	}
	catch(Exception $e)
	{
		die('Erreur : '.$e->getMessage());
	}

	function str($string)
	{
		return str_replace('\'', '\\\'', $string);
	}
		
	if($address != null)
	{
		$bdd->exec('insert into accounts(username, address, tel, email, password) values(\'' . str($username) . '\', \'' . str($address) . '\', \'' . $tel . '\', \'' . $email . '\', \'' . $password . '\')');
		if($action == null)
			return;
	}

	if($username != null && $action == null)
	{
		//CHECK connexion appli
	}
	else if($connected)
	{
		echo '<link href="Basique.css" rel="stylesheet" type="text/css">';
		$type = '';
		if($action != null)
		{
			$parts = explode("@", $action);
			$type = $parts[0];
			$id = 0;
			if(count($parts) > 1)
				$id = explode("@", $action)[1];
			if($type == "remove")
				$bdd->exec('delete from accounts where id=' . $id);
			else if($type == "removePriceList")
				$bdd->exec('delete from priceList where id=' . $id);
			else if($type == "addPriceList")
				$bdd->exec('insert into priceList(plumbing, electricity, locksmith) values(\'' . str($plumbing) . '\', \'' . str($electricity) . '\', \'' . str($locksmith) . '\')');
			else if($type == "refreshPriceList")
				$bdd->exec('update priceList set plumbing = \'' . str($plumbing) . '\', electricity = \'' . str($electricity) . '\', locksmith = \'' . str($locksmith) . '\' where id=' . $id);
			else if($type == "info")
			{
				$username = $bdd->query('select username from accounts where id=' . $id)->fetch()[0];
				echo '<table><caption>Liste des demandes pour l\'utilisateur ' . $username . '</caption>
				<tr>
					<th>ID</th>
					<th>Nom/Raison sociale</th>
					<th>Adresse</th>
					<th>Téléphone</th>
					<th>Email</th>
					<th>Panne</th>
					<th>Documents</th>
					<th>Description</th>
					<th>Sur place</th>
					<th>Adresse de l\'intervention</th>
					<th>Nom du contact</th>
					<th>Téléphone du contact</th>
					<th>Date</th>
				</tr>';
				$content = $bdd->query('select * from demands where username=' . $username);
				$nextId = 1;
				if($content == "")
					return;
				while($data = $content->fetch())
				{
					$nextId = $data['id'];
					echo '<tr><td>' . $nextId . '</td><td>' . $data['username'] . '</td><td>' . $data['address'] . '</td><td>' . $data['tel'] . '</td><td>' . $data['email'] . '</td><td>' . $data['breakdown'] . '</td><td>' . $data['documents'] . '</td><td>' . $data['description'] . '</td><td>' . $data['here'] . '</td><td>' . $data['addressBreakdown'] . '</td><td>' . $data['nameBreakdown'] . '</td><td>' . $data['telBreakdown'] . '</td><td>' . $data['date'] . '</td></tr>';
				}
				return;
			}
		}
		echo '<table><caption>Liste des utilisateurs</caption>
			<tr>
				<th>ID</th>
				<th>Nom/Raison sociale</th>
				<th>Adresse</th>
				<th>Téléphone</th>
				<th>Email</th>
				<th>Mot de passe</th>
				<th>Actions</th>
			</tr>';
		$content = $bdd->query('select * from accounts');
		$nextId = 0;
		while($data = $content->fetch())
		{
			$nextId = $data['id'];
			echo '<tr><td>' . $nextId . '</td><td><form method="post"><input type="text" name="action" value="info@' . $nextId . '" class="hidden"><input type="image" value="' . $data['username'] . '"></form></td><td>' . $data['address'] . '</td><td>' . $data['tel'] . '</td><td>' . $data['email'] . '</td><td>' . $data['password'] . '</td><td><form method="post"><input type="image" name="action" value="remove@' . $nextId . '" title="Retirer" height=5% src="remove.png"></form></td></tr>';
		}
		echo '<form method="post"><td>' . ($nextId + 1) . '</td><td><input type="text" name="username" value="Nom/Raison sociale"></td>
              <td><input type="text" name="address" value="Adresse"></td>
              <td><input type="tel" name="tel" value="Téléphone"></td>
              <td><input type="email" name="email" value="Email"></td>
              <td><input type="password" name="password" value="Mot de passe"></td>
			  <td><input type="text" name="action" value="add" class="hidden"><input type="image" title="Ajouter" height=5% src="add.png"></td></form></table>';

		echo '<br><br><table><caption>Grille tarifaire</caption>
			<tr>
				<th>ID</th>
				<th>Plomberie</th>
				<th>Electricité</th>
				<th>Serrurerie</th>
				<th>Actions</th>
			</tr>';
		$content = $bdd->query('select * from priceList');
		$nextId = 0;
		while($data = $content->fetch())
		{
			$nextId = $data['id'];
			echo '<tr><td>' . $nextId . '</td><td><form method="post"><input type="text" name="plumbing" value="' . $data['plumbing'] . '"></td><td><input type="text" name="electricity" value="' . $data['electricity'] . '"></td><td><input type="text" name="locksmith" value="' . $data['locksmith'] . '"></td><td><input type="image" name="action" value="removePriceList@' . $nextId . '" title="Retirer" height=5% src="remove.png"><input type="image" name="action" value="refreshPriceList@' . $nextId . '" title="Mettre à jour" height=5% src="refresh.png"></form></td></tr>';
		}
		echo '<form method="post"><td>' . ($nextId + 1) . '</td><td><input type="text" name="plumbing" value="Plomberie"></td>
              <td><input type="text" name="electricity" value="Electricité"></td>
              <td><input type="text" name="locksmith" value="Serrurerie"></td>
			  <td><input type="image" name="action" value="addPriceList" title="Ajouter" height=5% src="add.png"></td></form></table><br><br><form method="post">';
		$content = '';
		
		function keyWord($word)
		{
			if($word == 'Plomberie' || $word == 'Electricité' || $word == 'Serrurerie' || $word == 'Jour' || $word == 'Mois' || $word == 'Année')
				return true;
		}
		
		function button($value)
		{
			global $type;
			echo '<input type="';
			if(keyWord($type))
			{
				//
			}
			else
			{
				if($type == $value)
					echo 'image" name="action" value="Désactiver filtre ' . $value . '">';
				else
					echo 'submit" name="action" value="' . $value . '">';
			}
		}

		button('Plomberie');
		button('Electricité');
		button('Serrurerie');
		echo '&emsp;';
		button('Jour');
		button('Mois');
		button('Année');
		echo '<input type="image" name="action" value="left" title="Précédent" height=2% src="left.png"><input type="image" name="action" value="right" title="Suivant" height=2% src="right.png"></form>';

		if($action != 'Plomberie' && $action != 'Electricité' && $action != 'Serrurerie')
			$content = $bdd->query('select * from demands');
		else
			$content = $bdd->query('select * from demands where breakdown=\'' . $action . '\'');

		echo '<br><table><caption>Liste des demandes</caption>
			<tr>
				<th>ID</th>
				<th>Nom/Raison sociale</th>
				<th>Adresse</th>
				<th>Téléphone</th>
				<th>Email</th>
				<th>Panne</th>
				<th>Documents</th>
				<th>Description</th>
				<th>Sur place</th>
				<th>Adresse de l\'intervention</th>
				<th>Nom du contact</th>
				<th>Téléphone du contact</th>
				<th>Date</th>
			</tr>';
		$nextId = 1;
		while($data = $content->fetch())
		{
			$nextId = $data['id'];
			echo '<tr><td>' . $nextId . '</td><td>' . $data['username'] . '</td><td>' . $data['address'] . '</td><td>' . $data['tel'] . '</td><td>' . $data['email'] . '</td><td>' . $data['breakdown'] . '</td><td>' . $data['documents'] . '</td><td>' . $data['description'] . '</td><td>' . $data['here'] . '</td><td>' . $data['addressBreakdown'] . '</td><td>' . $data['nameBreakdown'] . '</td><td>' . $data['telBreakdown'] . '</td><td>' . $data['date'] . '</td></tr>';
		}
	}
	else
		echo '<form method="post"><input type="password" name="connection" value="Mot de passe"><input type="submit"></form>';
?>