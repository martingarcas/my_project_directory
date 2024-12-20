<?php

namespace App\Scheduler;

use App\Message\CheckActiveQuestionsMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('CheckActiveQuestionsScheduleTask')] //php bin/console messenger:consume scheduler_CheckActiveQuestionsScheduleTask --verbose
final class CheckActiveQuestionsSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                RecurringMessage::every('10 seconds', new CheckActiveQuestionsMessage()),
                //RecurringMessage::every('1 hour', new CheckActiveQuestionsMessage()),
            )
            ->stateful($this->cache)
        ;
    }
}
