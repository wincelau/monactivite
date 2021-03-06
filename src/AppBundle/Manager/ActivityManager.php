<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Activity;
use AppBundle\Entity\Filter;

class ActivityManager
{
    protected $em;
    protected $repository;
    protected $slugger;

    public function __construct($em, $slugger) {
        $this->em = $em;
        $this->repository = $em->getRepository('AppBundle:Activity');
        $this->slugger = $slugger;
    }

    public function addFromEntity(Activity $activity, $checkExist = true) {
        if(!$activity->getExecutedAt()) {
            throw new \Exception("Date is required");
        }

        if(!$activity->getTitle()) {
            throw new \Exception("Title is required");
        }

        $activity->setSlug(md5($this->slugger->slugify(sprintf("%s_%s", $activity->getExecutedAt()->format('Y-m-d H:i-s'), $activity->getTitle()))));

        if($checkExist && $this->repository->findBySlug($activity->getSlug())) {
            throw new \Exception("Already exist");
        }

        return $activity;
    }

    public function createView($activities, $viewMode = 'daily') {
        $activitiesByDates = array();
        $withValue = false;
        foreach($activities as $activity) {
            $keyDate = $activity->getKeyDate($viewMode);
            if(!array_key_exists($keyDate, $activitiesByDates)) {
                $activitiesByDates[$keyDate] = array('activites' => array(), 'tags' => array());
            }
            $activitiesByDates[$keyDate]['activities'][] = $activity;
            foreach($activity->getTags() as $tag) {
                if(!array_key_exists($tag->getKey(), $activitiesByDates[$keyDate]['tags'])) {
                    $activitiesByDates[$keyDate]['tags'][$tag->getKey()] = array('nb' => 0, 'entity' => $tag);
                }
                if($activity->getValue()) {
                    $withValue = true;
                }
                $activitiesByDates[$keyDate]['tags'][$tag->getKey()]['nb'] += ($withValue) ? $activity->getValue() : 1;
            }
        }

        foreach($activitiesByDates as $key => $activitiesByDate) {
            uasort($activitiesByDates[$key]['tags'], "\AppBundle\Manager\ActivityManager::sortTagByNb");
        }

        return $activitiesByDates;
    }

    public static function sortTagByNb($a, $b) {

        return $a['nb'] < $b['nb'];
    }
}
