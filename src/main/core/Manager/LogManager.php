<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LogManager
{
    const CSV_LOG_BATCH = 1000;

    /** @var ObjectManager */
    private $om;

    /** @var LogRepository */
    private $logRepository;

    /** @var FinderProvider */
    private $finder;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ClaroUtilities */
    private $ut;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * LogManager constructor.
     */
    public function __construct(
        ObjectManager $objectManager,
        FinderProvider $finder,
        TranslatorInterface $translator,
        ClaroUtilities $ut,
        EventDispatcherInterface $dispatcher
    ) {
        $this->translator = $translator;
        $this->om = $objectManager;
        $this->finder = $finder;
        $this->ut = $ut;
        $this->dispatcher = $dispatcher;

        $this->logRepository = $objectManager->getRepository(Log::class);
    }

    /**
     * Get log by id.
     *
     * @param $id
     *
     * @return object|null
     */
    public function getLog($id)
    {
        return $this->logRepository->findOneBy(['id' => $id]);
    }

    public function getData(array $finderParams = [])
    {
        // get filters
        $filters = FinderProvider::parseQueryParams($finderParams)['allFilters'];
        $unique = isset($filters['unique']) ? filter_var($filters['unique'], FILTER_VALIDATE_BOOLEAN) : false;

        return $this->logRepository->fetchChartData($filters, $unique);
    }

    /**
     * Get chart data given a list/array of filters.
     *
     * @param array $finderParams filters for query
     *
     * @return array formatted data to use with chart functions
     */
    public function getChartData(array $finderParams = [])
    {
        // get filters
        $filters = FinderProvider::parseQueryParams($finderParams)['allFilters'];
        $unique = isset($filters['unique']) ? filter_var($filters['unique'], FILTER_VALIDATE_BOOLEAN) : false;
        $data = $this->logRepository->fetchChartData($filters, $unique);
        $minDate = isset($filters['dateLog']) ? $filters['dateLog'] : null;
        if (is_string($minDate)) {
            $minDate = new \DateTime($minDate);
        }
        $maxDate = isset($filters['dateTo']) ? $filters['dateTo'] : null;
        if (is_string($maxDate)) {
            $maxDate = new \DateTime($maxDate);
        }

        return $this->formatDataForChart($data, $minDate, $maxDate);
    }

    /**
     * Given a query params, it exports all logs to a CSV file.
     *
     * @param $query
     *
     * @return bool|resource
     */
    public function exportLogsToCsv($query, $fileName = null)
    {
        // Initialize variables
        $query['limit'] = self::CSV_LOG_BATCH;
        $query['page'] = 0;
        $count = 0;
        $total = 0;

        // Prepare CSV file
        $handle = fopen($fileName ?? 'php://output', 'w+');
        fputcsv($handle, [
            $this->translator->trans('date', [], 'platform'),
            $this->translator->trans('action', [], 'platform'),
            $this->translator->trans('user', [], 'platform'),
            $this->translator->trans('description', [], 'platform'),
        ], ';', '"');

        // Get batched logs
        while (0 === $count || $count < $total) {
            $logs = $this->finder->searchEntities(Log::class, $query);
            $total = $logs['totalResults'];
            $count += self::CSV_LOG_BATCH;
            ++$query['page'];

            /** @var Log $log */
            foreach ($logs['data'] as $log) {
                // TODO : merge with serializer
                // Get log description (depending on log sentence rendering)
                $eventName = 'create_log_list_item_'.$log->getAction();
                if (!$this->dispatcher->hasListeners($eventName)) {
                    $eventName = 'create_log_list_item';
                }

                /** @var LogCreateDelegateViewEvent $event */
                $event = $this->dispatcher->dispatch(new LogCreateDelegateViewEvent($log), $eventName);
                $description = trim(preg_replace('/\s\s+/', ' ', $event->getResponseContent()));

                fputcsv($handle, [
                    DateNormalizer::normalize($log->getDateLog()),
                    $this->translator->trans('log_'.$log->getAction().'_shortname', [], 'log'),
                    $log->getDoer() ? $log->getDoer()->getUsername() : '',
                    $this->ut->html2Csv($description, true),
                ], ';', '"');
            }

            $this->om->clear(Log::class);
        }

        fclose($handle);

        return $handle;
    }

    /**
     * Returns users' actions with their corresponding chart data.
     *
     * @return array
     */
    public function getUserActionsList(array $finderParams = [])
    {
        $queryParams = FinderProvider::parseQueryParams($finderParams);
        $page = $queryParams['page'];
        $limit = $queryParams['limit'];
        $allFilters = $queryParams['allFilters'];
        $filters = $queryParams['filters'];
        $sortBy = $queryParams['sortBy'];
        $minDate = isset($filters['dateLog']) ? (new \DateTime($filters['dateLog']))->setTime(0, 0, 0) : null;
        $maxDate = isset($filters['dateTo']) ? (new \DateTime($filters['dateTo']))->setTime(0, 0, 0) : null;

        $totalUsers = intval($this->logRepository->fetchUserActionsList($allFilters, true));
        $userList = $this->logRepository->fetchUserActionsList($allFilters, false, $page, $limit, $sortBy);

        $userData = [];
        foreach ($userList as $userAction) {
            $id = $userAction['doerId'];
            $firstName = $userAction['doerFirstName'];
            $lastName = $userAction['doerLastName'];
            $picture = $userAction['doerPicture'];
            $date = $userAction['date'];
            $total = $userAction['total'];
            if (!isset($userData['u'.$id])) {
                $userData['u'.$id] = [
                    'id' => $id,
                    'doer' => [
                        'id' => $id,
                        'name' => $lastName.' '.$firstName,
                        'picture' => $picture,
                    ],
                    'chartData' => [],
                    'actions' => 0,
                ];
            }
            $userData['u'.$id]['chartData'][] = ['date' => $date, 'total' => floatval($total)];
            $userData['u'.$id]['actions'] += floatval($total);
            $minDate = null === $minDate || $minDate > $date ? clone $date : $minDate;
            $maxDate = null === $maxDate || $maxDate < $date ? clone $date : $maxDate;
        }

        $data = [];
        foreach ($userData as $line) {
            $line['chartData'] = $this->formatDataForChart($line['chartData'], clone $minDate, clone $maxDate);
            $data[] = $line;
        }

        if (!empty($sortBy)) {
            usort($data, function ($o1, $o2) use ($sortBy) {
                $cmp = 0;
                switch ($sortBy['property']) {
                    case 'doer.name':
                        $cmp = strcmp($o1['doer']['name'], $o2['doer']['name']);
                        break;
                    case 'actions':
                        $cmp = $o1['actions'] - $o2['actions'];
                        break;
                }

                return $sortBy['direction'] * $cmp;
            });
        }

        return FinderProvider::formatPaginatedData($data, $totalUsers, $page, $limit, $filters, $sortBy);
    }

    /**
     * Exports users' actions for a given query.
     *
     * @return bool|resource
     */
    public function exportUserActionToCsv(array $finderParams = [])
    {
        // Initialize variables
        $queryParams = FinderProvider::parseQueryParams($finderParams);
        $allFilters = $queryParams['allFilters'];
        $sortBy = $queryParams['sortBy'];
        $limit = self::CSV_LOG_BATCH;
        $page = 0;
        $count = 0;
        $total = intval($this->logRepository->fetchUserActionsList($allFilters, true));

        // Prepare CSV file
        $handle = fopen('php://output', 'w+');
        fputcsv($handle, [
            $this->translator->trans('user', [], 'platform'),
            $this->translator->trans('actions', [], 'platform'),
        ], ';', '"');

        // Get batched logs
        while (0 === $count || $count < $total) {
            $logs = $logs = $this->logRepository->fetchUsersByActionsList($allFilters, false, $page, $limit, $sortBy);
            $count += self::CSV_LOG_BATCH;
            ++$page;

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['doerLastName'].' '.$log['doerFirstName'],
                    $log['actions'],
                ], ';', '"');
            }
        }

        fclose($handle);

        return $handle;
    }

    /**
     * Formats raw data to the appropriate charts format.
     *
     * @return array
     */
    private function formatDataForChart(array $data, \DateTime $minDate = null, \DateTime $maxDate = null)
    {
        $prevDate = $minDate;
        $chartData = [];
        $idx = 0;
        foreach ($data as $value) {
            // Fill in with zeros from previous date till this date
            while (null !== $prevDate && $prevDate < $value['date']) {
                $chartData["c${idx}"] = ['xData' => $prevDate->format('Y-m-d\TH:i:s'), 'yData' => 0];
                $prevDate->add(new \DateInterval('P1D'));
                ++$idx;
            }
            $chartData["c${idx}"] = ['xData' => $value['date']->format('Y-m-d\TH:i:s'), 'yData' => floatval($value['total'])];
            $prevDate = $value['date']->add(new \DateInterval('P1D'));
            ++$idx;
        }
        // Fill in with zeros till maxDate
        while (null !== $prevDate && null !== $maxDate && $maxDate >= $prevDate) {
            $chartData["c${idx}"] = ['xData' => $prevDate->format('Y-m-d\TH:i:s'), 'yData' => 0];
            $prevDate->add(new \DateInterval('P1D'));
            ++$idx;
        }

        return $chartData;
    }

    public function getDetails(Log $log)
    {
        $details = $log->getDetails();
        $receiverUser = isset($details['receiverUser']) ? $details['receiverUser']['firstName'].' '.$details['receiverUser']['lastName'] : null;
        $receiverGroup = isset($details['receiverGroup']) ? $details['receiverGroup']['name'] : null;
        $role = isset($details['role']) ? $details['role']['name'] : null;
        $workspace = isset($details['workspace']) ? $details['workspace']['name'] : null;
        $resource = $log->getResourceNode() ? $details['resource']['path'] : null;

        return $this->translator->trans(
            'log_'.$log->getAction().'_sentence',
            [
                '%resource%' => $resource,
                '%receiver_user%' => $receiverUser,
                '%receiver_group%' => $receiverGroup,
                '%role%' => $role,
                '%workspace%' => $workspace,
                '%tool%' => $this->translator->trans($log->getToolName(), [], 'tool'),
            ],
            'log'
        );
    }
}
