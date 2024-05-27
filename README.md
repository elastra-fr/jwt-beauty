# Fil rouge 2.3 Developper la partie Back-End d'une application web ou web Mobile

## Descriptif du projet 

Création d'une API REST permettant à des salons de beauté de renseigner leur chiffres d'affaires et d'avoir accès au positionnement par rapport à la concurrence.


## Environnement de développement

PHP 8.2
Framework Symphony V7

## Environnement de production 
PHP 8.2 
Hebergement  O2switch
Domaine : api.eldn-dev-app.fr



## Harmonisation des réponses 

La plupart des réponses JSON vont se présenter sous les formats qui suivent. Dans toutes les réponses un code HTTP est passé via le header.

En cas de succès :

{
    "status": "success",
    "data": {
        // Les données ou le message de la réponse vont se trouver ici
    },
    "error": null
}

En cas d'erreur :

{
    "status": "error",
    "data": null,
    "error": {
		"code":"ERROC_CODE",
		"message": "Détails de l'erreur"
	}
}


Ce format est normalisé grace au service JsonResponseNormalizer.  
Potentiellement cette standardisation peut faciliter la gestion des réponses par le front end.

Le Trait StandardResponsesTrait va fournir des réponses standard (toujours mises en forme avec JsonResponsesNormalizer).



## Enregistrement, Système d'authentification et force du mot de passe   

### JWT
Système d'authentification JWT mis en place avec lexik/jwt-authentication-bundle et extension open SSL pour création des clés publique et privée.

Durée de validité du token 3600 secondes (1h). Cette durée peut être modifiée dans \config\packages\lexik_jwt_authentication.yaml

### Force du mot de passe 
Le mot de passe doit être d'au moins 8 caractères comprenant une majuscule, un chiffre et un caractère spécial. Au moment de l'enregistrement le service PasswordValidatorService permet de vérifier avec des expressions régulières la conformité du mot de passe. Le renvoi un message d'erreur indiquant les critères qui ne sont pas respectés.

### Vérification de l'adresse mail 
Lors de l'enregistrement un message d'inscription est envoyé, grace à MailerService, à l'utilisateur pour confirmer son adresse mail et un token d'identification mail est généré et enregistré dans la base de données. Le mail contient un lien de vérification.

Ce lien contient pointe vers la route confirm-email/{token-genéré}.

Quand l'utilisateur clique sur le lien, le controlleur ConfirmEmailController vérifie si le token est valide. Si c'est le cas, le champ email_verified est passé à true  dans la base de données et le token est passé à null. Le contrôleur envoi la réponse :
{"status":"success","data":{"message":"Adresse email confirmée avec succès"},"error":null}

Si le token est invalide :
{"status":"error","data":null,"error":{"code":"INVALID_TOKEN","message":"Token invalide"}}

Le service IsMailVerifiedService et le subscriber IsMailVerifiedSubscriber vont vérifier si l'utilisateur porteur du token a bien validé son adresse mail. Si ce n'est pas le cas le subscriber va renvoyer le message :

{
	"status": "error",
	"data": null,
	"error": {
		"code": "email_not_verified",
		"message": "Email non vérifié. Veuillez cliquer sur lien de vérification dans l'email envoyé lors de l'inscription. Sans cette action vous ne pourrez pas accéder à ce service"
	}
}

Les routes concernées par ce contrôle sont listées dans la variable $routesRequiringVerification du subscriber IsMailVerifiedSubscriber. Cette liste permet de limiter ou non l'accès à certaines ressources si l'email n'est pas vérifié. Le client pourrait décider que certaines routes peuvent être accessibles en lecture même si l'utilisateur n'a pas encore validé son adress.

Si le token est invalide le controleur envoi la réponse {"status":false,"message":"Token invalide"}

### Sécurité en cas de multiples tentatives de connexions
En cas de multiples tentatives de connexions erronnées, l'utilisateur va recevoir un mail pour reinitialiser son mot de passe. Et cela via un Event Subscriber LoginFailureSubscriber qui va suivre l'évenement lexik_jwt_authentication.on_authentication_failure et mettre à jour le champs loginAttempts dans la base de données. Si le nombre dépasse 5 un email est envoyé via le service Mailer Service.
Le champ password_reset_in_progress est passé à true et un token password_reset_token est généré.
L'email de reset contient un lien vers un formulaire de reset du mot de passe. Le mot de passe fera l'objet de la même vérification qu'au moment du register.

