# Fil rouge 2.3 Developper la partie Back-End d'une application web ou web Mobile

## Descriptif du projet 

Création d'une API REST permettant à des salons de beauté de renseigner leur chiffres d'affaires et d'avoir accès au positionnement par rapport à la concurrence.


## Environnement de développement

PHP 8.2
Framework Symphony V7


## Enregistrement, Système d'authentification et force du mot de passe   

Système d'authentification JWT mis en place avec lexik/jwt-authentication-bundle et extension open SSL pour création des clés publique et privée.

Durée de validité du token 3600 secondes (1h) - Peut être modifié dans \config\packages\lexik_jwt_authentication.yaml


Le mot de passe doit être d'au moins 8 caractères comprenant une majuscule, un chiffre et un caractère spécial. Au moment de l'enregistrement la fonction isPasswordComplex  de le UserController permet de vérifier avec des expressions régulières la conformité du mot de passe. La fonction renvoi un message d'erreur indiquant les critères qui ne sont pas respectés.

Lors de l'enregistrement un message d'inscription est envoyé à l'utilisateur pour confirmer son adresse mail et un token d'identification mail est généré et enregsitré dans la base de données.

Ce lien contient pointe vers la route confirm-email/{token-genéré}.

Quand l'utilisateur clique sur le lien, le controlleur ConfirmEmailController vérifie si le token est valide. Si c'est le cas, le champ email_verified est passé à true  dans la base de données et le token est passé à null. Le contrôleur envoi la réponse {"status":true,"message":"Adresse email confirmée avec succès"}

Le service IsMailVerifiedService et le subscriber IsMailVerifiedSubscriber vont vérifier si l'utilisateur porteur du token a bien validé son adresse mail. Si ce n'est pas le cas le subscriber va renvoyer le message :

{
	"message": "Email non vérifié. Veuillez cliquer sur lien de vérification dans l'email envoyé lors de l'inscription. Sans cette action vous ne pourrez pas accéder à ce service"
}

Les routes concernées par ce contrôle sont listées dans la variable $routesRequiringVerification du subscriber IsMailVerifiedSubscriber. Cette liste permet de limiter ou non l'accès à certaines ressources si l'email n'est pas vérifié. 

Si le token est invalide le controleur envoi la réponse {"status":false,"message":"Token invalide"}

En cas de multiples tentatives de connexions erronnées, l'utilisateur va recevoir un mail pour reset son mot de passe. Et cela via un Event Subscriber LoginFailureSubscriber qui va suivre l'évenement lexik_jwt_authentication.on_authentication_failure et mettre à jour le champs loginAttempts dans la base de données. Si le nombre dépasse 5 un email est envoyé via le service Mailer Service.
Procédure de Reset à mettre en place.




## Synthèse des Endpoints

### Endpoint "Register" 
Permet l'ajout d'un nouvel utilisateur.

Chemin :
PATH/register
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
	"status": true,
	"message": "Utilisateur ajouté avec succès"
}

L'Email Existe déjà 

{
	"status": false,
	"message": "Cet email existe déjà"
}

Mot de passe non conforme

{
	"status": false,
	"message": "Le mot de passe n'est pas valide . Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial. Critères non respectés : au moins une majuscule, au moins 8 caractères"
}


### Endpoint "Login" 
Authentifie l'utilisateur avec renvoi d'un Token JWT

Chemin
PATH/api/login_check
Méthode : POST

Body JSON

{
	"email":"test@test.com",
	"password":"Password2?"
}

Succès du Login => obtention d'un token JSON 

{
	"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3MTYyNDQwNDYsImV4cCI6MTcxNjI0NzY0Niwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdDJAdGVzdC5jb20ifQ.V8NRQrVvXy2erXMVBEqldF1RUF3f9rtRCwK8ojP8Yvp9TA4ZCmAkm9nQpXcpM86ZnWMltIOvXkKjtTyIj14hNyvjpfX4IIeNmKPPApIljtvP9bpVFr8HYL_JSxmfrdfTiYEiH9MEHvXezADue-uIL9FTY-sHqb2Rw43WYtnOfzGkC9xu9VDXCxG78g2CjKqQOcp_rqIilb88nTFWc16bg3rWwJ5VYD0gieFG1qa-owfQ9VxjoqW1vDe_WRUcdyKWKOhZluABZZrJ_ECcdEvFUDA6YgWA-WV4RAsstDmUh3TCQJp0bNZPT6lRn5FkXSG0X_wqC-8DP84Z9TMRdKbsbg"
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
	"current_user": {
		"id": 17,
		"firstName": "Jules",
		"lastName": "Cesar",
		"email": "jules.cesar@test.com",
		"emailVerified": false
	}
}

##### Modification profil utilisateur en cours

Ce chemin permet de modifier fistName, lastName et email. Il n'est pas nécessaire de tous les modifier, la modification d'un seul paramètre est possible.

Chemin
PATH/api/profil/user/update
Méthode : PATCH
Header "Bearer Texte_du_token_jwt"

Body :

