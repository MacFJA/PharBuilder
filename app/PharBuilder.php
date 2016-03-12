<?php


namespace MacFJA\PharBuilder;

use Rych\ByteSize\ByteSize;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use webignition\ReadableDuration\ReadableDuration;

/**
 * Class PharBuilder
 * This class create the Phar file of a specific composer based project
 *
 * @package MacFJA\PharBuilder
 * @author  MacFJA
 * @license MIT
 */
class PharBuilder
{
    /**
     * The Phar object
     *
     * @var \Phar
     */
    protected $phar;
    /**
     * The path of phar file
     *
     * @var string
     */
    protected $pharName;
    /**
     * The path of the entry point of the application
     *
     * @var string
     */
    protected $stubFile;
    /**
     * The name of the phar inside the application
     *
     * @var string
     */
    protected $alias;
    /**
     * The CLI output interface (display message)
     *
     * @var OutputInterface
     */
    protected $output;
    /**
     * The path of the composer.json file
     *
     * @var string
     */
    protected $composer;
    /**
     * The compression type (see `$compressionList`)
     *
     * @var int
     */
    protected $compression;
    /**
     * List of directories to include
     *
     * @var string[]
     */
    protected $includes = array();

    /**
     * List of equivalence for compression
     *
     * @var array
     */
    private $compressionList = array(
        'no' => \Phar::NONE,
        'none' => \Phar::NONE,
        'gzip' => \Phar::GZ,
        'bzip2' => \Phar::BZ2,
    );

    /**
     * The class constructor
     *
     * @param OutputInterface $output      The CLI output interface (display message)
     * @param string          $composer    The path of the composer.json file
     * @param string          $outputDir   The path to the directory where the phar will be created
     * @param string          $pharName    The name of the phar
     * @param string          $stubFile    The path of entry point of the application
     * @param string          $compression The compression type of the Phar (no, none, gzip, bzip2)
     * @param string[]        $includes    List of directories to include
     *
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function __construct(
        OutputInterface $output,
        $composer,
        $outputDir,
        $pharName,
        $stubFile,
        $compression,
        $includes
    ) {
        $compression = strtolower($compression);

        $this->pharName    = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pharName;
        $this->stubFile    = $stubFile;
        $this->alias       = $pharName;
        $this->composer    = $composer;
        $this->output      = $output;
        $this->compression = array_key_exists($compression, $this->compressionList) ?
            $this->compressionList[$compression] : \Phar::NONE;
        $this->includes    = $includes;
        $this->buildPhar();
    }

    /**
     * The main function.
     * This function create the Phar file and add all file in it
     *
     * @return void
     *
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    protected function buildPhar()
    {
        $startTime = time();
        $this->output->write('Reading composer.json...', false);
        $dirsFiles = $this->readComposerAutoload();
        $this->output->writeln(' <info>OK</info>');

        $this->phar = new \Phar(
            $this->pharName,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME,
            $this->alias
        );
        $this->phar->startBuffering();

        $this->phar->setStub(
            '#!/usr/bin/env php' . PHP_EOL .
            '<?php Phar::mapPhar(); include "phar://' . $this->alias . '/' . $this->stubFile .
            '"; __HALT_COMPILER(); ?>'
        );

        chdir(dirname($this->composer));

        //Adding files to the archive
        $this->output->writeln('Adding files to Phar...');
        // Add all project file (based on composer declaration)
        foreach ($dirsFiles['dirs'] as $dir) {
            $this->addDir($dir);
        }
        foreach ($dirsFiles['files'] as $file) {
            $this->addFile($file);
        }
        // Add included directories
        foreach ($this->includes as $dir) {
            $this->addDir($dir);
        }
        // Add the composer vendor dir
        $this->addDir('vendor');
        $this->addFile('composer.json');
        $this->addFile('composer.lock');
        $this->addStub($this->stubFile);

        $this->output->writeln("\r\033[2K" . '   <info>All files added</info>');

        $this->phar->stopBuffering();

        $endTime = time();
        $size    = new ByteSize();
        $this->output->writeln(
            'File size: <comment>' . $size->formatBinary(filesize($this->pharName)) . '</comment>'
        );
        $this->output->writeln(
            'Process duration: <comment>' . $this->buildDuration($startTime, $endTime) . '</comment>' . PHP_EOL
        );

        $successMessage = 'Phar creation successful';
        $this->output->writeln('<success>  ' . str_repeat(' ', strlen($successMessage)) . '  </success>');
        $this->output->writeln(
            '<success>  [Success]' . str_repeat(' ', strlen($successMessage) - 9) . '  </success>'
        ); // -9 for the "[Success]" text
        $this->output->writeln('<success>  ' . $successMessage . '  </success>');
        $this->output->writeln('<success>  ' . str_repeat(' ', strlen($successMessage)) . '  </success>');
    }

    /**
     * Calculate and build a readable duration
     *
     * @param int $start start timestamp
     * @param int $end   end timestamp
     *
     * @return string
     */
    protected function buildDuration($start, $end)
    {
        $duration = new ReadableDuration($end - $start);
        $data     = $duration->getInMostAppropriateUnits(2);
        $result   = array();
        foreach ($data as $unit) {
            $result[] = $unit['value'] . ' ' . $unit['unit'] . ($unit['value'] > 1 ? 's' : '');
        }
        return implode(', ', $result);
    }

