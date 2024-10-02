ICHOU Abdelali
GOUM GOUM Omar
OUZZINE Ahmed
BACHR Abdallah

//Configuration GIT
git config --global user.name "Goum Goum Omar"
git config --global user.email "omar.goum-goum@etu.univ-orleans.fr"
git clone https://o22307261@pdicost.univ-orleans.fr/git/scm/wc24/cc7.git
//Creation de projet
symfony new cc7 --webapp
//Installation webpack et bootstrap
symfony composer require symfony/webpack-encore-bundle
npm install
npm install bootstrap
npm install bootstrap-icons
npm run dev

//Creatoin de l'entité leçon, on a choisi le nom Lesson pour éviter les problèmes de 'ç'
php bin/console make:entity Lesson (avec la définition de noms des attributs) 
php bin/console make:migration
php bin/console doctrine:migrations:migrate

//Configuration de fixture et faker
composer require --dev orm-fixtures doctrine/doctrine-fixtures-bundle fakerphp/faker
php bin/console make:fixture (le nom de la class choisi est Fixtures)
php bin/console doctrine:fixtures:load (pour remplir la table de Lesson)

//Creation de crud de Lesson
symfony console make:crud Lesson

//Creatoin de navbar
//On a creé une page pour navbar 'nav.html.twig' et on a fait {% include 'nav.html.twig' %} dans base.html.twig

//Descriptions au format markdown
symfony console make:twig-extension
composer update cebe/markdown
//On a ajouter la ligne 'cebe\markdown\Markdown:' à la fin de fichier 'service.yaml'

// La creation de l'entity User
php bin/console make:entity User
php bin/console make:migration
php bin/console doctrine:migrations:migrate
symfony console make:auth
// On a modifié la fonction onAuthenticationSuccess (dans la class UserAuthenticator)
//Le nom de l'utilsateur doit etre unique pour chaque utilisateur
symfony console make:registration-form


//Pour la creation de relation entre User et Lesson
php bin/console make:entity (On doit choisi une entity pour faire des modifications
et suivre les étapes, parmis les étapes on a du choisir le type de la relatoin "OneToMany" pour le User)
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
// On a modifié la class Fixtures.php en ajoutant un constructeur qui nous permet
d'ajouter un neveau utlisateur (Problème de hashage de password)

//On a vérifié que l'utilisateur est connecté, et que ce user soit le professor en utlisant Security package
//On doit vérifier que le user a le role de proffesseur
//Lors de la creatoin d'un leçon on sauvgard le user qu'a créé cette leçon

//On a masqué les button d'ajout supressoin, et modification de cour pour l'étudiant
//On a ajouté des vérification d'existance d'un user authentifié pour qu'il puisse faire des actions.
//l'étudiant ne peut pas créer,modifier et supprimer un cours, meme s'il a l'url, par ce que c'est
// interdit pour lui d'accéder dans quelques Urls tel que la modification d'un cours
//Le prof n'a pas le droit de modifier ou bien supprimer un cours sauf si est l'auteur de cours

//On a modifié le fixture pour qu'on puisse genérer des user et des cours aléatoire
//On a associé pour chaque prof 5 cours
//on a créé aussi 5 étudiants

//L'inscription et la desinscription dans une leçon
php bin/console make:entity (On doit choisi une entity pour faire des modifications
et suivre les étapes, parmis les étapes on a du choisir le type de la relatoin "ManyToMany" entre le User et Lesson)
php bin/console make:migration
php bin/console doctrine:migrations:migrate
//On a géreé un petit peu les droits d'accées, comme sauf les étudiants peuvent s'inscrire et desinscrire

//Pour que le prof puisse consulter les étudiants inscrits dans un cours on a joute utliser la relation entre user et lesson
//Le meme principe concernat l'étudiant quand il consulte la liste de ces cours


php bin/console doctrine:fixtures:load (On a créé 5 profs 5 étudiants, chaque profs a 5 cours et chaque étudiant inscrit dans 5 cours)

//Dans le fichier security.yaml on a ajouter les lignes suivantes pour définier les roles et les patterns de routes
access_control:
        # Restrict access to certain paths based on roles
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/prof, roles: [ROLE_PROFESSEUR,ROLE_ADMIN] }
        - { path: ^/etudiant, roles: ROLE_ELEVE }
//On a modifié la fonction de getRoles de l'entité user pour qu'elle return la liste de roles connectés
symfony console make:crud User (On a choisi le nome de controller ProfController)
//L'admin peut ajouter ou supprimer un prof, il peut aussi modifier le role d'un prof

//La gestion de droits d'accées en utlisant les patterns definis dans security.yaml

//On a modifié le fixture pour ajouter un admin (Login='admin', mdp='admin')