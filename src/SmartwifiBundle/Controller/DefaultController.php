<?php

namespace SmartwifiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SmartwifiBundle\Document\wlc;
use SmartwifiBundle\Document\ap;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SmartwifiBundle:Default:index.html.twig', array('name' => $name));
    }
    public function setApAction()
    {
        $ap = new wlc();
        $ap->setWlcname("mmoscoso");        
        $ap->setWlcip("1.1.1.1"); 

        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($ap);
        $dm->flush();       

        return new Response('Created product id '.$ap->getId());
    }
    public function getWlcsAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:wlc')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
     $logger->info('--->'.$wlcs[0]->getId());
     $logger->info('--->'.$wlcs[0]->getWlcname());
     $logger->info('--->'.$wlcs[0]->getWlcip());
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }   
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
    public function getApAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:ap')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
    public function getApsummaryAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:apsummary')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
    public function getClientsAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:clients')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
    public function getClientsummaryAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:clientsummary')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
    public function getLogAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:log')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
    public function getRogueapAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:rogueap')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
    public function getSummaryAction()
    {
        $wlcs = $this->get('doctrine_mongodb')
        ->getRepository('SmartwifiBundle:summary')
        ->findAll();
     $logger = $this->get('logger');
     $logger->info('--->'.count($wlcs));
    if (!$wlcs) {
        throw $this->createNotFoundException('No product found for id ');
    }
     return $this->render('SmartwifiBundle:Default:index.html.twig', array('wlcs' => $wlcs));
    // do something, like pass the $product object into a template
    }
}

