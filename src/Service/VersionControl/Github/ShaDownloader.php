<?php


namespace App\Service\VersionControl\Github;


use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ShaDownloader
{
    private $logger;

    /** error message for user */
    private $errorMessage;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public function downloadSha(string $repository, string $branch, ?string $login = null, ?string $password = null):?string
    {
        $httpClient = HttpClient::create();

        $auth = [];
        if(isset($login)){
            $auth = ['auth_basic' => [
                $login,
                $password
            ]];
        }

        try {
            $response = $httpClient->request('GET', 'https://api.github.com/repos/'.$repository.'/branches/'.$branch, $auth);
            switch($response->getStatusCode()){
                case 200:
                    $this->errorMessage = 'Success.';
                    break;
                case 401:
                    $this->errorMessage = 'Invalid authorization attempt.';
                    return null;
                    break;
                case 403:
                    $this->errorMessage = 'Authorization attempt limit exceeded. Try again';
                    return null;
                    break;
                case 404:
                    $this->errorMessage = 'The repository was not found or is private.';
                    return null;
                    break;
                default:
                    $this->errorMessage = 'Error occurred. Try Again.';
                    return null;
                    break;
            }

            $responseBody = json_decode($response->getContent());
            return $responseBody->commit->sha;

        } catch (TransportExceptionInterface $e) {
            $this->errorMessage = 'Error occurred. Try Again.';
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->errorMessage = 'Error occurred. Try Again.';
            $this->logger->error($e->getMessage());
        }
        return null;
    }



    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

}