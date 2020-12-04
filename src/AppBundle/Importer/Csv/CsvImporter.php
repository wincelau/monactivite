<?php

namespace AppBundle\Importer\Csv;

use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Importer\Importer;
use AppBundle\Entity\Activity;
use AppBundle\Entity\ActivityAttribute;
use AppBundle\Entity\Source;
use Sabre\VObject;

class CsvImporter extends Importer
{
    public function getName() {

        return 'Csv';
    }

    public function getDescription() {

        return "Récupère les lignes d'un fichier CSV";
    }

    public function getParameters() {

        return array(
            'path' => array("required" => true, "label" => "Chemin ou url", "help" => "Chemin ou url du fichier csv"),
            'name' => array("required" => false, "label" => "Nom", "help" => "Nom (optionnelle)"),
            'type' => array("required" => false, "label" => "Type", "help" => "Type (optionnelle)"),
            'date' => array("required" => true, "label" => "Colonne date", "help" => "Numéro de la colonne a utiliser pour la date"),
            'title' => array("required" => true, "label" => "Colonne titre", "help" => "Numéro de la colonne a utiliser pour le titre"),
            'content' => array("required" => false, "label" => "Colonne contenu", "help" => "Numéro de la colonne a utiliser pour le contenu (optionnelle)"),
            'attributes' => array("required" => false, "label" => "Colonnes attributs", "help" => "Liste de numéros de colonnes à utiliser comme attribut (optionnelle)"),
        );
    }

    public function updateTitle(Source $source) {
        $source->setTitle($source->getParameter('path'));
    }

    public static function detectSeparator($file) {
        $handle = fopen($file, 'r');
        $buffer = fread($handle, 500);
        fclose($handle);

        $separator = ";";
        $virgule = explode(',', $buffer);
        $ptvirgule = explode(';', $buffer);
        $tabulation = explode('\t', $buffer);
        if (count($virgule) > count($ptvirgule) && count($virgule) > count($tabulation)) {
          $separator = ',';
        } else if (count($tabulation) > count($ptvirgule)) {
          $separator = '\t';
        }

        return $separator;
    }

    public function getDataValue($source, $data, $key) {
        if(!$this->hasColumnIndex($source, $key)) {
            return null;
        }

        $index = $this->getColumnIndex($source, $key);

        if(!isset($data[$index])) {

            return null;
        }

        return $data[$index];
    }


    public function getColumnIndex($source, $key) {
        if(!$source->getParameter($key)) {

            return null;
        }

        if($key == 'attributes') {
            $indexes = array();
            foreach(preg_split("/[^0-9]+/", $source->getParameter($key)) as $number) {
                $indexes[] = intval($number) - 1;
            }

            return $indexes;
        }

        return intval($source->getParameter($key)) - 1;
    }

    public function hasColumnIndex($source, $key) {

        return $this->getColumnIndex($source, $key) !== null;
    }

    public function run(Source $source, OutputInterface $output, $dryrun = false, $checkExist = true, $limit = false) {
        $output->writeln(sprintf("<comment>Started import csv in %s</comment>", $source->getTitle()));

        $separator = self::detectSeparator($source->getParameter('path'));

        $nb = 0;
        $firstLine = true;
        $header = null;
        $handle = fopen($source->getParameter('path'), "r");
        while (($data = fgetcsv($handle, 0, $separator)) !== false) {
            if($firstLine) {
                $firstLine = false;
                $header = $data;
                continue;
            }

            $activity = new Activity();
            $date = $this->getDataValue($source, $data, 'date');
            if($date) {
                $activity->setExecutedAt(new \DateTime($date));
            }
            $activity->setTitle($this->getDataValue($source, $data, 'title'));
            $activity->setContent($this->getDataValue($source, $data, 'content'));

            if($source->getParameter('name')) {
                $name = new ActivityAttribute();
                $name->setName("Name");
                $name->setValue($source->getParameter('name'));
            }

            if($source->getParameter('type')) {
                $type = new ActivityAttribute();
                $type->setName("Type");
                $type->setValue($source->getParameter('type'));
            }
            $attributes = array();
            if($this->hasColumnIndex($source, 'attributes')) {
                foreach($this->getColumnIndex($source, 'attributes') as $index) {
                    if(!isset($header[$index])) {
                        continue;
                    }
                    if(!isset($data[$index])) {
                        continue;
                    }
                    $attribute = new ActivityAttribute();
                    $attribute->setName($header[$index]);
                    $attribute->setValue($data[$index]);
                    $attributes[] = $attribute;
                }
            }

            if(isset($name)) {
                $activity->addAttribute($name);
            }

            if(isset($type)) {
                $activity->addAttribute($type);
            }

            foreach($attributes as $attribute) {
                $activity->addAttribute($attribute);
            }

            try {
                $this->am->addFromEntity($activity, $checkExist);

                if(isset($name)) {
                    $this->em->persist($name);
                }
                if(isset($type)) {
                    $this->em->persist($type);
                }
                foreach($attributes as $attribute) {
                    $this->em->persist($attribute);
                }

                $this->em->persist($activity);

                if(!$dryrun) {
                    $this->em->flush($activity);
                }

                $nb++;

                if($limit && $nb > $limit) {
                    break;
                }
            } catch (\Exception $e) {
                if($output->isVerbose()) {
                    $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
                }
            }
        }
        fclose($handle);

        if(!$dryrun) {
            $this->em->persist($source);
            $this->em->flush();
        }

        $output->writeln(sprintf("<info>%s new activity imported</info>", $nb));
    }

    public function getRootDir() {

        return dirname(__FILE__);
    }

    public function check(Source $source) {
        parent::check($source);

        try {
            fopen($source->getParameter('path'), "r");
        } catch(\Exception $e) {
            throw new \Exception(sprintf("Le fichier csv %s n'a pas pu être lu : %s", $source->getParameter('path'), $e->getMessage()));
        }
    }

}
