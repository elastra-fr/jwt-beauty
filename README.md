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

Ce lien contient pointe vers la route confirm-emai/token-genéré.

Quand l'utilisateur clique sur le lien, le controlleur ConfirmEmailController vérifie si le token est valide. Si c'est le cas, le champ email_verified est passé à true  dans la base de données et le token est passé à null. Le contrôleur envoi la réponse {"status":true,"message":"Adresse email confirmée avec succès"}


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







