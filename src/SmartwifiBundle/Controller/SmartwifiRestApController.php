<?php
namespace SmartwifiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations\Get;

//For documentation path
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

//Entities/Document
use SmartwifiBundle\Document\Ap;
use SmartwifiBundle\Document\Clients;

//For date problem*
date_default_timezone_set('UTC');

class SmartwifiRestApController extends Controller
{    
    /**
    * The list of AP registered on all WLCs.
    * @Get("/ap/list")
    * @ApiDoc(
    *  resource=true,
    *  description="This is a list of all AP"
    * )
    */
    public function getApListAction()
    {
        //QUERY BUILDER - UNIC IP on AP List
        $iplist = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Ap')
            ->distinct('ap_ip')
            ->getQuery()
            ->execute();
        
        $aps = array();
        
        foreach($iplist as $ip){
            //array_push($aps,$ap);/
            //GETTING AP
            $ap = $this->get('doctrine_mongodb')
                ->getRepository('SmartwifiBundle:Ap')
                ->findOneBy(
                array('ap_ip' => $ip),array("date_of_record" => "ASC")
            );
            array_push($aps,$ap);
        }
        return $aps;
    }

   /**
    * The list of AP registered on one WLC, by IP
    * @Get("/ap/{wlc}/list")
    * @ApiDoc(
    *  resource=true,
    *  description="This return AP of WLC",
    *   requirements={
    *      {"name"="wlc", "dataType"="string", "requirement"="true", "description"="Ip Address of WLC"}
    *  }
    * )
    */
    public function getApByWlcListAction($wlc)
    {
        //QUERY BUILDER - UNIC IP on AP List
        $iplist = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Ap')
            ->distinct('ap_ip')
            ->field('wlc_ip')->equals($wlc)
            ->getQuery()
            ->execute();

        $aps = array();

        foreach($iplist as $ip){
            //array_push($aps,$ap);/
            //GETTING AP
            $ap = $this->get('doctrine_mongodb')
                ->getRepository('SmartwifiBundle:Ap')
                ->findOneBy(
                array('ap_ip' => $ip),array("date_of_record" => "ASC")
            );
            array_push($aps,$ap);
        }
        return $aps;
    }


  /**
    * Information of one AP, by MAC Radio
    * @Get("/ap/by/mac/radio/{mac}")
    * @ApiDoc(
    *  resource=true,
    *  description="This return AP information",
    *   requirements={
    *      {"name"="mac", "dataType"="string", "requirement"="true", "description"="MAC of Radio"}
    *  }
    * )
    */
    public function getApByRadioMacAction($mac)
    {
        //Validate MAC
        if ( !preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $mac) )
        {   
            $result = ( array("message" => "wrong MAC format") );
            return $result;
        }

