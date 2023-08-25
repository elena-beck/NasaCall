<?php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\VarDumper\VarDumper;

#[AsCommand(
    name: 'app:create-nasa',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:add-nasa']
)]
class CreateNasaCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $key = '?api_key=p960B4skMQHGdPnetw2KYFVzzoomz4GV5oZMZjUM';
        $urlInfo = 'https://api.nasa.gov/EPIC/api/natural/date/';
        $urlArchive = 'https://api.nasa.gov/EPIC/archive/natural/';

        $targetFolder = $input->getArgument('targetFolder');
        if (!isset($targetFolder) || empty($targetFolder)) {
            return Command::FAILURE;
        }

        $output_dir = dirname(__FILE__) . '/../'.$targetFolder;

        $offset = "-50 days";

        $date = $input->getArgument('date');
        if (!isset($date) || empty($date)) {
            $date = date("Y-m-d", strtotime($offset));
        }

        $query = $urlInfo . $date . $key;

        // call api first time and get an image name
        $response = file_get_contents($query);
        if (strlen($response) === 0)
            return Command::INVALID;

        // create output directory
        $output_dir .= '/' . $date;
        if (!is_dir($output_dir) || !file_exists($output_dir)) {
            mkdir($output_dir, 0777, true);
        }

        $output->writeln('Output directory ' . $output_dir);

        $responseData = json_decode($response, true);
        $date = date("Y/m/d", strtotime($offset));

        foreach ($responseData as $detail) {
            $imageName = $detail['image'];
            $imageName .= '.png';
            $queryArchive = $urlArchive . $date .'/png/'. $imageName .$key;

            // call api second time with an image name
            $responseImage = file_get_contents($queryArchive);
            // create a file
            $file_path = $output_dir . '/' . $imageName;
            file_put_contents($file_path, $responseImage);

        }
        return Command::SUCCESS;
    }

    // ...
    protected function configure(): void
    {
        $this
            // ...
            ->addArgument('targetFolder', InputArgument::REQUIRED, 'Where do you want to place NASA images?')
            ->addArgument('date', InputArgument::OPTIONAL, 'The date of a images?')
        ;
    }
}