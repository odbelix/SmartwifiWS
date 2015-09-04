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


class SmartwifiRestWlcController extends Controller
{

    /**
    * List of WLCs
    * @Get("/wlc/list")
    * @ApiDoc(
    *  resource=true,
    *  description="List of all WLC recorded in the system"
    * )
    */
    public function getWlcListAction()
    {
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Wlc')
        ->findAll();

        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }
        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();
        return $documents;
    }
   /**
    * List of all clients amount
    * @Get("/wlc/clients/all")
    * @ApiDoc(
    *  resource=true,
    *  description="List of all clients amount"
    * )
    */
    public function getWlcClientsAllAction()
    {
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Wlcclients')
        ->findAll();

        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }

        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();

        return $documents;
    }


   /**
    * Information of the last client measure for each wlc
    * @Get("/wlc/clients/last")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of the last client measure"
    * )
    */
    public function getWlcClientsLastAction()
    {
        $summaries = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findBy(array(),
            array('summary_number'=>'DESC')
        );

        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();

        //GETTING WLC Clients
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summaries[0]->getSummaryStart(),$summaries[0]->getSummaryStop())
            ->getQuery()
            ->execute();

        $wlcclients = array();
        foreach($documents as $clients){
            array_push($wlcclients,$clients);
        }
        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();

        return $wlcclients;

    }

   /**
    * Information of all client measure for each WLC of today
    * @Get("/wlc/clients/today")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of all client measure of today"
    * )
    */
    public function getWlcClientsFromTodayAction(){
        //QUERY BUILDER
        $summaries = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('today'),new \DateTime('tomorrow'))
            ->sort('summary_number', 'ASC')
            ->getQuery()
            ->execute();

        $wlcclients = array();

        foreach($summaries as $summary){
            //array_push($summaries,$summary);
            //GETTING CLIENTS

            $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summary->getSummaryStart(),$summary->getSummaryStop())
            ->getQuery()
            ->execute();

            foreach($documents as $clients){
                array_push($wlcclients,$clients);
            }
        }
        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();

        return $wlcclients;
    }


    /**
    * Information of all client measure for each WLC of yesterday.
    * @Get("/wlc/clients/yesterday")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of all client measure of yesterday"
    * )
    */
    public function getWlcClientsFromYesterdayAction(){
        //QUERY BUILDER
        $summaries = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Summary')
            //->hydrate(false)
            ->field('summary_start')->range(new \DateTime('yesterday'),new \DateTime('today'))
            ->sort('summary_number', 'ASC')
            ->getQuery()
            ->execute();

        $wlcclients = array();

        foreach($summaries as $summary){
            //array_push($summaries,$summary);
            //GETTING CLIENTS

            $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summary->getSummaryStart(),$summary->getSummaryStop())
            ->getQuery()
            ->execute();

            foreach($documents as $clients){
                array_push($wlcclients,$clients);
            }
        }

        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();

        return $wlcclients;
    }


    /**
    * Information of client between to Summaries, by Summary's id
    * @Get("/wlc/clients/id/{idsummaryfrom}/to/{idsummaryto}")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of client between to Summaries, by Summary's id.",
    *  requirements={
    *      {"name"="idfrom", "dataType"="string", "requirement"="true", "description"="Id of Summary(FROM)"},
    *      {"name"="idto", "dataType"="string", "requirement"="true", "description"="Id of Summary(TO)"}
    *  }
    * )
    */
    public function getWlcClientsFromToByIdAction($idsummaryfrom,$idsummaryto){
        //QUERY BUILDER
        $summaryfrom = 0;
        $summaryto = 0;

        $summaryfrom = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneById($idsummaryfrom);


        $summaryto = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneById($idsummaryto);

        $wlcclients = array();
        //GETTING CLIENTS

        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summaryfrom->getSummaryStart(),$summaryto->getSummaryStop())
            ->getQuery()
            ->execute();

        foreach($documents as $clients){
            array_push($wlcclients,$clients);
        }

        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();

        return $wlcclients;
    }

   /**
    * Information of client between to Summaries, by Summary's number.
    * @Get("/wlc/clients/number/{numbersummaryfrom}/to/{numbersummaryto}")
    * @ApiDoc(
    *  resource=true,
    *  description="Information of client between to Summaries, by Summary's number.",
    *  requirements={
    *      {"name"="numbersummaryfrom", "dataType"="integer", "requirement"="true", "description"="Number of Summary(FROM)"},
    *      {"name"="numbersummaryto", "dataType"="integer", "requirement"="true", "description"="Number of Summary(TO)"}
    *  }
    * )
    */
    public function getWlcClientsFromToByNumberAction($numbersummaryfrom,$numbersummaryto){
        //QUERY BUILDER
        $summaryfrom = 0;
        $summaryto = 0;

        $summaryfrom = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneBy(
            array('summary_number' => (int)$numbersummaryfrom)
        );


        if ( !$summaryfrom ) {
            $result = ( array("message" => "Summary does not exists for number".$numbersummaryfrom) );
            return $result;
        }


        $summaryto = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findOneBy(
            array('summary_number' => (int)$numbersummaryto)
        );

        if ( !$summaryto ) {
            $result = ( array("message" => "Summary does not exists for number".$numbersummaryto) );
            return $result;
        }


        $wlcclients = array();
        //GETTING CLIENTS

        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Wlcclients')
            ->field('date_of_record')->range($summaryfrom->getSummaryStart(),$summaryto->getSummaryStop())
            ->getQuery()
            ->execute();

        foreach($documents as $clients){
            array_push($wlcclients,$clients);
        }

        $em = $this->get('doctrine_mongodb')->getManager();
        $em->getConnection()->close();
        return $wlcclients;
    }


}