         $ap = $this->get('doctrine_mongodb')
                ->getRepository('SmartwifiBundle:Ap')
                ->findOneBy(
                array('ap_macradio' => $mac)
         );
        if ( !$ap ) {
            $result = ( array("message" => "AP not found with Radio MAC:".$mac) );
            return $result;
        }
        return $ap;
    }

  /**
    * Information of one AP, by MAC Ethernet
    * @Get("/ap/by/mac/ethernet/{mac}")
    * @ApiDoc(
    *  resource=true,
    *  description="This return AP information",
    *   requirements={
    *      {"name"="mac", "dataType"="string", "requirement"="true", "description"="MAC of Ethernet"}
    *  }
    * )
    */
    public function getApByEthernetMacAction($mac)
    {
        //Validate MAC
        if ( !preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $mac) )
        {   
            $result = ( array("message" => "wrong MAC format") );
            return $result;
        }

         $ap = $this->get('doctrine_mongodb')
                ->getRepository('SmartwifiBundle:Ap')
                ->findOneBy(
                array('ap_macethernet' => $mac)
         );
        if ( !$ap ) {
            $result = ( array("message" => "AP not found with Ethernet MAC:".$mac) );
            return $result;
        }
        return $ap;
    }    

  /**
    * Information of AP for each time that it is available
    * @Get("/ap/record/by/ip/{ip}")
    * @ApiDoc(
    *  resource=true,
    *  description="AP information when it is available",
    *   requirements={
    *      {"name"="ip", "dataType"="string", "requirement"="true", "description"="IP of AP"}
    *  }
    * )
    */
    public function getApRecordByIpAction($ip)
    {
        //Validate IP
        $value = $ip;
        $logger = $this->get('logger');
        $logger->info(strlen($value));
        $logger->info(substr_count($value,"."));
 
        if ( strlen($value) > 15 || substr_count($value,".") != 3 )
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }       

        $aprecords = $this->get('doctrine_mongodb')
                ->getRepository('SmartwifiBundle:Ap')
                ->findBy(
                array('ap_ip' => $ip)
        );
        if ( !$aprecords ) {
            $result = ( array("message" => "AP Records not found with IP:".$ip) );
            return $result;
        }
        return $aprecords;
    }

  /**
    * Information of client recorder for one AP.
    * @Get("/ap/summary/by/mac/radio/{mac}/{limit}")
    * @ApiDoc(
    *  resource=true,
    *  description="Client count information for one AP",
    *   requirements={
    *      {"name"="mac", "dataType"="string", "requirement"="true", "description"="MAC of Radio"},
    *      {"name"="limit", "dataType"="string", "requirement"="true", "description"="amount of records"}
    *  }
    * )
    */
    public function getApSummaryByMacRadioAction($mac,$limit)
    {
        //Validate MAC
        $logger = $this->get('logger');
        //$logger->info(substr_count($value,"."));

        $macarr = split(":",$mac);
        if ( count($macarr) != 6) {
            $result = ( array("message" => "wrong MAC format") );
            return $result;
        }

        $apsummarydata = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Apsummary')
            ->field('ap_macradio')->equals($mac)
            ->limit($limit)
            ->getQuery()
            ->execute();
        
        $apsummaries = array();
        foreach($apsummarydata  as $data)
        {
            array_push($apsummaries,$data);
        }
        
        if ( !$apsummaries ) {
            $result = ( array("message" => "AP Summaries not found with MAC:".$mac) );
            return $result;
        }

        return $apsummaries;
    }

   /**
    * The information of the last record, from the last Summary for one WLC
    * @Get("/ap/{wlcip}/lastrecord/{order}/{limit}")
    * @ApiDoc(
    *  resource=true,
    *  description="Get Client count information from the last Record",
    *   requirements={
    *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP of WLC"},
    *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order of records (DESC|ASC) by total of Clients for each AP"},
    *      {"name"="limit", "dataType"="string", "requirement"="true", "description"="amount of records, 0 for unlimited"},
    *  }
    * )
    */
    public function getApLastRecordsByWlcAction($wlcip,$order,$limit)
    {
        
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        
        
        $summaries = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findBy(array(),
            array('summary_number'=>'DESC')
        );
        //GETTING WLC Clients
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Apsummary')
            ->field('date_of_record')->range($summaries[0]->getSummaryStart(),$summaries[0]->getSummaryStop())
            ->field('wlc_ip')->equals($wlcip)
            ->limit($limit)
            ->sort("ap_clientscount", $order)
            ->getQuery()
            ->execute();

        $apsummaries = array();
        foreach($documents as $aps){
            array_push($apsummaries,$aps);
        }

        return $apsummaries;
    }

    /**
    * The information of the last record, from the last Summary for all WLCs
    * @Get("/ap/lastrecord/{order}/{limit}")
    * @ApiDoc(
    *  resource=true,
    *  description="Get Client count information from the last Record of all WLCs",
    *   requirements={
    *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order of records (DESC|ASC) by total of Clients for each AP"},
    *      {"name"="limit", "dataType"="string", "requirement"="true", "description"="amount of records, 0 for unlimited"},
    *  }
    * )
    */
    public function getApLastRecordsAction($order,$limit)
    {
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        
        
        
        $summaries = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findBy(array(),
            array('summary_number'=>'DESC')
        );
        //GETTING WLC Clients
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Apsummary')
            ->field('date_of_record')->range($summaries[0]->getSummaryStart(),$summaries[0]->getSummaryStop())
            ->limit($limit)
            ->sort("ap_clientscount", $order)
            ->getQuery()
            ->execute();

        $apsummaries = array();
        foreach($documents as $aps){
            array_push($apsummaries,$aps);
        }
        return $apsummaries;
        
    }
    
    /**
    * The information of amount of client for one AP
    * @Get("/ap/record/today/by/radio/{mac}/{order}")
    * @ApiDoc(
    *  resource=true,
    *  description="Get Client count information of today for one AP",
    *   requirements={
    *      {"name"="mac", "dataType"="string", "requirement"="true", "description"="MAC of Radio"},
    *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order (DESC|ASC) by date"},
    *  }
    * )
    */
    public function getApTodayRecordsByAction($mac,$order)
    {
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        
        //Checking MAC format
        if ( !preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $mac) )
        {   
            $result = ( array("message" => "wrong MAC format") );
            return $result;
        }
        
        //GETTING WLC Clients
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Apsummary')
            ->field('date_of_record')->range(new \DateTime('today'),new \DateTime('tomorrow'))
            ->field('ap_macradio')->equals($mac)
            ->sort("date_of_record",$order)
            ->getQuery()
            ->execute();

        $apsummaries = array();
        foreach($documents as $aps){
            array_push($apsummaries,$aps);
        }
        return $apsummaries;
        
    }
    
    
    /**
     * Information fo AP for specific date and wlc
     * @Get("/ap/record/{wlcip}/{recordid}/{order}")
     * @ApiDoc(
     *  resource=true,
     *  description="Information fo AP for specific date and wlc",
     *   requirements={
     *      {"name"="wlcip", "dataType"="string", "requirement"="true", "description"="IP of WLC"},
     *      {"name"="recordid", "dataType"="string", "requirement"="true", "description"="Id of Date of record"},
     *      {"name"="order", "dataType"="string", "requirement"="true", "description"="Order of results"},
     *  }
     * )
     */
    public function getApRecordAction($wlcip,$recordid,$order)
    {
        if (!filter_var($wlcip, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }
        if ( $order != "DESC" && $order != "ASC" ) {
            $result = ( array("message" => "wrong ORDER format (DESC or ASC)") );
            return $result;
        }
        
        $wlcclients = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Wlcclients')
        ->findOneById($recordid);
        
        $logger = $this->get('logger');
        $logger->info($wlcclients->getDateOfRecord()->format('Y-m-d H:i:s'));
        $logger->info($wlcclients->getDateOfRecord()->getTimestamp());
        
        //$datetime = new \DateTime($daterecord,$UTC);
        
        //GETTING WLC Clients
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Apsummary')
            ->field('date_of_record')->equals($wlcclients->getDateOfRecord())
            ->field('wlc_ip')->equals($wlcip)
            ->sort("ap_clientscount",$order)
            ->getQuery()
            ->execute();

        $apsummaries = array();
        foreach($documents as $aps){
            array_push($apsummaries,$aps);
        }
        return $apsummaries;
    }
    
    /**
     * Information fo AP with Client IP. Only the record of the last AP with relation with client Ip
     * @Get("/ap/record/by/client/ip/{clientip}")
     * @ApiDoc(
     *  resource=true,
     *  description="Information of AP on the last record with client IP address",
     *   requirements={
     *      {"name"="clientip", "dataType"="string", "requirement"="true", "description"="IP of Client"}
     *  }
     * )
     */
    public function getApWithLastIpClientAction($clientip)
    {
        if (!filter_var($clientip, FILTER_VALIDATE_IP))
        {
            $result = ( array("message" => "wrong IP format") );
            return $result;
        }
                
        $clientrecord = $this->get('doctrine_mongodb')
            ->getRepository('SmartwifiBundle:Clients')
            ->findOneBy(
            array('client_ip' => $clientip),array('date_of_record' => 'ASC'),1
        );
        
        
        //GETTING WLC Clients
        $documents = $this->get('doctrine_mongodb')
            ->getManager()
            ->createQueryBuilder('SmartwifiBundle:Clients')
            ->field('client_ip')->equals($clientip)
            ->limit(1)
            ->sort("date_of_record",'DESC')
            ->getQuery()
            ->execute();

        $client = array();
        foreach($documents as $cli){
            array_push($client,$cli);
        }
        return $client;
        
    }
    
    
}
            
            
            
            
            
            