Tant que la procédure de reset est en cours le Subscriber IsPasswordResetInProgressSubscriber empêche l'utilisateur d'atteindre les routes derrière https://api.eldn-dev-app.fr/api/* et l'utilisateur recevra la réponse : 
{
	"status": "error",
	"data": null,
	"error": {
		"code": "UNAUTHORIZED",
		"message": "Réinitialisation du mot de passe en cours. Veuillez vérifier vos emails pour terminer le processus. Tant que la procédure ne sera pas terminée, vous ne pourrez pas accéder à ces services"
	}
}


Si la procédure de reset aboutie, le champ password_reset_in_progress est passé à false et le token password_reset_token est passé à null

### Procédure de changement d'email

Le endpoint update permet de changer d'email mais cette mise à jour va faire l'objet d'une procédure particulière.
Le nouvel email va être conservé provisoirement dans un champ new_email et un token email_change_token va être généré.

Un mail va être envoyé à l'ancienne adresse mail pour confirmer ou non le changement d'adresse email. Ce mail contient deux liens, un lien de confirmation de la procédure et un lien d'annulation de la procédure.

En cas de confirmation l'email est changé, new_email est effacé ainsi que le token. Un nouveau token est généré et mail pour valider la nouvelle adresse est envoyée à cette dernière avec un lien de validation comme pour la procédure de register.

En cas d'annulation de la procédure le token de changement ainsi que new-email sont effacés et par sécurité une procédure de reset du mot de passe est lancée.



## Synthèse des Endpoints

### Endpoint "Register" 
Permet l'ajout d'un nouvel utilisateur.

Chemin :
https://api.eldn-dev-app.fr/register
Méthode POST
Body JSON

{
	"email":"test@test.com",
	"password":"123456",
	"firstName":"Julius",
	"lastName":"Cesar"
}

Succès de la requête 

{
	"status": "success",
	"data": {
		"message": "Utilisateur ajouté avec succès"
	},
	"error": null
}

L'Email Existe déjà :

{
	"status": false,
	"message": "Cet email existe déjà"
}

Mot de passe non conforme :

{
	"status": "error",
	"data": null,
	"error": {
		"code": "BAD_REQUEST",
		"message": "Le mot de passe n'est pas valide . Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial. Critères non respectés : au moins un chiffre, au moins un caractère spécial, au moins 8 caractères"
	}
}


### Endpoint "Login" 
Authentifie l'utilisateur avec renvoi d'un Token JWT

Chemin
https://api.eldn-dev-app.fr/api/login_check
Méthode : POST

Body JSON

{
	"email":"test@test.com",
	"password":"Password2?"
}

Succès du Login => obtention d'un token JSON à utiliser pour toutes les requêtes derrières la route https://api.eldn-dev-app.fr/api/*

{
	"token": "token_text"
}

Echec du Login =>

{
	"code": 401,
	"message": "Invalid credentials."
}


### Endpoint "profil"

L'utilisateur Authentifié peut accéder aux Endpoints situés derrière la route /api/*.

L'accès à ces routes nécessitent d'inclure dans le header de l'entête Authorization  avec la valeur "Bearer Texte_du_token_jwt".

#### profil/user

##### Informations utilisateur en cours

Chemin
PATH/api/profil/user/informations
Méthode : GET
Header "Bearer Texte_du_token_jwt"

Réponse :

{
	"status": "success",
	"data": {
		"current_user": {
			"id": 3,
			"firstName": "Edmund",
			"lastName": "Stein",
			"email": "test@test.com",
			"emailVerified": true
		}
	},
	"error": null
}

##### Modification profil utilisateur en cours

Ce chemin permet de modifier fistName, lastName et email. Il n'est pas nécessaire de tous les modifier, la modification d'un seul paramètre est possible, 

