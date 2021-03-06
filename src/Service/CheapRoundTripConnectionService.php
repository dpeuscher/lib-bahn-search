<?php

namespace Dpeuscher\BahnSearch\Service;

use Dpeuscher\BahnSearch\Entity\RoundTrip;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 */
class CheapRoundTripConnectionService
{
    /**
     * @var CheapConnectionService
     */
    protected $cheapConnectionService;

    /**
     * @var \DateTime
     */
    protected $searchTime;

    /**
     * CheapRoundTripConnectionService constructor.
     *
     * @param CheapConnectionService $cheapConnectionService
     * @param \DateTime|null $searchTime
     */
    public function __construct(CheapConnectionService $cheapConnectionService, ?\DateTime $searchTime = null)
    {
        $this->cheapConnectionService = $cheapConnectionService;
        $this->searchTime = $searchTime ?? new \DateTime();
    }

    /**
     * @param array $firstLeg
     * @param array $lastLeg
     * @return RoundTrip
     * @throws \Doctrine\ORM\ORMException
     */
    public function getRoundTrip($firstLeg, $lastLeg): RoundTrip
    {
        [$conFirst, $conFirstClassFirst] = $this->cheapConnectionService->getCheapestConnections($firstLeg);
        [$conLast, $conFirstClassLast] = $this->cheapConnectionService->getCheapestConnections($lastLeg);

        $fullPriceFirstClass = null;
        $fullPriceCheapest = null;
        if ($conFirstClassFirst !== null && $conFirstClassLast !== null) {
            $fullPriceFirstClass = $conFirstClassFirst->getMinimumFare() + $conFirstClassLast->getMinimumFare();
            $fullPriceCheapest = $fullPriceFirstClass;
        }
        if ($conFirst !== null && $conLast !== null) {
            $fullPriceCheapest = $conFirst->getMinimumFare() + $conLast->getMinimumFare();
        }
        elseif ($conFirst !== null && $conFirstClassLast !== null) {
            $fullPriceCheapest = $conFirst->getMinimumFare() + $conFirstClassLast->getMinimumFare();
        }
        elseif ($conLast !== null && $conFirstClassFirst !== null) {
            $fullPriceCheapest = $conLast->getMinimumFare() + $conFirstClassFirst->getMinimumFare();
        }

        $roundTrip = new RoundTrip();
        $roundTrip->setCheapestFirstLeg($conFirst);
        $roundTrip->setCheapestFirstLegFirstClass($conFirstClassFirst);
        $roundTrip->setCheapestLastLeg($conLast);
        $roundTrip->setCheapestLastLegFirstClass($conFirstClassLast);
        $roundTrip->setFromLocation($firstLeg['from']);
        $roundTrip->setToLocation($firstLeg['to']);
        $roundTrip->setProgramId($firstLeg['programId']);
        $roundTrip->setFromDepDateTime($firstLeg['fromDateTime']);
        $roundTrip->setToDepDateTime($lastLeg['fromDateTime']);
        $roundTrip->setFullPrice($fullPriceCheapest);
        $roundTrip->setFullPriceFirstClass($fullPriceFirstClass);
        $roundTrip->setSearchTime(clone $this->searchTime);

        return $roundTrip;
    }
}
