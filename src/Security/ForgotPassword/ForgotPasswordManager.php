<?php

namespace App\Security\ForgotPassword;

use App\Converter\UserStringConverter;
use App\Entity\ActiveDirectoryUser;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForgotPasswordManager {
    private PasswordManager $passwordManager;
    private MailerInterface $mailer;
    private TranslatorInterface $translator;
    private UserStringConverter $userConverter;
    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(PasswordManager $passwordManager, MailerInterface $mailer, TranslatorInterface $translator,
                                UserStringConverter $userConverter, UrlGeneratorInterface $urlGenerator, LoggerInterface $logger = null) {
        $this->passwordManager = $passwordManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->userConverter = $userConverter;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger ?? new NullLogger();
    }

    public function canResetPassword(User $user, ?string $email): bool {
        return $user instanceof User && !$user instanceof ActiveDirectoryUser && $email !== null;
    }

    public function resetPassword(?User $user, ?string $email): void {
        if($user === null) {
            return;
        }

        if($this->canResetPassword($user, $email) !== true) {
            return;
        }

        $token = $this->passwordManager->createPasswordToken($user);

        $email = (new TemplatedEmail())
            ->to(new Address($email, $this->userConverter->convert($user)))
            ->subject($this->translator->trans('reset_password.title', [], 'mail'))
            ->textTemplate('mail/reset_password.txt.twig')
            ->htmlTemplate('mail/reset_password.html.twig')
            ->context([
                'token' => $token->getToken(),
                'link' => $this->urlGenerator->generate(
                    'change_password', [
                        'token' => $token->getToken()
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'expiry_date' => $token->getExpiresAt(),
                'username' => $user->getUsername()
            ]);

        $this->mailer->send($email);
    }

    public function updatePassword(PasswordResetToken $token, string $password): void {
        $this->passwordManager->setPassword($token, $password);

        $this->logger
            ->info(sprintf('User "%s" successfully updated his/her password.', $token->getUser()->getUsername()));
    }
}