<?php

namespace SmartwifiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;

use SmartwifiBundle\Document\Wlc;
use SmartwifiBundle\Document\Wlcclients;
use SmartwifiBundle\Document\Summary;
use Doctrine\Common\Collections;

//For documentation path
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

date_default_timezone_set('UTC');


class SmartwifiRestOneWlcController extends Controller
{

   /**
    * Information of the client measure for one WLC. You need the IP of WLC and you can find it on this path /wlc/list
    * @Get("/wlc/{wlcip}/clients/all/{order}")
    * @ApiDoc(
    *  resource=true,
    *  description="List of all clients measure for one WLC",
    *  requirements={
    *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP address of WLC"},
    *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order of results (DESC|ASC)"},
    * })
    */
    public function getByWlcClientsAllAction($wlcip,$order)
    {
        $value = $wlcip;
        $logger = $this->get('logger');
        if (!filter_var($value, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Wlcclients')
        ->findBy(
            array('wlc_ip' => $value ),array('date_of_record' => $order)
        ); 

        if (!$documents) {
            $result = ( array("message" => "Result not FOUND for WLC(".$value.")") );
            return $result;
        }
        return $documents;
    }


   /**
    * Information of the last client measure for one WLC. 
    * @Get("/wlc/{wlcip}/clients/last")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of the last client measure for one WLC",
    *  requirements={
    *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP of WLC"}
    *   }
    * )
    */
    public function getByWlcClientsLastAction($wlcip)
    {
        if (!filter_var($wlcip, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }
        

        $summaries = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findBy(
            array(),
            array('summary_number'=>'DESC')    
        );
        
        //GETTING WLC Clients
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summaries[0]->getSummaryStart(),$summaries[0]->getSummaryStop())
            ->field('wlc_ip')->equals($wlcip)
            ->getQuery()
            ->execute();
  
        $wlcipclients = array();
        foreach($documents as $clients){
            array_push($wlcipclients,$clients);
        }
        
        return $wlcipclients;
        
    }

   /**
    * Information of all client measure for one WLC of today
    * @Get("/wlc/{wlcip}/clients/today")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of all client measure of today",
    *  requirements={
    *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP of WLC"}
    *   }
    * )
    */
    public function getByWlcClientsFromTodayAction($wlcip)
    {

        if (!filter_var($wlcip, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }        


        //QUERY BUILDER
        $summaries = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('today'),new \DateTime('tomorrow'))
            ->sort('summary_number', 'ASC')
            ->getQuery()
            ->execute();
        
        $wlcipclients = array();
        
        foreach($summaries as $summary){ 
            //array_push($summaries,$summary);
            //GETTING CLIENTS

            $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summary->getSummaryStart(),$summary->getSummaryStop())
            ->field('wlc_ip')->equals($wlcip)
            ->getQuery()
            ->execute();
        
            foreach($documents as $clients){
                array_push($wlcipclients,$clients);
            }    
        } 
        return $wlcipclients;
    }


     /**
    * Information of all client measure for one WLC of yesterday
    * @Get("/wlc/{wlcip}/clients/yesterday")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of all client measure of yesterday",
    *  requirements={
    *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP of WLC"}
    *   }
    * )
    */
    public function getByWlcClientsFromYesterdayAction($wlcip)
    {
        if (!filter_var($wlcip, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }


        //QUERY BUILDER
        $summaries = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('yesterday'),new \DateTime('today'))
            ->sort('summary_number', 'ASC')
            ->getQuery()
            ->execute();

        $wlcipclients = array();

        foreach($summaries as $summary){
            //array_push($summaries,$summary);
            //GETTING CLIENTS

            $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summary->getSummaryStart(),$summary->getSummaryStop())
            ->field('wlc_ip')->equals($wlcip)
            ->getQuery()
            ->execute();

            foreach($documents as $clients){
                array_push($wlcipclients,$clients);
            }
        }
        return $wlcipclients;
    }


    /**
    * Information of client between to Summaries, by Summary's id and for only one WLC
    * @Get("/wlc/{wlcip}/clients/id/{idsummaryfrom}/to/{idsummaryto}")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of client between to Summaries, by Summary's id.",
    *  requirements={
    *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP of WLC"},
    *      {"name"="idfrom", "dataType"="string", "requirement"="true", "description"="Id of Summary(FROM)"},
    *      {"name"="idto", "dataType"="string", "requirement"="true", "description"="Id of Summary(TO)"}
    *  }
    * )
    */
    public function getByWlcClientsFromToByIdAction($wlcip,$idsummaryfrom,$idsummaryto)
    {
    
        if (!filter_var($wlcip, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }


        //QUERY BUILDER
        $summaryfrom = 0; 
        $summaryto = 0; 

        $summaryfrom = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneById($idsummaryfrom);

        if (!$summaryfrom )
        {
            $result = ( array("message" => "Summary not FOUND for ID (".$idsummaryfrom.")") );
            return $result;
        }

        $summaryto = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneById($idsummaryto);

        if (!$summaryto )
        {
            $result = ( array("message" => "Summary not FOUND for ID (".$idsummaryto.")") );
            return $result;
        }


        $wlcipclients = array();
        //GETTING CLIENTS

        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summaryfrom->getSummaryStart(),$summaryto->getSummaryStop())
            ->field('wlc_ip')->equals($wlcip)
            ->getQuery()
            ->execute();

        foreach($documents as $clients){
            array_push($wlcipclients,$clients);
        }
        return $wlcipclients;
    }

   /**
    * Information of client between to Summaries, by Summary's number and for only one WLC
    * @Get("/wlc/{wlcip}/clients/number/{numbersummaryfrom}/to/{numbersummaryto}")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of client between to Summaries, by Summary's number.",
    *  requirements={
    *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP of WLC"},
    *      {"name"="numberfrom", "dataType"="string", "requirement"="true", "description"="Number of Summary(FROM)"},
    *      {"name"="numberto", "dataType"="string", "requirement"="true", "description"="Number of Summary(TO)"}
    *  }
    * )
    */
    public function getByWlcClientsFromToByNumberAction($wlcip,$numbersummaryfrom,$numbersummaryto)
    {

        if (!filter_var($wlcip, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }



        //QUERY BUILDER
        $summaryfrom = 0;
        $summaryto = 0;

        $summaryfrom = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneBy(
            array('summary_number' => (int)$numbersummaryfrom)
        );



        $summaryto = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneBy(
            array('summary_number' => (int)$numbersummaryto)
        );

        $wlcipclients = array();
        //GETTING CLIENTS

        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summaryfrom->getSummaryStart(),$summaryto->getSummaryStop())
            ->field('wlc_ip')->equals($wlcip)
            ->getQuery()
            ->execute();

        foreach($documents as $clients){
            array_push($wlcipclients,$clients);
        }
        return $wlcipclients;
    }


}


