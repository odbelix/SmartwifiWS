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
    * List of all Summaries recorded, default values are Order=DESC and Limit=2016(One week ago)
    * @Get("/summary/all/{order}/{limit}", defaults={"order" = "DESC","limit" = 2016})
    * @ApiDoc(
    *  resource=true,
    *  tags={
    *      "stable" = "#99CC00"
    *   },
    *  description="List of all summaries recorded, whitout limits",
    *  requirements={
    *      {"name"="order", "dataType"="string", "requirement"="false", "description"="Order of results (DESC|ASC)"},
    *      {"name"="limit", "dataType"="string", "requirement"="false", "description"="Limit of results, default 2016"}
    *  }
    * )
    */
    public function getSummariesAction($order,$limit)
    {
        
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        if(!is_numeric($limit)) {
            $result = ( array("message" => "wrong LIMIT format (Only number)") );
            return $result;
        }
        
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findBy(array(),
                 array('summary_number'=>$order),$limit);
        
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
    * Information of Summaries of today. The default order is DESC and it has not limit.
    * @Get("/summary/today/{order}/{limit}",defaults={"order" = "DESC","limit" = 0})
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summaries of today",
    *  requirements={
    *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order of results (DESC|ASC)","default"="DESC"},
    *      {
    *       "name"="limit",
    *       "dataType"="integer",
    *       "requirement"="true",
    *       "description"="how many objects to return"
    *      }
    *  }
    * )
    */
    public function getSummariesOfTodayAction($order,$limit)
    {
        $logger = $this->get('logger');
        $logger->info($order);
       
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        if(!is_numeric($limit)) {
            $result = ( array("message" => "wrong LIMIT format (Only number)") );
            return $result;
        }
        
        //QUERY BUILDER
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('today'),new \DateTime('tomorrow'))
            ->limit($limit)
            ->sort('summary_number',$order)
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
    * @Get("/summary/yesterday/{order}/{limit}",defaults={"order" = "DESC","limit" = 0})
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summaries of yesterday",
    *  requirements={
    *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order of results (DESC|ASC)"},
    *      {"name"="limit", "dataType"="string", "requirement"="true", "description"="Limit of results, default 0, means unlimited"}
    *  }
    * )
    */
    public function getSummariesOfYesterdayAction($order,$limit)
    {
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        if(!is_numeric($limit)) {
            $result = ( array("message" => "wrong LIMIT format (Only number)") );
            return $result;
        }
        
        //QUERY BUILDER
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('yesterday'),new \DateTime('today'))
            ->limit($limit)
            ->sort('summary_number', $order)
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
    * @Get("/summary/range/{idfrom}/to/{idto}/{order}",defaults={"order" = "DESC"})
    * @ApiDoc(
    *  resource=true,
    *  description="Information of Summaries between two summaries id",
    *  requirements={
    *      {"name"="idfrom", "dataType"="string", "requirement"="true", "description"="Id of Summary(FROM)"},
    *      {"name"="idto", "dataType"="string", "requirement"="true", "description"="Id of Summary(TO)"},
    *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order of results, default DESC"}
    *  }
    * )
    */
    public function getSummariesInRangeAction($idfrom,$idto,$order)
    {
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        
        
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
            ->sort('summary_number', $order)
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


