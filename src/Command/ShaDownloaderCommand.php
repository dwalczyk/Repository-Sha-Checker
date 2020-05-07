<?php


namespace App\Command;
use App\Service\VersionControl\Github\ShaDownloader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Command\Command;

class ShaDownloaderCommand extends Command
{
    protected static $defaultName = 'app:sha-downloader';

    protected $gitShaDownloader;


    public function __construct(ShaDownloader $gitShaDownloader)
    {
        $this->gitShaDownloader = $gitShaDownloader;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'arguments',
                InputArgument::IS_ARRAY,
            'Repository name and branch'
            )
            ->addOption(
            'service',
            null,
            InputOption::VALUE_OPTIONAL,
            'Service eg. github, bitbucket',
            'github'
            )
            ->addOption(
                'login',
                null,
                InputOption::VALUE_OPTIONAL,
                'Login for github account'
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_OPTIONAL,
                'Password for github account'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $arguments = $input->getArgument('arguments');
        if(!isset($arguments[0])){
            $output->writeln('<error>Repository name must be entered</error>');
            return 0;
        }else{
            $repository = $arguments[0];
        }

        if(!isset($arguments[1])){
            $branch = 'master';
        }else{
            $branch = $arguments[1];
        }

        switch($input->getOption('service')){

            case 'github':
                $sha = $this->gitShaDownloader->downloadSha($repository, $branch, $input->getOption('login'), $input->getOption('password'));
                if($sha == null){
                    $response = '<error>'.$this->gitShaDownloader->getErrorMessage().'</error>';
                }else{
                    $response = '<info>Sha: ' . $sha.'</info>';
                }
                break;

            default:
                $response = '<error>Unknown service "'.$repository.'"</error>';
        }

        $output->writeln($response);

        return 0;
    }
}