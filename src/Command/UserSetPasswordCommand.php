<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:set-password',
    description: 'Set (hash) a user password by username.',
)]
final class UserSetPasswordCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username of the user to update.')
            ->addArgument('password', InputArgument::REQUIRED, 'New plain password (will be hashed).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = (string) $input->getArgument('username');
        $plainPassword = (string) $input->getArgument('password');

        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            $io->error(sprintf('User "%s" not found.', $username));
            return Command::FAILURE;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->em->flush();

        $io->success(sprintf('Password updated for user "%s".', $username));
        return Command::SUCCESS;
    }
}

