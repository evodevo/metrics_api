<?php

declare(strict_types=1);

namespace MetricsAPI\Infrastructure\Console\Command;

use MetricsAPI\Application\ReportGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateReport
 * @package MetricsAPI\Infrastructure\Console\Command
 */
class GenerateReport extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'post:stats';

    /**
     * @var ReportGenerator
     */
    private $reportService;

    /**
     * GenerateReport constructor.
     * @param ReportGenerator $reportService
     */
    public function __construct(ReportGenerator $reportService)
    {
        $this->reportService = $reportService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate post stats report.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stats = $this->reportService->generate();

        $output->writeln(json_encode($stats, JSON_PRETTY_PRINT));
    }
}