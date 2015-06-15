<?php 

namespace AppBundle\Manager;

use AppBundle\Entity\Source;
use Symfony\Component\Console\Output\OutputInterface;

class MainManager
{
    protected $am;
    protected $im;
    protected $fm;
    protected $sm;

    public function __construct($am, $im, $fm, $sm) {
        $this->am = $am;
        $this->im = $im;
        $this->fm = $fm;
        $this->sm = $sm;
    }

    public function executeSource(Source $source, OutputInterface $output, $dryRun = false) {

        return $this->sm->executeOne($source, $output, $dryRun);
    }

    public function executeAllSources(OutputInterface $output, $dryRun = false) {

        return $this->sm->executeAll($output, $dryRun);
    }

    public function executeFilter(Filter $filter, OutputInterface $output) {
        
        return $this->fm->executeOne($filter, $output);
    }

    public function executeAllFilters(OutputInterface $output) {
        
        return $this->fm->executeAll($output);
    }

    public function executeOne(Source $source, OutputInterface $output, $dryRun = false) {
        $this->executeSource($source, $output, $dryRun);
        $this->executeAllFilters($output);
    }

    public function executeAll(OutputInterface $output, $dryRun = false) {
        $this->executeAllSources($output, $dryRun);
        $this->executeAllFilters($output);
    }

}
