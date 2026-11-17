<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use ZipArchive;

#[Signature('deploy:package-hostinger
    {--output= : Where to write the zip (defaults to branham-blog-hostinger.zip in the project root)}
    {--skip-build : Use the existing public/build instead of running npm run build}')]
#[Description('Build a zip for Hostinger shared hosting: the app in branham-blog/ and the web root in public_html/')]
class PackageForHostinger extends Command
{
    /**
     * Folder the app is unpacked into, beside public_html. public/index.php looks for this name.
     */
    public const APP_FOLDER = 'branham-blog';

    public const WEB_FOLDER = 'public_html';

    /**
     * Project files and folders that are only needed for development.
     *
     * @var list<string>
     */
    private const DEV_ONLY = [
        '.ai', '.claude', '.github', '.mcp.json', '.editorconfig', '.gitattributes', '.gitignore', '.npmrc',
        'AGENTS.md', 'CLAUDE.md', 'boost.json', 'blog-website-requirements.md', 'phpunit.xml',
        'package.json', 'package-lock.json', 'vite.config.js', 'tests', 'resources/css', 'resources/js',
        'public/hot', 'public/storage', 'public/fonts-manifest.dev.json',
    ];

    public function handle(): int
    {
        $output = $this->option('output') ?: base_path(self::APP_FOLDER.'-hostinger.zip');
        $staging = sys_get_temp_dir().DIRECTORY_SEPARATOR.'branham-deploy-'.bin2hex(random_bytes(4));

        try {
            if (! $this->option('skip-build')) {
                $this->components->task('Building front-end assets', fn () => $this->runShell('npm run build', base_path()));
            }

            if (! is_file(public_path('build/manifest.json'))) {
                throw new RuntimeException('public/build/manifest.json is missing. Run npm run build first.');
            }

            $this->components->task('Copying project files', function () use ($staging): void {
                foreach ($this->deployableFiles() as $file) {
                    File::ensureDirectoryExists(dirname("{$staging}/{$file}"));
                    File::copy(base_path($file), "{$staging}/{$file}");
                }

                File::copyDirectory(public_path('build'), "{$staging}/public/build");
            });

            $this->components->task('Installing production dependencies (no dev packages)', fn () => $this->runShell(
                'composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress',
                $staging,
            ));

            $this->components->task('Writing the zip', fn () => $this->zip($staging, $output));
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            File::deleteDirectory($staging);
        }

        $this->components->info('Package ready: '.$output);
        $this->line('  Extract it in <comment>domains/branhamsamwel.com/</comment> on Hostinger, then follow "Deploying to Hostinger" in README.md.');

        return self::SUCCESS;
    }

    /**
     * Files to ship, relative to the project root: everything git tracks or would track (so .env, vendor,
     * node_modules, uploads, logs and caches are left out), minus development-only files.
     *
     * @return list<string>
     */
    public function deployableFiles(): array
    {
        $result = Process::path(base_path())->run('git ls-files --cached --others --exclude-standard');

        if ($result->failed()) {
            throw new RuntimeException('git is needed to list the project files: '.trim($result->errorOutput()));
        }

        $files = array_filter(
            preg_split('/\R/', trim($result->output())) ?: [],
            fn (string $file): bool => $file !== '' && is_file(base_path($file)) && ! $this->isDevOnly($file),
        );

        sort($files);

        return array_values($files);
    }

    private function isDevOnly(string $file): bool
    {
        foreach (self::DEV_ONLY as $excluded) {
            if ($file === $excluded || str_starts_with($file, $excluded.'/')) {
                return true;
            }
        }

        return str_starts_with($file, 'database/') && str_ends_with($file, '.sqlite');
    }

    private function runShell(string $command, string $path): void
    {
        $result = Process::path($path)->timeout(900)->run($command);

        if ($result->failed()) {
            throw new RuntimeException("\"{$command}\" failed:\n".trim($result->errorOutput() ?: $result->output()));
        }
    }

    /**
     * Zip the staged app into APP_FOLDER/ and its public/ folder into WEB_FOLDER/.
     */
    private function zip(string $staging, string $output): void
    {
        File::delete($output);

        $zip = new ZipArchive;

        if ($zip->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Could not create {$output}.");
        }

        foreach (File::allFiles($staging, true) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());

            $target = str_starts_with($relative, 'public/')
                ? self::WEB_FOLDER.'/'.substr($relative, strlen('public/'))
                : self::APP_FOLDER.'/'.$relative;

            $zip->addFile($file->getPathname(), $target);
        }

        if (! $zip->close()) {
            throw new RuntimeException("Could not write {$output}.");
        }
    }
}
