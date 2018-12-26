<?php

namespace ProjectGhost;

use Illuminate\Console\Command;

class ProjectGhostCommand extends Command {

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

        $db_file = 'storage/.projectGhost';
        if ($this->argument('mode') == 'init') {

            $path = realpath(base_path());

            $load_files = [];

            $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($objects as $name => $object) {
                if (!is_file($name)) {
                    continue;
                }

                $ds = '/';
                $relativePath = str_replace($path, '.', $name);
                if (DIRECTORY_SEPARATOR != $ds) {
                    $relativePath = str_replace(DIRECTORY_SEPARATOR, $ds, $relativePath);
                }
                $relativePath = str_replace('./', $path . DIRECTORY_SEPARATOR, $relativePath);

                $sha = sha1_file($name);

                $load_files[$relativePath] = $sha;

            }

            file_put_contents($db_file, json_encode($load_files));
            usleep(500);

            echo 'scan file(s) : ' . count($load_files) . "\r\n\r\n";

            echo 'ok ! you can work in your project and use ' . "\r\n" . "php artisan project:ghost scan zip";

        } elseif ($this->argument('mode') == 'scan') {

            if (!is_file($db_file)) {
                file_put_contents($db_file, '');
                echo 'please use : php artisan project:ghost init' . "\r\n";
                return false;
            }

            $path = realpath(base_path());
            $new_files = [];
            $mod_files = [];
            $del_files = [];
            $found = [];

            $load_files = file_get_contents($db_file);

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

                // dd($name);
                $ds = '/';
                $relativePath = str_replace($path, '.', $name);
                if (DIRECTORY_SEPARATOR != $ds) {
                    $relativePath = str_replace(DIRECTORY_SEPARATOR, $ds, $relativePath);
                }
                $relativePath = str_replace('./', $path . DIRECTORY_SEPARATOR, $relativePath);
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

            // file_put_contents($db_file,json_encode($load_files));
            usleep(500);

            echo 'scan file(s) : ' . count($load_files) . "\r\n\r\n";
            echo 'new file(s) : ' . count($new_files) . "\r\n";
            echo 'modified file(s) : ' . count($mod_files) . "\r\n";
            echo 'deleted file(s) : ' . count($del_files) . "\r\n";

            if ($this->argument('options') != null) {
                if ($this->argument('options') == 'zip') {
                    $source = $path;
                    $destination = 'projectGhost_' . @time() . '.zip';

                    if (extension_loaded('zip') === true) {
                        if (file_exists($source) === true) {
                            $zip = new \ZipArchive();
                            if ($zip->open($destination, \ZIPARCHIVE::CREATE) === true) {
                                $source = realpath($source);
                                if (is_dir($source) === true) {
                                    // $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
                                    foreach (array_merge($new_files, $mod_files) as $file) {
                                        $file = realpath($file);
                                        // dd(str_replace([$source . DIRECTORY_SEPARATOR,$source],[''], $file));
                                        if (is_dir($file) === true) {
                                            // $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                                            $zip->addEmptyDir(str_replace([$source . DIRECTORY_SEPARATOR], [''], $file . DIRECTORY_SEPARATOR));
                                        } elseif (is_file($file) === true) {
                                            // $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                                            $zip->addFromString(str_replace([$source . DIRECTORY_SEPARATOR], [''], $file), file_get_contents($file));
                                        }
                                    }
                                } elseif (is_file($source) === true) {
                                    $zip->addFromString(basename($source), file_get_contents($source));
                                }
                            }
                            $zip->close();

                            echo $destination . ' copy it !' . "\r\n";
                        }
                    }
                } elseif ($this->argument('options') == 'log') {

                    if (count($new_files) > 0) {
                        echo "\r\n" . 'new files : ' . "\r\n";
                        foreach ($new_files as $f) {
                            echo $f . "\r\n";
                        }
                    }
                    if (count($mod_files) > 0) {
                        echo "\r\n" . 'modified files : ' . "\r\n";
                        foreach ($mod_files as $f) {
                            echo $f . "\r\n";
                        }
                    }
                    if (count($del_files) > 0) {
                        echo "\r\n" . 'deleted files : ' . "\r\n";
                        foreach ($del_files as $f) {
                            echo $f . "\r\n";
                        }
                    }
                }
            }

            echo "\r\n" . 'ok ! you can use : ' . "\r\n" . "\r\n" . "php artisan project:ghost init" . "\r\n";

        } else {
            echo "\r\n" . "php artisan project:ghost init \t 'This command creates a digital signature from all the files in the project'"
                . "\r\n"
                . "php artisan project:ghost scan \t 'The following command finds files that have been modified or created or deleted'"
                . "\r\n"
                . "php artisan project:ghost scan zip \t 'scan for changes and make these changes in a zip file'"
                . "\r\n"
                . "php artisan project:ghost scan log \t 'scan for changes and show you a summary of these changes'"
                . "\r\n"
                . "php artisan project:ghost help \t 'see help'";
        }
    }

}