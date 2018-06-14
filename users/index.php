<?php
//retornará verdadeiro se dados de post existirem em users.json
$validation = array(
	"status" => "false",
	"message" => "usuário inválido", 
);

//lista de usuarios a partir de users.json
$json = file_get_contents('users.json');
$obj = json_decode($json, true);

//validacao
if (isset($_POST['email']) and isset($_POST['pass'])) {
	foreach ($obj['users'] as $user) {
		if ($user["email"] == $_POST['email']) {
			if ($user["pass"] == $_POST['pass']) {
				$validation = array(
					"status" => "true",
					"message" => "usuário válido",
					"firstname" => $user['firstname'],
					"role" => $user['role']
				);
			}
		}
	}
}
else{
	$validation = array(
		"status" => "false",
		"message" => "inclua email e senha"
	);
}
echo json_encode($validation);
header('Content-type: application/json');
?>