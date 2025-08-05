<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $clientsData = [
            ['Dr SALEM Sylvain', 'HM', '2021-03-30', 'IDS 1532-CL00216', null, true, '2021-04-01', '2021-04-30', 'CL00216', '01 49 61 11 11', '06 86 41 85 38'],
            ['Dr TEREL Cabinet GAMBETTA', 'VM', '2021-04-01', 'IDS 1533-CL01046', null, true, null, null, 'CL01046', '01 47 39 18 88', '06 79 17 49 67'],
            ['POLYCLINIQUE DE CREIL + Avenant Pano', 'HM', '2021-10-04', 'IDS 1538-CL01086', null, true, null, null, 'CL01086', '03 44 55 87 00', '06 28 83 81 70'],
            ['CABINET THAI et ASSOCIES GIF SUR YVETTE', 'VM', '2021-10-18', 'IDS 1537-CL01076', null, true, null, null, 'CL01076', '160124555', '603981633'],
            ['LES ULIS- MR HADDAD 91', 'VM', '2024-01-01', 'IDS-1542', null, true, '2024-02-14', null, 'CL01199', '01 72 86 50 02', null],
            ['BRY VARDA - Dr ALMODOVAR', null, '2022-05-24', 'IDS-1545-CL01094', null, true, '2022-07-01', null, 'CL01094', '01 41 77 09 23', '06 80 02 70 13'],
            ['BITBOL et MARTINSKY 94 SAINT MAUR DES FOSSE', 'VM', '2022-10-17', 'IDS-1546- CL01095', null, true, '2022-11-03', '2022-11-03', 'CL01095', '662057159', '01 48 83 24 24'],
            ['SION COHEN 75', 'VM', '2023-01-30', 'IDS-1547-CL00050', null, true, '2023-01-30', '2023-01-30', 'CL00050', '01 45 67 89 03', null],
            ['CD ROMASI 77 PRINGY', 'VM', '2025-03-24', 'FA2016768- CL00553', null, true, '2025-05-06', '2025-06-05', 'CL00553', '01 60 65 30 00', '781500487'],
            ['NISSAN ZOUHOUR 75008 PARIS', 'VM', '2025-04-07', 'FA2016646-CL01328', null, true, '2025-04-30', '2025-04-30', 'CL01328', '01 40 76 08 63', '06 60 67 23 84'],
            ['DR MAXIME MOREL', 'HM', '2025-02-28', 'FA2016525 - CL00651', null, true, '2025-03-28', '2025-03-28', 'CL00651', '950576451', '671473232'],
            ['CABINET FAMILIALE DR SOSA', 'HM', '2024-11-29', 'FA2016218 - CL01337', null, true, '2024-12-15', '2024-12-15', 'CL01337', '147601715', null],
            ['DR AUGIER VICTOR', 'HM', '2024-12-20', 'FA2016316 CL01366', null, true, '2025-01-24', '2025-01-24', 'CL01366', '01 47 02 97 20', '06 52 13 30 37'],
            ['SELARL BINK DR NGUYEN-KHOA', 'HM', '2025-02-05', 'FA2016369 - CL01037', null, true, '2025-02-07', '2025-02-07', 'CL01037', '01 43 78 10 86', '06 25 43 29 19'],
            ['DR VASILE ILIES', 'HM', '2025-03-04', 'FA2016527 - CL01392', null, true, '2025-03-28', '2025-03-28', 'CL01392', '01 34 77 19 92', '07 49 88 33 19'],
            ['DR ANNE GAZZOLA', 'HM', '2025-03-26', 'FA2016526 CL00768', null, true, '2025-03-28', '2025-03-28', 'CL00768', '01 47 02 72 66', '06 20 89 10 98'],
            ['DR LAOUAR CHACHOUA HASSIBA', 'HM', '2025-01-10', 'FA2016600 CL00873', null, true, '2025-04-18', '2025-04-18', 'CL00873', '01 48 45 50 23', '06 65 10 89 66'],
            ['DR BISMUTH JULIEN 75009 PARIS', 'VM', '2025-06-27', 'FA2016842 CL00015', null, true, '2025-06-23', '2025-06-27', 'CL00015', '148745763', '06 27 55 35 71'],
            ['MUYAL SAAL Judith 91 Gif sur YVETTE', 'VM', '2025-07-11', 'CM00004133', null, true, '2025-08-01', null, 'CL01434', '164469143', '620374864'],
        ];

        foreach ($clientsData as $data) {
            $client = new Client();
            $client->setNomClient($data[0]);
            $client->setCommercial($data[1]);
            $client->setDateProposition($data[2] ? new \DateTime($data[2]) : null);
            $client->setNumeroContrat($data[3]);
            $client->setRefuse(false);
            $client->setAccordSigne($data[5]);
            $client->setDemarrageContrat($data[6] ? new \DateTime($data[6]) : null);
            $client->setPremiereFacturation($data[7] ? new \DateTime($data[7]) : null);
            $client->setNumeroClient($data[8]);
            $client->setTelephoneCabinet($data[9]);
            $client->setGsmPraticien($data[10]);

            

            $manager->persist($client);
        }

        $manager->flush();
    }
}
