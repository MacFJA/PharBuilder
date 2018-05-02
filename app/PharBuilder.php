<?php


namespace MacFJA\PharBuilder;

use MacFJA\PharBuilder\Utils\Composer;
use Rych\ByteSize\ByteSize;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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
     * Character list of directory separators
     */
    const DIRECTORY_SEPARATORS = '/\\';

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
    protected $pharName = '';
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
     * The Symfony Style Input/Output
     *
     * @var SymfonyStyle
     */
    protected $ioStyle;
    /**
     * The compression type (see `$compressionList`)
     *
     * @var int
     */
    protected $compression = \Phar::NONE;
    /**
     * List of directories to include
     *
     * @var string[]
     */
    protected $includes = array();
    /**
     * Keep require-dev only package?
     *
     * @var bool
     */
    protected $keepDev = false;
    /**
     * The composer file reader
     *
     * @var Composer
     */
    protected $composerReader;

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
     * Skip the shebang?
     *
     * @var bool
     */
    protected $skipShebang = false;

    /**
     * Get the name of the PHAR
     *
     * @return string
     */
    public function getPharName()
    {
        return $this->alias;
    }

    /**
     * Get the directory path where the PHAR will be built
     *
     * @return string
     */
    public function getOutputDir()
    {
        return dirname($this->pharName);
    }

    /**
     * Set the output directory path
     *
     * @param string $directory The output directory path
     *
     * @return void
     */
    public function setOutputDir($directory)
    {
        $this->pharName = rtrim($directory, self::DIRECTORY_SEPARATORS) . DIRECTORY_SEPARATOR . basename($this->pharName);
    }

    /**
     * Set the name of the PHAR
     *
     * @param string $pharName The name
     *
     * @return void
     */
    public function setPharName($pharName)
    {
        if (empty($this->alias)) {
            $this->pharName .= $pharName;
        } else {
            $this->pharName = dirname($this->pharName) . DIRECTORY_SEPARATOR . $pharName;
        }
        $this->alias = $pharName;
    }

    /**
     * Get the path of the stub (entry-point)
     *
     * @return string
     */
    public function getStubFile()
    {
        return $this->stubFile;
    }

    /**
     * Set the path of the stub (entry-point)
     *
     * @param string $stubFile The path
     *
     * @return void
     */
    public function setStubFile($stubFile)
    {
        $this->stubFile = $stubFile;
    }

    /**
     * Get the compression name
     *
     * @return string
     */
    public function getCompression()
    {
        return array_search($this->compression, $this->compressionList, true);
    }

    /**
     * Set the compression
     *
     * @param string $compression The compression name
     *
     * @return void
     */
    public function setCompression($compression)
    {
        $compression       = strtolower($compression);
        $this->compression = array_key_exists($compression, $this->compressionList) ?
            $this->compressionList[$compression] : \Phar::NONE;
    }

    /**
     * Get the list of path that will be included
     *
     * @return \string[]
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * Set the list of path that will be included
     *
     * @param \string[] $includes The list of path
     *
     * @return void
     */
    public function setIncludes($includes)
    {
        $this->includes = $includes;
    }

    /**
     * Indicate if dev source/package must be added to the PHAR
     *
     * @return boolean
     */
    public function isKeepDev()
    {
        return $this->keepDev;
    }

    /**
     * Indicate if dev source/package must be added to the PHAR
     *
     * @param boolean $keepDev The value
     *
     * @return void
     */
    public function setKeepDev($keepDev)
    {
        $this->keepDev = $keepDev;
    }

    /**
     * Set the path the composer.json file
     *
     * @param string $composer The path the composer.json file
     *
     * @return void
     */
    public function setComposer($composer)
    {
        $this->composerReader = new Composer($composer);
    }

    /**
     * Get the composer reader object
     *
     * @return Composer
     */
    public function getComposerReader()
    {
        return $this->composerReader;
    }
    
    /**
     * Sets the skip shebang flag.
     *
     * @param bool $skipShebang skip the shebang or not
     *
     * @return void
     */
    public function setSkipShebang($skipShebang)
    {
        $this->skipShebang = (bool) $skipShebang;
    }
    
    /**
     * Indicates whether the shebang should be skipped or not.
     *
     * @return bool
     */
    public function isSkipShebang()
    {
        return $this->skipShebang;
    }

    /**
     * The class constructor
     *
     * @param SymfonyStyle $ioStyle The Symfony Style Input/Output
     */
    public function __construct(SymfonyStyle $ioStyle)
    {
        $this->ioStyle = $ioStyle;
    }

    /**
     * Check if all required data are provided
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function checkAllData()
    {
        $errors = array();
        if ('' == $this->getStubFile()) {
            $errors[] = 'The stub file is missing.';
        }
        if ('' == $this->getOutputDir()) {
            $errors[] = 'The output directory is missing';
        }
        if ('' == $this->getPharName()) {
            $errors[] = 'The name of the phar is missing';
        }
        if ($this->getComposerReader() === null || '' == $this->composerReader->getComposerJsonPath()) {
            $errors[] = 'The composer.json file is missing';
        }

        if (count($errors) > 0) {
            throw new \InvalidArgumentException(implode(PHP_EOL, $errors));
        }
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
     * @throws \RuntimeException
     */
    public function buildPhar()
    {
        $this->checkAllData();

        $startTime = time();

        $this->ioStyle->title('Creating your Phar application...');

        $this->ioStyle->section('Reading composer.json...');
        $composerInfo = $this->readComposerAutoload();
        $this->ioStyle->success('composer.json analysed');

        // Unlink, otherwise we just add things to the already existing phar
        if (file_exists($this->pharName)) {
            unlink($this->pharName);
        }

        chdir(dirname($this->composerReader->getComposerJsonPath()));

        $this->phar = new \Phar(
            $this->pharName,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME,
            $this->alias
        );
        $this->phar->startBuffering();

        $this->stubFile = $this->makePathRelative($this->stubFile);
        $this->phar->setStub(
            (!$this->isSkipShebang() ? '#!/usr/bin/env php' . "\n" : '') .
            '<?php Phar::mapPhar(); include "phar://' . $this->alias . '/' . $this->stubFile .
            '"; __HALT_COMPILER(); ?>'
        );

        //Adding files to the archive
        $this->ioStyle->section('Adding files to Phar...');

        // Add all project file (based on composer declaration)
        foreach ($composerInfo['dirs'] as $dir) {
            if (!is_array($dir)) {
                $dir = array($dir);
            }
            array_walk($dir, array($this, 'addDir'));
        }
        array_walk($composerInfo['files'], array($this, 'addFile'));
        array_walk($composerInfo['stubs'], array($this, 'addFakeFile'));
        // Add included directories
        array_walk($this->includes, array($this, 'addDir'));
        // Add the composer vendor dir
        $filesAutoload = $this->composerReader->getRemoveFilesAutoloadFor($composerInfo['excludes']);
        $this->addDir($composerInfo['vendor'], $composerInfo['excludes']);
        if ($filesAutoload !== null) {
            $this->addFakeFile($composerInfo['vendor'] . DIRECTORY_SEPARATOR .
                'composer' . DIRECTORY_SEPARATOR . 'autoload_files.php', $filesAutoload);
        }
        $this->addFile('composer.json');
        $this->addFile('composer.lock');
        $this->addStub();

        $this->ioStyle->success('All files added');

        $this->phar->stopBuffering();

        $endTime = time();
        $size    = new ByteSize();

        $this->ioStyle->success(array(
            'Phar creation successful',
            'File size: ' . $size->formatBinary(filesize($this->pharName)) . PHP_EOL .
            'Process duration: ' . $this->buildDuration($startTime, $endTime)
        ));
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
     * @param array  $excludes  List of path to exclude
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function addDir($directory, $excludes = array())
    {
        foreach ($excludes as &$exclude) {
            $exclude = str_replace(realpath($directory) . DIRECTORY_SEPARATOR, '', $exclude);
            $exclude = rtrim($exclude, DIRECTORY_SEPARATOR);
        }

        $directory = rtrim($directory, self::DIRECTORY_SEPARATORS);
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
            ->exclude($excludes)
            ->filter(function (SplFileInfo $fileInfo) {
                return !(
                    is_link($fileInfo->getPath())
                    && !file_exists($fileInfo->getPath())
                    || $fileInfo->getRealPath() === false
                );
            })
            ->in($directory);
        foreach ($files as $file) {
            /**
             * The found file
             *
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */
            $this->addFile(rtrim($directory, self::DIRECTORY_SEPARATORS) . DIRECTORY_SEPARATOR . $file->getRelativePathname());
        }
    }

    /**
     * Add stubfile to the Phar and remove the shebang if present
     *
     * @return void
     */
    protected function addStub()
    {
        $this->ioStyle->write("\r\033[2K" . ' > ' . $this->stubFile);

        $stub = file_get_contents($this->stubFile);

        // Remove shebang if present
        $shebang = "~^#!/(.*)\n~";
        $stub    = preg_replace($shebang, '', $stub);

        $this->phar->addFromString($this->stubFile, $stub);
        $this->compressFile($this->stubFile);
    }

    /**
     * Ensure that $path is a relative path
     *
     * @param string $path The path to test and correct
     *
     * @return string
     */
    protected function makePathRelative($path)
    {
        $path = $this->processDotNotatedPath($path);

        if (0 === strpos($path, getcwd())) {
            $path = substr($path, strlen(getcwd()));
            $path = ltrim($path, self::DIRECTORY_SEPARATORS);
        }

        return $path;
    }

    /**
     * Process dot notated relative paths
     *
     * @param string $path The path to resolve when it's a dot notated path (e.g. ./entry-file.php)
     *
     * @return string
     */
    protected function processDotNotatedPath($path)
    {
        if (preg_match('~^\.\.?[\\\/]~', $path) !== 1) {
            return $path;
        }

        $resolvedPath = realpath(getcwd() . '/' . $path);

        return $resolvedPath;
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
        //Clean-up path: Remove multiple directory separator
        $filePath = preg_replace('#[\\/]{2,}#', '/', $filePath);

        $this->ioStyle->write("\r\033[2K" . ' > ' . $filePath);

        //Add the file
        $this->phar->addFile($filePath);
        // Compress the file (see the reason of one file compressing just after)
        $this->compressFile($filePath);
    }

    /**
     * Add a fake file (stub) to the Phar
     *
     * @param string $filePath The path MUST be relative to the composer.json parent directory
     * @param string $content  The content of the fake file
     *
     * @return void
     */
    protected function addFakeFile($filePath, $content = '')
    {
        //Clean-up path: Remove multiple directory separator
        $filePath = preg_replace('#[\\/]{2,}#', '/', $filePath);

        $this->ioStyle->write("\r\033[2K" . ' > ' . $filePath);

        //Add the file
        $this->phar->addFromString($filePath, $content);
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
            '.php', '.txt', '.md', '.xml', '.js', '.css', '.less', '.scss', '.json', '.html', '.rst', '.svg'
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

        $this->ioStyle->write('...');
        $this->phar[$file]->compress($this->compression);
        $this->ioStyle->write(' <info>compressed</info>');
    }

    /**
     * Read composer's files
     *
     * The result array format is:
     *   - ["dirs"]: array, List of directories to include (project source)
     *   - ["files"]: array, List of files to include (project source)
     *   - ["vendor"]: string, Path to the composer vendor directory
     *   - ["exclude"]: List package name to exclude
     *   - ["stubs"]: List of files that have to be stubbed
     *
     * @return array list of relative path
     *
     * @throws \RuntimeException
     */
    protected function readComposerAutoload()
    {
        $paths = $this->composerReader->getSourcePaths($this->keepDev);

        $paths['excludes'] = array();
        $paths['vendor']   = $this->composerReader->getVendorDir();

        if (!$this->keepDev) {
            $paths['excludes'] = $this->composerReader->getDevOnlyPackageName();
        }

        $paths["stubs"] = array_map(function ($file) use ($paths) {
            $file = str_replace('/./', '/', $file);
            return $paths['vendor'] . "/" . $file;
        }, $this->composerReader->getStubFiles());

        return $paths;
    }
}
