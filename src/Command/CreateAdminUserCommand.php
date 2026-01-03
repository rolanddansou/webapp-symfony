<?php

namespace App\Command;

use App\Entity\Access\AdminUser;
use App\Entity\Access\Identity;
use App\Entity\Access\UserCredentials;
use App\Entity\Access\UserRole;
use App\Feature\Access\Service\IdentityUser;
use App\Repository\Access\UserRepository;
use App\Repository\Access\UserRoleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Create an admin user for the dashboard',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserRoleRepository $roleRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Admin email address', 'admin@app.com')
            ->addArgument('password', InputArgument::OPTIONAL, 'Admin password', '123456')
            ->addArgument('fullName', InputArgument::OPTIONAL, 'Admin full name', 'Admin User')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'Admin role code', 'ROLE_ADMIN')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $fullName = $input->getArgument('fullName');
        $roleCode = $input->getOption('role');

        // Check if user already exists
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            $io->error(sprintf('User with email "%s" already exists!', $email));
            return Command::FAILURE;
        }

        // Find or create the ROLE_ADMIN role
        $adminRole = $this->roleRepository->findOneBy(['code' => $roleCode]);
        if (!$adminRole) {
            $io->note(sprintf('Role "%s" not found. Creating it...', $roleCode));
            $adminRole = new UserRole();
            $adminRole->setCode($roleCode);
            $adminRole->setName(ucwords(str_replace('_', ' ', strtolower($roleCode))));
            $this->entityManager->persist($adminRole);
        }

        // Create Identity
        $identity = new Identity();
        $identity->setEmail($email);
        $identity->setEmailVerified(true);
        $identity->setEmailVerifiedAt(new DateTimeImmutable());
        $identity->addRole($adminRole);

        // Create UserCredentials with hashed password
        $credentials = new UserCredentials();
        $credentials->setRelativeUser($identity);
        $credentials->setIsEnabled(true);

        // Hash password using Symfony's password hasher
        // We need to create a temporary JwtUser to hash the password
        $tempUser = new IdentityUser($identity);
        $hashedPassword = $this->passwordHasher->hashPassword($tempUser, $password);
        $credentials->setPasswordHash($hashedPassword);

        $identity->setCredentials($credentials);

        // Create AdminUser
        $adminUser = new AdminUser($identity, $fullName);
        $adminUser->setTitle('System Administrator');

        // Persist everything
        $this->entityManager->persist($adminRole);
        $this->entityManager->persist($identity);
        $this->entityManager->persist($credentials);
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $io->success('Admin user created successfully!');
        $io->table(
            ['Property', 'Value'],
            [
                ['Email', $email],
                ['Password', $password],
                ['Full Name', $fullName],
                ['Role', $roleCode],
                ['Title', 'System Administrator'],
            ]
        );

        $io->note('You can now login at /admin/login with these credentials');

        return Command::SUCCESS;
    }
}
