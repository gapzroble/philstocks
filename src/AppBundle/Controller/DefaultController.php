<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $conn = $this->get('doctrine')->getManager()->getConnection();
        $stmt = $conn->prepare('SELECT distinct date FROM quotes ORDER BY date desc LIMIT 3');
        $stmt->execute();
        $dates = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $content = '<pre>';
        foreach ($dates as $date) {
            $content .= $this->runFilter($date);
            $content .= '<br>';
        }

        return new Response($content);
    }

    private function runFilter($date)
    {
        $kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'quotes:filter',
            '--env' => $this->container->getParameter('kernel.environment'),
            '--top' => true,
            '--up' => true,
            '--date' => $date,
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output->fetch();
    }
}
