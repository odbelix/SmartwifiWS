<?php

namespace SmartwifiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


use SmartwifiBundle\Document\Ap;
use SmartwifiBundle\Document\Apsummary;
use SmartwifiBundle\Document\Clients;
use SmartwifiBundle\Document\Wlc;
use SmartwifiBundle\Document\Wlcclients;
use SmartwifiBundle\Document\Log;
use SmartwifiBundle\Document\Rogueap;
use SmartwifiBundle\Document\Summary;

class SmartwifiRestController extends Controller
{
    
    public function getApAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Ap')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    }

    public function getApSAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Apsummary')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    }

    public function getWlcAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Wlc')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    }
    
    public function getWlcclientsAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Wlcclients')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    }

    public function getSummaryAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Summary')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    }

    public function getLogAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Log')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    }

    public function getClientsAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Clients')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    } 

    public function getRogueapAction(){
         $documents = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:Rogueap')
        ->findAll();
        
        if (!$documents) {
            throw $this->createNotFoundException('Unable to find document');
        }   
        return $documents;
    } 

    public function testAction()
    {
        $collection = $db->createCollection('SmartwifiBundle:Summary');
        $document = array( "summary_number" => 1000, "summary_start" => new \DateTime("now"), "summary_stop" => new \DateTime("now") );
        $collection->insert($document);

        
    }
}


