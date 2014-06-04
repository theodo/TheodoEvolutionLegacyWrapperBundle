<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface;

/**
 * AssetsInstallCommand
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class AssetsInstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('legacy:assets:install')
            ->setDescription('Installs legacy web assets under a public web directory')
            ->addArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web')
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setHelp(<<<TEXT
The <info>%command.name%</info> command installs legacy assets into a given
directory (e.g. the web directory).

<info>php %command.full_name% web</info>

A "bundles" directory will be created inside the target directory, and the
listed directories from the configuration file will be copied into it.

To create a symlink to each bundle instead of copying its assets, use the
<info>--symlink</info> option:

<info>php %command.full_name% web --symlink</info>

To make symlink relative, add the <info>--relative</info> option:

<info>php %command.full_name% web --symlink --relative</info>
TEXT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($input->getArgument('target'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        $output->writeln(sprintf('Installing legacy assets as <comment>%s</comment>', $input->getOption('symlink') ? 'symlinks' : 'hard copies'));

        /** @var LegacyKernelInterface $kernel */
        $kernel = $this->getContainer()->get('theodo_evolution_legacy_wrapper.legacy_kernel');

        foreach ($this->getContainer()->getParameter('theodo_evolution_legacy_wrapper.assets') as $group) {
            $this->installGroup($group, $kernel, $targetArg, $input, $output);
        }
    }

    private function installGroup($group, $kernel, $target, InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->getContainer()->get('filesystem');

        $groupDir = $kernel->getRootDir().'/'.$group['base'];

        foreach ($group['directories'] as $directory) {
            if (is_dir($originDir = $groupDir.'/'.$directory)) {
                $targetDir  = $target.'/'.$directory;

                $output->writeln(sprintf('Installing assets for <comment>%s</comment> into <comment>%s</comment>', $originDir, $targetDir));

                $filesystem->remove($targetDir);

                if ($input->getOption('symlink')) {
                    if ($input->getOption('relative')) {
                        $relativeOriginDir = $filesystem->makePathRelative($originDir, realpath($bundlesDir));
                    } else {
                        $relativeOriginDir = $originDir;
                    }
                    $filesystem->symlink($relativeOriginDir, $targetDir);
                } else {
                    $filesystem->mkdir($targetDir, 0777);
                    // We use a custom iterator to ignore VCS files
                    $filesystem->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir));
                }
            }
        }
    }
}
 