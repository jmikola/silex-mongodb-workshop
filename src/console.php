<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use GeoJson\Geometry\Point;

$console = new Application('My Silex Application', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console
    ->register('import-venues')
    ->setDefinition(array(
        new InputArgument('file', InputArgument::REQUIRED, 'JSON file to import'),
    ))
    ->setDescription('Import a JSON response from the Foursquare venues API')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $m = new MongoClient();
        $c = $m->silex->venues;

        $data = json_decode(file_get_contents($input->getArgument('file')), true);
        $venues = $data['response']['venues'];

        foreach ($venues as $venue) {
            // Foursquare venue IDs are actually ObjectIds
            $venue['_id'] = new MongoId($venue['id']);
            unset($venue['id']);

            if (isset($venue['location']['lng'], $venue['location']['lat'])) {
                $point = new Point(array($venue['location']['lng'], $venue['location']['lat']));
                $venue['loc'] = $point->jsonSerialize();
            }

            $c->insert($venue);
        }

        $output->writeln(sprintf('Imported %d venues', count($venues)));
    })
;

return $console;
