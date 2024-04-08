<?php

namespace App\Task;

use App\Entity\Order;
use App\Entity\DelayedOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:schedule-task',
    description: 'Schedule a task',
    help: 'This command schedules a task'
)]
class Scheduler extends Command
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setName('app:schedule-task');
        $this->entityManager = $entityManager;
    }

    protected function configure():void
    {
        $this
            ->setDescription('Schedule a task')
            ->setHelp('This command schedules a task');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentTime = new \DateTime();

        // Retrieve delayed orders that are past their expected time of delivery
        $delayedOrders = $this->entityManager->getRepository(Order::class)
            ->createQueryBuilder('d')
            ->where('d.delivery_date < :currentTime')
            ->andWhere('d.status = :status')
            ->setParameter('currentTime', $currentTime)
            ->setParameter('status', Order::$status_processing)
            ->getQuery()
            ->getResult();

        foreach ($delayedOrders as $order) {
            $order->setStatus('delayed');
            $this->entityManager->persist($order);
        }

        $this->entityManager->flush();

        $output->writeln('Delayed orders processed successfully.');

        return Command::SUCCESS;
    }
}
