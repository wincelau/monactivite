<?php 

namespace AppBundle\Manager;

use AppBundle\Entity\Filter;
    
class FilterManager
{
    protected $em;
    protected $repository;
    protected $slugger;

    public function __construct($em, $slugger) {
        $this->em = $em;
        $this->repository = $em->getRepository('AppBundle:Filter');
        $this->slugger = $slugger;
    }

    public function executeFilter(Filter $filter) {
        $activities = $em->getRepository('AppBundle:Activity')->findByFilter($filter);

        foreach($activities as $activity) {
            $activity->addTag($filter->getTag());
            $this->em->persist($activity);
        }

        return count($activities);
    }
}