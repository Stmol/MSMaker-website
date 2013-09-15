<?php

use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$console = new Application('MSMaker.stmol.me', '1.0');
$console->getDefinition()->addOption(
    new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev')
);

$console
    ->register('deploy')
//    ->setDefinition(array(
//            // new InputOption('some-option', null, InputOption::VALUE_NONE, 'Some help'),
//        ))
    ->setDescription('Deploy project')
    ->setCode(
        function (InputInterface $input, OutputInterface $output) use ($app) {

            $schema = new Schema();

            $downloadLogTable = $schema->createTable($app['dbname']);

            $downloadLogTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
            $downloadLogTable->addColumn(
                'dl_time',
                'datetime',
                array('default' => '0000-00-00 00:00:00', 'notnull' => false)
            );
            $downloadLogTable->addColumn(
                'target',
                'string',
                array('length' => 64, 'default' => null, 'notnull' => false)
            );
            $downloadLogTable->addColumn(
                'version',
                'string',
                array('length' => 64, 'default' => null, 'notnull' => false)
            );
            $downloadLogTable->addColumn('ip', 'string', array('length' => 64, 'default' => null, 'notnull' => false));
            $downloadLogTable->addColumn('ua', 'text', array('default' => null, 'notnull' => false));
            $downloadLogTable->addColumn('ref', 'text', array('default' => null, 'notnull' => false));
            $downloadLogTable->setPrimaryKey(array("id"));

            $platform = $app['db']->getDatabasePlatform();
            $queries = $schema->toSql($platform);

            foreach ($queries as $query) {
                $statement = $app['db']->prepare($query);
                $statement->execute();
            }
        }
    );

return $console;