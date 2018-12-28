<?php

namespace ProjectGhost;

use Illuminate\Console\Command;

class ProjectGhostCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:ghost {mode} {options?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Change Monitoring';

    protected $dbFile = '/storage/.projectGhost';

    protected $directorySeperator = '/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
        @mode = init
        @mode = scan [zip|log]

         */

        if ($this->argument('mode') == 'init') {

            $this->initCommand();

        } elseif ($this->argument('mode') == 'scan') {

            $this->scanCommand();

        } else {

            $this->printMessages(
                [
                    "php artisan project:ghost init \t 'This command creates a digital signature from all the files in the project'",
                    "php artisan project:ghost scan \t 'The following command finds files that have been modified or created or deleted'",
                    "php artisan project:ghost scan zip \t 'scan for changes and make these changes in a zip file'",
                    "php artisan project:ghost scan log \t 'scan for changes and show you a summary of these changes'",
                    "php artisan project:ghost help \t 'see help'",
                ]
            );

        }
    }

    protected function initCommand()
    {

        $this->dbFile = base_path() . $this->dbFile;

        $path = realpath(base_path());

        $load_files = [];

        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (!is_file($name)) {
                continue;
            }

            $relativePath = str_replace([$path, './'], ['.', $path . DIRECTORY_SEPARATOR], $name);

            if (DIRECTORY_SEPARATOR != $this->directorySeperator) {
                $relativePath = str_replace(DIRECTORY_SEPARATOR, $this->directorySeperator, $relativePath);
            }
            // $relativePath = str_replace('./', $path . DIRECTORY_SEPARATOR, $relativePath);

            $sha = sha1_file($name);

            $load_files[$relativePath] = $sha;

        }

        file_put_contents($this->dbFile, json_encode($load_files));

        usleep(500);

        $this->printMessages(
            [
                'scan file(s) : ' . count($load_files),
                'ok ! you can work in your project and use ' . "\r\n" . "php artisan project:ghost scan zip",
            ]
        );

    }

    protected function scanCommand()
    {

        $this->dbFile = base_path() . $this->dbFile;

        if (!is_file($this->dbFile)) {

            file_put_contents($this->dbFile, '');

            $this->printMessages('please use : php artisan project:ghost init');

            return false;
        }

        $path = realpath(base_path());
        $new_files = [];
        $mod_files = [];
        $del_files = [];
        $found = [];

        $load_files = file_get_contents($this->dbFile);

        if (strlen($load_files) == 0) {
            $load_files = [];
        } else {
            $load_files = json_decode($load_files, true);
        }

        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {

            if (!is_file($name)) {
                continue;
            }

            $relativePath = str_replace([$path, './'], ['.', $path . DIRECTORY_SEPARATOR], $name);
            if (DIRECTORY_SEPARATOR != $this->directorySeperator) {
                $relativePath = str_replace(DIRECTORY_SEPARATOR, $this->directorySeperator, $relativePath);
            }
            // $relativePath = str_replace('./', $path . DIRECTORY_SEPARATOR, $relativePath);

            $found[] = $relativePath;
            $sha = sha1_file($name);

            if (!isset($load_files[$relativePath])) {
                $new_files[] = $relativePath;
            } elseif ($load_files[$relativePath] != $sha) {
                $mod_files[] = $relativePath;
            }

            $load_files[$relativePath] = $sha;

        }

        $del_files = array_diff(array_keys($load_files), $found);
        if (count($del_files)) {
            foreach ($del_files as $delPath) {
                unset($load_files[$delPath]);
            }
        }

        usleep(500);

        $this->printMessages(
            [
                'scan file(s) : ' . count($load_files),
                'new file(s) : ' . count($new_files),
                'modified file(s) : ' . count($mod_files),
                'deleted file(s) : ' . count($del_files),
            ]
        );

        if ($this->argument('options') != null) {
            if ($this->argument('options') == 'zip') {

                $this->zipFileGenerator(
                    $path,
                    base_path() . '/storage/projectGhost_' . @time() . '.zip',
                    array_merge($new_files, $mod_files)
                );

            } elseif ($this->argument('options') == 'log') {

                $this->printLogfiles($new_files, 'new files ');

                $this->printLogfiles($mod_files, 'modified files ');

                $this->printLogfiles($del_files, 'deleted files ');

            }
        }

        $this->printMessages(
            [
                'ok ! you can use : ',
                'php artisan project:ghost init',
            ]
        );
    }

    protected function zipFileGenerator($source, $destination, $files = [])
    {
        // $source = $path;
        // $destination = base_path().'/projectGhost_' . @time() . '.zip';

        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new \ZipArchive();
                if ($zip->open($destination, \ZIPARCHIVE::CREATE) === true) {
                    $source = realpath($source);
                    if (is_dir($source) === true) {
                        foreach ($files as $file) {

                            $file = realpath($file);

                            if (is_dir($file) === true) {

                                $zip->addEmptyDir(str_replace([$source . DIRECTORY_SEPARATOR], [''], $file . DIRECTORY_SEPARATOR));

                            } elseif (is_file($file) === true) {

                                $zip->addFromString(str_replace([$source . DIRECTORY_SEPARATOR], [''], $file), file_get_contents($file));
                            }
                        }
                    } elseif (is_file($source) === true) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                    }
                }

                $zip->close();

                echo $this->printMessages($destination . ' copy it !');
            }
        }
    }

    protected function printMessages($messages)
    {
        if (is_array($messages)) {
            echo "\r\n";
            foreach ($messages as $message) {
                echo $message . "\r\n\r\n";
            }
        } else {
            "\r\n" . $messages . "\r\n\r\n";
        }
    }

    protected function printLogfiles($files = [], $file_name)
    {
        if (count($files) > 0) {
            echo "\r\n" . $file_name . ' : ' . "\r\n";
            foreach ($files as $f) {
                echo $f . "\r\n";
            }
        } else {
            echo "\r\n" . $file_name . ' : ( 0 )' . "\r\n";
        }
    }

}
