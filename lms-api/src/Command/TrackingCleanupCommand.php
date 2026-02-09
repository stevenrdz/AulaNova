<?php

namespace App\Command;

use App\Entity\TimeTrackingDaily;
use App\Entity\TimeTrackingRouteDaily;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'tracking:cleanup',
    description: 'Elimina registros antiguos de tracking (diario y por ruta).'
)]
class TrackingCleanupCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%env(int:default:180:TRACKING_RETENTION_DAYS)%')] private readonly int $defaultDays = 180
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'days',
            null,
            InputOption::VALUE_OPTIONAL,
            'Dias de retencion. Se eliminan registros con day < hoy - dias.',
            $this->defaultDays
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = (int) $input->getOption('days');
        if ($days < 1) {
            $output->writeln('<error>El valor de days debe ser >= 1.</error>');
            return Command::INVALID;
        }

        $cutoff = (new \DateTimeImmutable('today'))->modify(sprintf('-%d days', $days));

        $deletedDaily = $this->entityManager->createQueryBuilder()
            ->delete(TimeTrackingDaily::class, 't')
            ->where('t.day < :cutoff')
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
            ->execute();

        $deletedRoute = $this->entityManager->createQueryBuilder()
            ->delete(TimeTrackingRouteDaily::class, 'tr')
            ->where('tr.day < :cutoff')
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
            ->execute();

        $output->writeln(sprintf(
            'Eliminados %d registros time_tracking_daily y %d registros time_tracking_route_daily anteriores a %s.',
            $deletedDaily,
            $deletedRoute,
            $cutoff->format('Y-m-d')
        ));

        return Command::SUCCESS;
    }
}