Chemin
https://api.eldn-dev-app.fr/api/profil/user/update
Méthode : PATCH
Header "Bearer Texte_du_token_jwt"

Body :

{

"firstName" : "Julien",
"lastName" : "Dupont",
"email" : "julien.dupont@test.com"

}

Si l'utilisateur a modifié son adresse mail cela va lancer un procédure et un message spécifique .

Réponse en cas de succès :

{
	"status": "success",
	"data": {
		"message": "Utilisateur modifié avec succès",
		"emailChangeMessage": "Un email de confirmation a été envoyé à votre ancienne adresse email. Veuillez cliquer sur le lien qu'il contient pour confirmer la procédure de changement."
	},
	"error": null
}

Si pas de changement d'adresse mail :

{
	"status": "success",
	"data": {
		"message": "Utilisateur modifié avec succès",
		"emailChangeMessage": null
	},
	"error": null
}

Le controlleur contient des champs autorisés pour la modification. Si des champs non autorisés sont passés dans le body :

{
	"status": "error",
	"data": null,
	"error": {
		"code": "BAD_REQUEST",
		"message": "Les champs suivants ne peuvent pas être modifiés : password"
	}
}



#### profil/salon

##### Ajout d'un salon par l'utilisateur en cours

Chemin
https://api.eldn-dev-app.fr/api/profil/create-salon
Méthode : POST 
Header "Bearer Texte_du_token_jwt"

Le controlleur va récupérer l'id de l'utilisateur associé au token et l'intégrer à l'intégrer à la colonne user_id.
Pour le département l'utilisateur va rentrer le code usuel du département. Le controlleur va automatiquent faire la jointure avec l'id département de la table département.

Body :

{
    "salon_name": "Beauty Salon1",
    "adress": "123 rue des Salons",
    "city": "Caen",
    "zipCode": "14000",
    "department_code": "14",
    "etp": "2",
    "opening_date": "2024-02-01T10:00:00"
}

{
	"status": "success",
	"data": {
		"message": "Salon créé avec succès"
	},
	"error": null
}




##### Récupération de la liste de salons appartenant à l'utilisateur en cours

Chemin
https://api.eldn-dev-app.fr/api/profil/salons
Méthode : GET
Header "Bearer Texte_du_token_jwt"

Le controlleur ne va retourner que les salons associés à l'id porteur du token. 

Réponse :

{
	"status": "success",
	"data": {
		"salons": [
			{
				"id": 1,
				"salonName": "Peau'tit Bonheur",
				"adress": "123 rue de la liberté",
				"city": "Lyon",
				"zipCode": "69000",
				"departmentName": "Rhône",
				"departmentCode": "69",
				"regionName": "Auvergne-Rhône-Alpes",
				"etp": "3.00",
				"openingDate": "2022-08-08 10:00:00"
			},
			{
				"id": 2,
				"salonName": "Beauté Corse",
				"adress": "123 rue de l'indépendance",
				"city": "Ajaccio",
				"zipCode": "20000",
				"departmentName": "Haute-Corse",
				"departmentCode": "2B",
				"regionName": "Corse",
				"etp": "7.00",
				"openingDate": "2023-09-01 10:00:00"
			}
		]
	},
	"error": null
}

{
	"status": "success",
	"data": {
		"message": "Aucun salon trouvé pour cet utilisateur"
	},
	"error": null
}





##### Affichage des informations d'un salon précis appartenant à l'utilisateur en cours 


Chemin
https://api.eldn-dev-app.fr/api/profil/salon/{id}
Méthode : GET
Header "Bearer Texte_du_token_jwt"

Avant de répondre, le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

En de correspondance d'Id :
{
	"status": "success",
	"data": {
		"id": 3,
		"salonName": "Beauté Bretonne",
		"adress": "123 rue de l'indépendance",
		"city": "Rennes",
		"zipCode": "35000",
		"departmentName": "Ille-et-Vilaine",
		"departmentCode": "35",
		"regionName": "Bretagne",
		"etp": "7.00",
		"openingDate": "2022-02-15 10:00:00"
	},
	"error": null
}

Tentative d'accès à un salon dont le client n'est pas propriétaire :