{

"firstName" : "Julien",
"lastName" : "Dupont",
"email" : "julien.dupont@test.com"

}



Réponse : 
{
	"status": true,
	"message": "Utilisateur modifié avec succès"
}

Le controlleur contient des champs autorisés pour la modification. Si des champs non autorisés sont passés dans le body :

{
	"status": false,
	"message": "Les champs suivants ne peuvent pas être modifiés : password"
}



#### profil/salon

##### Ajout d'un salon par l'utilisateur en cours

Chemin
PATH/api/profil/create-salon
Méthode : POST 
Header "Bearer Texte_du_token_jwt"

Le controlleur va récupérer l'id de l'utilisateur associé au token et l'intégrer à l'intégrer à la colonne user_id.

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

En cas de succès :
{
	"message": "Salon créé avec succès"
}




##### Récupération de la liste de salons appartenant à l'utilisateur en cours

Chemin
PATH/api/profil/salons
Méthode : GET
Header "Bearer Texte_du_token_jwt"

Le controlleur ne va retourner que les salons associés à l'id porteur du token.

Réponse :

[
	{
		"id": 1,
		"salonName": "Nouveau salon",
		"adress": "123 rue des Salons",
		"city": "Paris",
		"zipCode": "75000",
		"departmentCode": "75",
		"etp": "10.00",
		"openingDate": "2023-03-20 10:00:00"
	},
	{
		"id": 2,
		"salonName": "Nouveau salon 2",
		"adress": "123 rue des Salons",
		"city": "Paris",
		"zipCode": "75000",
		"departmentCode": "75",
		"etp": "10.00",
		"openingDate": "2024-04-19 10:00:00"
	}
]

En cas d'absence de salon :

{
	"message": "Aucun salon trouvé pour cet utilisateur"
}





##### Affichage des informations d'un salon précis appartenant à l'utilisateur en cours 


Chemin
PATH/api/profil/salon/{id}
Méthode : GET
Header "Bearer Texte_du_token_jwt"

Avant de répondre, le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

En de correspondance d'Id :
{
	"id": 3,
	"salonName": "Beauty Salon1",
	"adress": "123 rue des Salons",
	"city": "Caen",
	"zipCode": "14000",
	"departmentCode": "14",
	"etp": "2.00",
	"openingDate": "2024-02-01 10:00:00"
}


Si les id ne correspondent pas :

{
	"message": "Accès interdit"
}

Si l'id Salon n'existe pas :
{
	"message": "Salon non trouvé"
}




##### Modification des informations d'un salon précis appartenant à l'utilisateur en cours

Chemin
PATH/api/profil/salon/update/{id}
Méthode : PATCH
Header "Bearer Texte_du_token_jwt"

Le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

Ce chemin permet de modifier toutes les données d'un salon. Il n'est pas nécessaire de tous les modifier, la modification d'un seul paramètre est possible. En cas de modification partielle les autres champs ne seront pas affectés :

Body :
{
	"salon_name":"Beauty parlor"
}

Mise à jour réussie :

{
	"message": "Salon mis à jour avec succès"
}

Tentative de mise jour d'un salon qui n'est pas la propriété du porteur du token

{
	"message": "Accès interdit"
}


### Endpoints CA

#### Insertion CA du mois précédent pour un salon

Chemin
PATH/api/turnover-insert/{salonId}
Méthode : POST
Header "Bearer Texte_du_token_jwt"

Le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

Body

{
	
	"amount":6000
	
		
}

Après l'insertion le contrôleur va recalculer les statistiques (moyenne nationale, départementale, régionale) et va retourner une appréciation sur le positionnement et statistiques brut pour usage par le front si nécessaire :
{
	"message": "Chiffre d'affaires ajouté avec succès",
	"positionnement": "Votre chiffre d'affaires déclaré est inférieur à la moyenne nationale qui est de 5640.66 €",
	"statistiques": {
		"moyenne_nationale": 5640.66
	}
}

Si le chiffre d'affaires du mois précédent a déjà été ajouté :

{
	"message": "Le chiffre d'affaires pour ce mois existe déjà"
}

Si la requête porte sur un salon pour n'appartenant pas au porteur du toke:

{
	"message": "Vous n'êtes pas propriétaire de ce salon"
}

#### Historique des CA pour un salon

Chemin
PATH/api/turnover/{salonId}
Méthode : GET
Header "Bearer Texte_du_token_jwt"

Le controleur va vérifier préalablement que le salon passé en url appartient bien à l'utilisateur porteur du token en comparant l'id du salon demandé avec l'id associé au porteur du token.

En cas de succès de la requête obtention de la liste des CA déclarés pour le salon concerné :

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

Si l'utilisateur tente de renseigner l'id d'un salon qu'il ne possède pas :

{
	"message": "Vous n'êtes pas propriétaire de ce salon"
}

## Structure base de donnée

Table Users 


email 
password
region
département
nom du salon
adresse du salon
date d'ouverture
effectif etp
Nom représentant
Prénom représentant

CA
PERIODE CA








