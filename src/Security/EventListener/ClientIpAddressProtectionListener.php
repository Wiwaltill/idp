<?php

namespace App\Security\EventListener;

use App\Security\Badge\ClientIpAddressBadge;
use App\Security\NonAllowedClientIpAddressException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class ClientIpAddressProtectionListener implements EventSubscriberInterface {

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    public function checkPassport(CheckPassportEvent $event): void {
        $request = $this->requestStack->getMainRequest();
        $ip = $request->getClientIp();

        $passport = $event->getPassport();
        if(!$passport->hasBadge(ClientIpAddressBadge::class)) {
            return;
        }

        /** @var ClientIpAddressBadge $badge */
        $badge = $passport->getBadge(ClientIpAddressBadge::class);
        if($badge->isResolved()) {
            return;
        }

        if(count($badge->getValidIpAddresses()) === 0) {
            $badge->markResolved();
            return;
        }

        if(!in_array($request->getClientIp(), $badge->getValidIpAddresses())) {
            throw new NonAllowedClientIpAddressException();
        }

        $badge->markResolved();
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array {
        return [CheckPassportEvent::class => ['checkPassport', 512]];
    }
}