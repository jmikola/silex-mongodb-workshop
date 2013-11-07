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

        $updated = $inserted = 0;

        foreach ($venues as $venue) {
            // Foursquare venue IDs are actually ObjectIds
            $venue = array_merge(array('_id' => new MongoId($venue['id'])), $venue);
            unset($venue['id']);

            if (isset($venue['location']['lng'], $venue['location']['lat'])) {
                $point = new Point(array($venue['location']['lng'], $venue['location']['lat']));
                $venue['loc'] = $point->jsonSerialize();
            }

            $gle = $c->save($venue);

            if ($gle['updatedExisting']) {
                $updated++;
            } else {
                $inserted++;
            }
        }

        $output->writeln(sprintf('Inserted %d new venues', $inserted));
        $output->writeln(sprintf('Updated %d existing venues', $updated));
    })
;

return $console;
