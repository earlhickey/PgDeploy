<?php

namespace PgDeploy\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;

class GithubController extends AbstractActionController
{
    protected $configService;

    /**
     * Deploy
     */
    public function indexAction()
    {
        $config = $this->getConfigService();

        $respons = array();

        $headers = getallheaders();

        if (!isset($headers['X-Hub-Signature'])) {
            $response['error'] = 'Bad X-Hub-Signature';
            return $this->render($response);
        }

        $hubSignature = $headers['X-Hub-Signature'];

        list($algo, $hash) = explode('=', $hubSignature, 2);

        $payload = file_get_contents('php://input');
        $data = Json::decode($payload);

        $secret = $config['secret'];

        $payloadHash = hash_hmac($algo, $payload, $secret);

        if ($hash !== $payloadHash) {
            $response['error'] = 'Bad secret';
            return $this->render($response);
        }

        // allowed repositories
        $repositories = $config['repositories'];

        // do we have a valid repro?
        if (!array_key_exists($data->repository->full_name, $repositories))
        {
            $response['error'] = 'No valid repository';
            return $this->render($response);
        }

        /**
         * the repro we're going to deal with
         * - url
         * - branch
         * - workingdir
         */
        // the repro, url and branch we're going to deal with
        $repository = $config['repositories'][$data->repository->full_name];
        $url = $repository['url'];
        $branch = str_replace('refs/heads/', '', $data->ref);
        $workingdir = $repository['workingdir'];

        // do we have a valid branch?
        if (!in_array($branch, $repository['branches']))
        {
            $response['error'] = 'No valid branch';
            return $this->render($response);
        }

        /**
         * we are all set!
         */

        // run deployment script
        $deploy = dirname(__FILE__) . '/../../../scripts/deploy.sh';
        exec("$deploy $url $branch $workingdir 2>&1", $deployOutput);

        // set response
        $response['repository'] = $url;
        $response['branch'] = $branch;
        $response['deploy'] = $deployOutput;

        return $this->render($response);

    }

    /**
     *
     * Render the content as json
     *
     * @param json $content
     * @return response
     *
     */
    private function render($content)
    {
        $json = Json::encode((object) $content);

        /*
         * Make the output human readable
         */
        $result = Json::prettyPrint($json, array('indent' => '  '));

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
            'Content-length' => strlen($result)
        ));
        $response->setContent($result);

        return $response;
    }

    /**
     *
     * Getters/Setter for DI
     *
     */
    private function getConfigService()
    {
        if (!$this->configService) {
            $this->configService = $this->getServiceLocator()->get('Config');
        }
        return $this->configService;
    }

}
