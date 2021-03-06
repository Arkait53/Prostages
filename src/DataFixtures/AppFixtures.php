<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Stage;
use App\Entity\Entreprise;
use App\Entity\Formation;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Creation d'un générateur de données Faker
        $faker = \Faker\Factory::create('fr_FR'); // create a French faker

        //Definition des Formations
        $dutInfo = new Formation();
        $dutInfo->setNom("DUT Info");
        $dutInfo->setNomComplet("DUT Informatique");

        $lpProg = new Formation();
        $lpProg->setNom("LP Prog");
        $lpProg->setNomComplet("License professionnel Programmation avancée");

        $lpNum = new Formation();
        $lpNum->setNom("DU TIC");
        $lpNum->setNomComplet("Diplome Universitaire des Technologies de l'Information et de la Communication");

        $tabTypeFormation = array($dutInfo, $lpNum, $lpProg); //Tableau des formations

        foreach ($tabTypeFormation as $typeModule) {
            $manager->persist($typeModule);
        }

        //Definition des Entreprises
        $nbEntreprises =50;
        for ($i=0; $i<$nbEntreprises ; $i++) { 
            //Création d'une entreprise
            $entreprise = new Entreprise();
            $entreprise->setNom($faker->company);
            $entreprise->setAdresse($faker->address);
            $entreprise->setActivite($faker->jobTitle);

            //Préparation du nom de l'entreprise
            $nomEntreprise = str_replace(' ','_',$entreprise->getNom()); //Enlève les espace au nom d'entreprise
            $nomEntreprise = str_replace('.','',$nomEntreprise); //Enlève les points 
            $entreprise->setSite(strtolower($faker->regexify('http\:\/\/'.$nomEntreprise.'\.'.$faker->tld)));

            $manager->persist($entreprise);

            //Definition des stages associé à l'entreprise
            $nbStages = $faker->numberBetween($min=1, $max=5);
            for ($y=0; $y<$nbStages; $y++) { 
                //Création d'un stage
                $stage = new Stage();
                $stage->setTitre($entreprise->getActivite());
                $stage->setmail(strtolower($faker->regexify(str_replace('É','é',$faker->firstName).'\.'.$faker->lastName.'@'.$nomEntreprise.'\.com')));
                $stage->setDescription($faker->realText($maxNbChars = $faker->numberBetween($min = 600, $max = 1200), $indexSize = 1));
                $stage->setEntreprise($entreprise);

                //Ajout des formations au stage
                $nbFormations = $faker->numberBetween($min=1,$max=5);

                switch ($nbFormations) {
                    case '1':
                        $numTypeFormation = $faker->numberBetween($min=0, $max=2);
                        $stage->addFormation($tabTypeFormation[$numTypeFormation]);
                        //Ajout du stage à la formation
                        $tabTypeFormation[$numTypeFormation]->addStage($stage);
                        $manager->persist($tabTypeFormation[$numTypeFormation]);
                        break;
                    
                    case '2':
                        $numTypeFormation1 = $faker->numberBetween($min=0, $max=2);
                        $numTypeFormation2 = $faker->numberBetween($min=0, $max=2);
                        $stage->addFormation($tabTypeFormation[$numTypeFormation1]);
                        //Ajout du stage à la formation1
                        $tabTypeFormation[$numTypeFormation1]->addStage($stage);
                        $manager->persist($tabTypeFormation[$numTypeFormation1]);

                        if ($numTypeFormation1!=$numTypeFormation2) {
                            //Ajout du stage à la formation2 si les 2 formations tirées sont différentes
                            $stage->addFormation($tabTypeFormation[$numTypeFormation2]);
                            $manager->persist($tabTypeFormation[$numTypeFormation2]);
                        }
                        break;

                    default:    // 3 formations
                        // Formation 1
                        $stage->addFormation($tabTypeFormation[0]);
                        $tabTypeFormation[0]->addStage($stage);
                        $manager->persist($tabTypeFormation[0]);

                        // Formation 2
                        $stage->addFormation($tabTypeFormation[1]);
                        $tabTypeFormation[1]->addStage($stage);
                        $manager->persist($tabTypeFormation[1]);

                        // Formation 3
                        $stage->addFormation($tabTypeFormation[2]);
                        $tabTypeFormation[2]->addStage($stage);
                        $manager->persist($tabTypeFormation[2]);
                        break;
                }

                $manager->persist($stage);
                
                //Ajout du stage à l'entreprise
                $entreprise->addEntreprise($stage);//La méthode n'a pas le bon nom (Mauvaise génération)
                $manager->persist($entreprise);
            }
        }

        //Creation des user de test
        $louison = new User();
        $louison->setPrenom('Louison');
        $louison->setNom('Vincent');
        $louison->setEmail('louison@free.fr');
        $louison->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $louison->setPassword('$2y$15$7exe9S/tYnTPxLRoE7fqvOz2LfNPzmVxtpmMtB0PUdNxW8eqoHUOu');

        $manager->persist($louison);

        $basile = new User();
        $basile->setPrenom('Basile');
        $basile->setNom('Basile');
        $basile->setEmail('basile@free.fr');
        $basile->setRoles(['ROLE_USER']);
        $basile->setPassword('$2y$15$eAgUV9AQrWErkCGu9wZkOu7NGZmOqU6CtdQNX3Ot7rl4lT3vv/Mu.');

        $manager->persist($basile);

        $manager->flush();
    }
}
