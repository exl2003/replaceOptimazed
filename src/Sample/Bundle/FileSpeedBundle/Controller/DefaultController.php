<?php

namespace Sample\Bundle\FileSpeedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/file-speed", name="file_speed_index")
     */
    public function indexAction()
    {
        return $this->render('SampleBundleFileSpeedBundle:Default:index.html.twig');
    }
    
    
    /**
     * @Route("/file-speed-download", name="file_speed_download")
     */
    public function downloadAction()
    {
        //var_dump(realpath(__DIR__ . "/../Static/1m.sample")); exit;        
        $filename = realpath(__DIR__ . "/../Static/1m.sample");
        // Generate response
        $response = new Response();

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/octect-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(file_get_contents($filename));
        return $response;
    }
    
}
