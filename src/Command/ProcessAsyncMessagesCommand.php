<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

/**
 * Commande optimisée pour traiter les messages async sur hébergement partagé.
 *
 * Contrairement au worker permanent (systemd), cette commande:
 * - Se termine automatiquement après N messages ou X minutes
 * - Limite la mémoire pour éviter crash sur serveur partagé
 * - Peut être appelée par cron job ou probabilistiquement
 *
 * Usage:
 * 1. Cron (toutes les 5 min): php bin/console app:process-async-messages
 * 2. Probabiliste: Déclencher sur 10% des requêtes web
 * 3. Manual: php bin/console app:process-async-messages --limit=100
 */
#[AsCommand(
    name: 'app:process-async-messages',
    description: 'Process async messages (optimized for shared hosting)'
)]
class ProcessAsyncMessagesCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Maximum number of messages to process',
                50
            )
            ->addOption(
                'time-limit',
                't',
                InputOption::VALUE_OPTIONAL,
                'Maximum execution time in seconds',
                240  // 4 minutes (safe pour cron 5 min)
            )
            ->addOption(
                'memory-limit',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Maximum memory usage (ex: 128M)',
                '128M'
            )
            ->addOption(
                'transport',
                null,
                InputOption::VALUE_OPTIONAL,
                'Transport name to consume',
                'async'
            )
            ->setHelp(<<<'HELP'
Cette commande traite les messages asynchrones de manière contrôlée
pour les environnements avec ressources limitées (hébergement partagé).

Exemples:
  # Traiter 50 messages max, 4 min max, 128MB max (défaut)
  php bin/console app:process-async-messages
  
  # Traiter plus de messages avec plus de temps
  php bin/console app:process-async-messages --limit=100 --time-limit=600
  
  # Cron job (toutes les 5 minutes)
  */5 * * * * cd /home/user/public_html && php bin/console app:process-async-messages >> /dev/null 2>&1

Recommandations hébergement partagé:
- limit: 20-50 messages (évite timeout)
- time-limit: 240s (4 min pour cron 5 min)
- memory-limit: 128M (selon limites hébergeur)
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $limit = (int) $input->getOption('limit');
        $timeLimit = (int) $input->getOption('time-limit');
        $memoryLimit = $input->getOption('memory-limit');
        $transport = $input->getOption('transport');

        // Vérifier si un autre worker tourne déjà (éviter conflits)
        if ($this->isWorkerRunning()) {
            $io->note('Another worker is already running. Skipping...');
            return Command::SUCCESS;
        }

        $io->title('Processing Async Messages');
        $io->info(sprintf(
            'Configuration: max %d messages, %ds timeout, %s memory',
            $limit,
            $timeLimit,
            $memoryLimit
        ));

        // Créer le process messenger:consume avec limites
        $consolePath = $this->projectDir . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console';

        $command = [
            PHP_BINARY,
            $consolePath,
            'messenger:consume',
            $transport,
            '--limit=' . $limit,
            '--time-limit=' . $timeLimit,
            '--memory-limit=' . $memoryLimit,
            '--no-interaction',
        ];

        // Ajouter verbosity selon output
        if ($output->isVerbose()) {
            $command[] = '-v';
        } elseif ($output->isVeryVerbose()) {
            $command[] = '-vv';
        } elseif ($output->isDebug()) {
            $command[] = '-vvv';
        } else {
            $command[] = '--quiet';
        }

        $process = new Process(
            $command,
            getcwd(),
            null,
            null,
            $timeLimit + 30  // Timeout avec marge
        );

        $startTime = microtime(true);
        $io->text('Starting worker...');

        try {
            $process->run(function ($type, $buffer) use ($output) {
                // Transférer output du worker vers console
                if (Process::ERR === $type) {
                    $output->write('<error>' . $buffer . '</error>');
                } else {
                    $output->write($buffer);
                }
            });

            $duration = round(microtime(true) - $startTime, 2);

            if ($process->isSuccessful()) {
                $io->success(sprintf(
                    'Worker completed successfully in %ss',
                    $duration
                ));
                return Command::SUCCESS;
            } else {
                $io->error(sprintf(
                    'Worker failed with exit code %d after %ss',
                    $process->getExitCode(),
                    $duration
                ));
                $io->text('Error output: ' . $process->getErrorOutput());
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $io->error('Exception during message processing: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Vérifie si un worker messenger:consume tourne déjà.
     * Évite les conflits sur messages (double traitement).
     */
    private function isWorkerRunning(): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Sur Windows, vérifier avec tasklist
            $process = Process::fromShellCommandline('tasklist | findstr "php.exe"');
            $process->run();
            $output = $process->getOutput();

            // Compter les process PHP (plus de 2 = worker tourne probablement)
            return substr_count($output, 'php.exe') > 2;
        }

        // Sur Linux/Unix, vérifier avec ps
        $process = Process::fromShellCommandline('ps aux | grep "[m]essenger:consume" | wc -l');
        $process->run();

        return (int) trim($process->getOutput()) > 0;
    }
}
