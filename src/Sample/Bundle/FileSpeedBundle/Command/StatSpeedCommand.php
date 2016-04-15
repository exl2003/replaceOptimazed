<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sample\Bundle\FileSpeedBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

class StatSpeedCommand extends ContainerAwareCommand
{
    private $servers = array(
      'work01.z',  
      'work02.z',          
      'work03.z',          
    );
    
    protected function configure()
    {
        $this
            ->setName('stat:speed')
            ->setDescription('Stat speed')
            ->addArgument(
                'conf',
                InputArgument::OPTIONAL,
                'What config do you prefer?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $myHost = null;
        $yaml = new Parser();
        //dump($yaml); exit;        
        $path = $this->getContainer()->getParameter('kernel.root_dir') . '/config/server.yml';
        //dump( $this->getContainer()->getParameter('kernel.root_dir') . '/config/' ); exit;

        
        try {
            $values = $yaml->parse(file_get_contents( $path ));      
            //dump($values); exit;
            if ( isset($values['server']) && isset($values['server']['name']) ){
                //var_dump($values['server']['name']); exit;
                $myHost = $values['server']['name'];
            }
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }        
        
        /*
        $name = $input->getArgument('conf');
        if ($name) {
            $text = 'Hello '.$name;
        } else {
            $text = 'Hello';
        }

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }
        
        $output->writeln($text);
         * 
         */
        foreach ($this->servers as $server) {
            if ( $server != $myHost ){
                //echo "$server\n";
                $url = "http://{$server}/app_dev.php/file-speed-download";
                $this->getUrl($url);
            }
            
        }
        
    }
    
    private function getUrl($url )
    {

            // Initialize cURL with given url
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, 'zSpeedTest 1.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); //param
            set_time_limit(65);//param

            $execute = curl_exec($ch);
            $info = curl_getinfo($ch);

            // Time spent downloading, I think
            $time = $info['total_time'] 
                  - $info['namelookup_time'] 
                  - $info['connect_time'] 
                  - $info['pretransfer_time'] 
                  - $info['starttransfer_time'] 
                  - $info['redirect_time'];


            // Echo friendly messages
            //header('Content-Type: text/plain');
            printf("Downloaded %d bytes in %0.4f seconds.\n", $info['size_download'], $time);
            printf("Which is %0.4f mbps\n", $info['size_download'] * 8 / $time / 1024 / 1024);
            printf("CURL said %0.4f mbps\n", $info['speed_download'] * 8 / 1024 / 1024);

            echo "\n\ncurl_getinfo() said:\n", str_repeat('-', 31 + strlen($url)), "\n";
            foreach ($info as $label => $value)
            {
                //if ('certinfo' != $label ){
                    printf("%-30s %s\n", $label, empty($value) ? '' : $value );
                /*} else  {
                    var_dump( $label, $value ); exit;
                }*/
            }
        
    }
    
}