    /**
     * Add a directory in the Phar file
     *
     * @param string $directory The relative (to composer.json file) path of the directory
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function addDir($directory)
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        $files     = Finder::create()->files()
            ->ignoreVCS(true)//Remove VCS
            ->ignoreDotFiles(true)//Remove system hidden file
            ->notName('composer.*')//Remove composer configuration
            ->notName('*~')//Remove backup file
            ->notName('*.back')//Remove backup file
            ->notName('*.swp')//Remove backup file
            ->notName('phpunit*')//Remove Unit test
            ->exclude('Tests')//Remove Unit test
            ->exclude('tests')//Remove Unit test
            ->exclude('test')//Remove Unit test
            ->exclude('docs')//Remove documentation
            ->notPath('/.*phpunit\/.*/')//Remove Unit test
            ->notPath('/.*test(s)?\/.*/')//Remove Unit test
            ->in($directory);
        foreach ($files as $file) {
            /**
             * The found file
             *
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */
            $this->addFile($directory . DIRECTORY_SEPARATOR . $file->getRelativePathname());
        }
    }

    /**
     * Add stubfile to the Phar and remove the shebang if present
     *
     * @param string $filePath The path MUST be relative to the composer.json parent directory
     *
     * @return void
     */
    protected function addStub($filePath)
    {
        $this->output->write("\r\033[2K" . ' > ' . $filePath);

        $stub = file_get_contents($filePath);

        // Remove shebang if present
        $shebang = "~^#!/(.*)\n~";
        $stub    = preg_replace($shebang, '', $stub);

        $this->phar->addFromString($this->stubFile, $stub);
        $this->compressFile($filePath);
    }

    /**
     * Add a file to the Phar
     *
     * @param string $filePath The path MUST be relative to the composer.json parent directory
     *
     * @return void
     */
    protected function addFile($filePath)
    {
        $this->output->write("\r\033[2K" . ' > ' . $filePath);

        //Add the file
        $this->phar->addFile($filePath);
        // Compress the file (see the reason of one file compressing just after)
        $this->compressFile($filePath);
    }

    /**
     * Compress a given file (if compression is enabled and the file type is _compressible_)
     *
     * Note: The compression is made file by file because Phar have a bug with compressing the whole archive.
     * The problem is (if I understand correctly) a C implementation issue cause by temporary file resource
     * be opened but not closed (until the end of the compression).
     * This walk around reduce the performance of the Phar creation
     * (compared to the whole Phar compression(that can be done on small application))
     *
     * @param string $file The path in the Phar
     *
     * @return void
     */
    protected function compressFile($file)
    {
        // Check is compression is enable, if it's not the case stop right away, don't need to go further
        if (!in_array($this->compression, array(\Phar::BZ2, \Phar::GZ), true)) {
            return;
        }
        // Some frequent text based file extension that can be compressed in a good rate
        $toCompressExtension = array(
            '.php', '.txt', '.md', '.xml', '.js', '.css', '.less', '.scss', '.json', '.html', '.rst'
        );
        $canCompress         = false;
        foreach ($toCompressExtension as $extension) {
            if (substr($file, -strlen($extension)) === $extension) {
                $canCompress = true;
            }
        }
        if (!$canCompress) {
            return;
        }

        $this->output->write('...');
        $this->phar[$file]->compress($this->compression);
        $this->output->write(' <info>compressed</info>');
    }

    /**
     * Return the list of project directories
     *
     * @return array list of relative path
     */
    protected function readComposerAutoload()
    {
        $dirs  = array();
        $files = array();

        $composer  = json_decode(file_get_contents($this->composer), true);
        $autoloads = $composer['autoload'];
        foreach ($autoloads as $type => $autoload) {
            if (in_array($type, array('psr-4', 'psr-0'), true)) {
                foreach ($autoload as /*$namespace =>*/ $dir) {
                    $dirs[] = $dir;
                }
            } elseif (in_array($type, array('files', 'classmap'), true)) {
                foreach ($autoload as $item) {
                    if (is_dir($item)) {
                        $dirs[] = $item;
                    } elseif (is_file($item)) {
                        $files[] = $item;
                    }
                }
            }
        }
        return array('dirs' => $dirs, 'files' => $files);
    }
}