{
	"status": "error",
	"data": null,
	"error": {
		"code": "FORBIDDEN",
		"message": "Accès non autorisée"
	}
}

Si le salon n'existe pas :

{
	"status": "error",
	"data": null,
	"error": {
		"code": "SALON_NOT_FOUND",
		"message": "Salon non trouvé"
	}
}




##### Modification des informations d'un salon précis appartenant à l'utilisateur en cours

Chemin
https://api.eldn-dev-app.fr/api/profil/salon/update/{id}
Méthode : PATCH
Header "Bearer Texte_du_token_jwt"

Le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

Ce chemin permet de modifier toutes les données d'un salon. Il n'est pas nécessaire de tous les modifier, la modification d'un seul paramètre est possible. En cas de modification partielle les autres champs ne seront pas affectés :

Body :
{
    "salon_name": "Beauté Bretonne",
    "adress": "123 rue de l'indépendance",
    "city": "Rennes",
    "zipCode": "35000",
    "department_code": "35",
    "etp": "6",
    "opening_date": "2022-02-15T10:00:00"
}

Mise à jour réussie :

{
	"status": "success",
	"data": {
		"message": "Salon mis à jour avec succès"
	},
	"error": null
}

Tentative de mise jour d'un salon qui n'est pas la propriété du porteur du token

{
	"status": "error",
	"data": null,
	"error": {
		"code": "FORBIDDEN",
		"message": "Accès non autorisée"
	}
}


### Endpoints CA

#### Insertion CA du mois précédent pour un salon

Chemin
https://api.eldn-dev-app.fr/api/turnover-insert/1
Méthode : POST
Header "Bearer Texte_du_token_jwt"

Le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

Body

{
	
	"amount":15000
	
		
}

Après l'insertion le contrôleur va recalculer les statistiques (moyenne nationale, départementale, régionale) et va retourner une appréciation sur le positionnement et statistiques brut pour usage par le front si nécessaire :
{
	"status": "success",
	"data": {
		"message": "Chiffre d'affaires ajouté avec succès",
		"positionnement_national": "",
		"positionnement_departemental": "Votre chiffre d'affaires déclaré est égal à la moyenne départementale (35 - Ille-et-Vilaine) qui est de 15000 €",
		"positionnement_regional": "Votre chiffre d'affaires déclaré est égal à la moyenne régionale (Bretagne) qui est de 15000 €",
		"statistiques": {
			"moyenne_nationale": 15000,
			"moyenne_departementale": 15000,
			"moyenne_regionale": 15000
		}
	},
	"error": null
}


Si le chiffre d'affaires du mois précédent a déjà été ajouté :

{
	"status": "error",
	"data": null,
	"error": {
		"code": "ALREADY_EXISTS",
		"message": "Le chiffre d'affaires pour ce mois existe déjà"
	}
}

Si la requête porte sur un salon pour n'appartenant pas au porteur du toke:

{
	"status": "error",
	"data": null,
	"error": {
		"code": "NOT_OWNER",
		"message": "Vous n'êtes pas propriétaire de ce salon"
	}
}

#### Historique des CA pour un salon

Chemin
https://api.eldn-dev-app.fr/api/turnover/{id}
Méthode : GET
Header "Bearer Texte_du_token_jwt"

Le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

En cas de succès de la requête obtention de la liste des CA déclarés pour le salon concerné du plus récent au plus ancien.

[
	{
		"id": 1,
		"period": "2024-04-01",
		"turnover_amount": "6000.00"
	},
	{
		"id": 57,
		"period": "2024-03-01",
		"turnover_amount": "241.13"
	},
	{
		"id": 56,
		"period": "2024-02-01",
		"turnover_amount": "8946.51"
	},
	{
		"id": 55,
		"period": "2024-01-01",
		"turnover_amount": "1597.14"
	},
	{
		"id": 54,
		"period": "2023-12-01",
		"turnover_amount": "6246.39"
	}
	]
{
	"status": "error",
	"data": null,
	"error": {
		"code": "NOT_OWNER",
		"message": "Vous n'êtes pas propriétaire de ce salon"
	}
}

## Rappels mails 














