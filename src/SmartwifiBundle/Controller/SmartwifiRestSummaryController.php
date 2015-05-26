<?php

namespace SmartwifiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
//use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Get;


use Doctrine\Common\Collections;
use SmartwifiBundle\Document\Summary;

//For documentation path
use Nelmio\ApiDocBundle\Annotation\ApiDoc;



date_default_timezone_set('UTC');

class SmartwifiRestSummaryController extends Controller
{
    
   /**
    * List of all Summaries recorded.
    * @Get("/summaries/all")
    * @ApiDoc(
    *  resource=true,
    *  description="List of all summaries recorded, whitout limits"
    * )
    */
    public function getSummariesAction()
    {
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findAll();
        
        if ( !$documents ) {
            $result = ( array("message" => "Summaries not found") );
            return $result;
        }
        return $documents;
    }
   /**
    * Information of one Summary
    * @Get("/summary/by/id/{id}")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summary",
    *  requirements={
    *      {"name"="id", "dataType"="string", "requirement"="true", "description"="Id of Summary"}
    *  }
    * )
    */    
    public function getSummaryByIdAction($id)
    {
        $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneById($id);

        if (!$documents) {
            $result = ( array("message" => "Summary not found for ID:".$id) );
            return $result;        
        }
        return $documents;
    
    }

   /**
    * Information of one Summary
    * @Get("/summary/by/number/{number}")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summary",
    *  requirements={
    *      {"name"="number", "dataType"="integer", "requirement"="true", "description"="Number of Summary"}
    *  }
    * )
    */   
    public function getSummaryByNumberAction($number)
    {
        $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneBy(
        array('summary_number' => (int)$number)
        );

        if (!$documents) {
            $result = ( array("message" => "Summary not found for NUMBER:".$number) );
            return $result;

        }
        return $documents;
    }

   /**
    * Information of Summaries of today
    * @Get("/summaries/today")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summaries of today",
    * )
    */
    public function getSummariesOfTodayAction()
    {

        //QUERY BUILDER
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('today'),new \DateTime('tomorrow'))
            ->sort('summary_number', 'ASC')
            ->getQuery()
            ->execute();
        $summaries = array();

        if ( count($documents) == 0)
        {
            $result = ( array("message" => "Summaries for today not found") );
            return $result;
        }

        $logger = $this->get('logger');
        $logger->info(count($documents));
        $today = new \DateTime('now');
        $logger->info(date_default_timezone_get());
//       $logger->info(var_dump($summary));

        foreach($documents as $summary){ 
            array_push($summaries,$summary);
        }       
        return $summaries; 

    } 

   /**
    * Information of Summaries of yesterday
    * @Get("/summaries/yesterday")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summaries of yesterday"
    * )
    */
    public function getSummariesOfYesterdayAction()
    {
        //QUERY BUILDER
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('yesterday'),new \DateTime('today'))
            ->sort('summary_number', 'ASC')
            ->getQuery()
            ->execute();
        $summaries = array();
       
        if ( count($documents) == 0)
        {
            $result = ( array("message" => "Summaries of yesterday not found") );
            return $result;
        }
 
        
        $logger = $this->get('logger');
        $today = new \DateTime('now');
        $logger->info(date_default_timezone_get());
//       $logger->info(var_dump($summary));
        
        foreach($documents as $summary){
            array_push($summaries,$summary);
        }       
        return $summaries;
    }

   /**
    * Information of summaries between to summaries ID
    * @Get("/summaries/range/{idfrom}/to/{idto}")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summaries between two summaries id",
    *  requirements={
    *      {"name"="idfrom", "dataType"="string", "requirement"="true", "description"="Id of Summary(FROM)"},
    *      {"name"="idto", "dataType"="string", "requirement"="true", "description"="Id of Summary(TO)"}
    *  }
    * )
    */
    public function getSummariesInRangeAction($idfrom,$idto)
    {
        //VALIDATE ID
        $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneById($idfrom);

        if (!$documents) {
            $result = ( array("message" => "ID(".$idfrom.") not found") );
            return $result;
        }

        $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneById($idto);
        
        if (!$documents) {
            $result = ( array("message" => "ID(".$idto.") not found") );
            return $result;
        }

        //QUERY BUILDER
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            ->field('id')->range($idfrom,$idto)
            ->sort('summary_number', 'ASC')
            ->getQuery()
            ->execute();



        if ( count($documents) == 0)
        {
            $result = ( array("message" => "Summaries not found from ".$idfrom." to ".$idto) );
            return $result;
        }


        $summaries = array();
        foreach($documents as $summary){
            array_push($summaries,$summary);
        }
        return $summaries;
    }

//end Controller
}

