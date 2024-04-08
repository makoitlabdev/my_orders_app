<?php

namespace App\Command;

use App\Entity\Order;
// use App\Entity\DelayedOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ProcessDelayedOrders',
    description: 'Add a short description for your command',
)]
class ProcessDelayedOrdersCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:process-delayed-orders')->setDescription('Process delayed orders');
    }
} 
