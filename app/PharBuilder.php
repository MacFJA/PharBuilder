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
 * @author  MacFJA
 * @package MacFJA\PharBuilder
 */
class PharBuilder
{
    /** @var \Phar The Phar object */
    protected $phar;
    /** @var string The path of phar file */
    protected $pharName;
    /** @var string The path of the entry point of the application */
    protected $stubFile;
    /** @var string The name of the phar inside the application */
    protected $alias;
    /** @var OutputInterface */
    protected $output;
    /** @var string The path of the composer.json file */
    protected $composer;
    /** @var int */
    protected $compression;
    /** @var string[] */
    protected $includes = array();

    /** @var array List of equivalence for compression */
    private $compressionList = array(
        'no' => \Phar::NONE,
        'none' => \Phar::NONE,
        'gzip' => \Phar::GZ,
        'bzip2' => \Phar::BZ2,
    );

    /**
     * @param OutputInterface $output
     * @param string          $composer    The path of the composer.json file
     * @param string          $outputDir   The path to the directory where the phar will be created
     * @param string          $pharName    The name of the phar
     * @param string          $stubFile    The path of entry point of the application
     * @param int             $compression The compression type of the Phar (see Phar constant)
     * @param string[]        $includes    List of directories to include
     */
    function __construct(OutputInterface $output, $composer, $outputDir, $pharName, $stubFile, $compression, $includes)
    {
        $compression = strtolower($compression);

        $this->pharName = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pharName;
        $this->stubFile = $stubFile;
        $this->alias = $pharName;
        $this->composer = $composer;
        $this->output = $output;
        $this->compression = array_key_exists($compression, $this->compressionList) ? $this->compressionList[$compression] : \Phar::NONE;
        $this->includes = $includes;
        $this->buildPhar();
    }

    /**
     * The main function.
     * This function create the Phar file and add all file in it
     */
    protected function buildPhar()
    {
        $startTime = time();
        $this->output->write('Reading composer.json...', false);
        $dirs = $this->readComposerAutoload();
        $this->output->writeln(' <info>OK</info>');

        $this->phar = new \Phar($this->pharName, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME, $this->alias);
        $this->phar->startBuffering();

        $this->phar->setStub(
            '<?php Phar::mapPhar(); include "phar://' . $this->alias . '/' . $this->stubFile . '"; __HALT_COMPILER(); ?>'
        );

        chdir(dirname($this->composer));

        //Adding files to the archive
        $this->output->writeln('Adding files to Phar...');
        // Add all project file (based on composer declaration)
        foreach ($dirs as $dir) {
            $this->addDir($dir);
        }
        // Add included directories
        foreach ($this->includes as $dir) {
            $this->addDir($dir);
        }
        // Add the composer vendor dir
        $this->addDir('vendor');
        $this->addFile('composer.json');
        $this->addFile('composer.lock');
        $this->addFile($this->stubFile);
        $this->output->writeln("\r\033[2K" . '   <info>All files added</info>');

        $this->phar->stopBuffering();

        $endTime = time();
        $this->output->writeln('File size: <comment>' . ByteSize::formatBinary(filesize($this->pharName)) . '</comment>');
        $this->output->writeln('Process duration: <comment>' . $this->buildDuration($startTime, $endTime) . '</comment>' . PHP_EOL);

        $successMessage = 'Phar creation successful';
        $this->output->writeln('<success>  ' . str_repeat(' ', strlen($successMessage)) . '  </success>');
        $this->output->writeln('<success>  [Success]' . str_repeat(' ', strlen($successMessage) - 9) . '  </success>'); // -9 for the "[Success]" text
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
        $data = $duration->getInMostAppropriateUnits(2);
        $result = array();
        foreach ($data as $unit) {
            $result[] = $unit['value'] . ' ' . $unit['unit'] . ($unit['value'] > 1 ? 's' : '');
        }
        return implode(', ', $result);
    }

    /**
     * Add a directory in the Phar file
     *
     * @param string $directory The relative (to composer.json file) path of the directory
     */
    protected function addDir($directory)
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        $files = Finder::create()->files()
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
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $this->addFile($directory . DIRECTORY_SEPARATOR . $file->getRelativePathname());
        }
    }

    /**
     * Add a file in the Phar
     *
     * @param string $filePath The path MUST be relative to the composer.json parent directory
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
     * The problem is (if I understand correctly) a C implementation issue cause by temporary file resource be opened but not closed (until the end of the compression).
     * This walk around reduce the performance of the Phar creation (compared to the whole Phar compression (that can be done on small application))
     *
     * @param string $file The path in the Phar
     */
    protected function compressFile($file)
    {
        // Check is compression is enable, if it's not the case stop right away, don't need to go further
        if (!in_array($this->compression, array(\Phar::BZ2, \Phar::GZ))) {
            return;
        }
        // Some frequent text based file extension that can be compressed in a good rate
        $toCompressExtension = array('.php', '.txt', '.md', '.xml', '.js', '.css', '.less', '.scss', '.json', '.html', '.rst');
        $canCompress = false;
        foreach ($toCompressExtension as $extension) {
            if (substr($file, -strlen($extension)) == $extension) {
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
    function readComposerAutoload()
    {
        $dirs = array();

        $composer = json_decode(file_get_contents($this->composer), true);
        $autoloads = $composer['autoload'];
        foreach ($autoloads as $autoload) {
            foreach ($autoload as /*$namespace =>*/
                     $dir) {
                $dirs[] = $dir;
            }
        }
        return $dirs;
    }
}