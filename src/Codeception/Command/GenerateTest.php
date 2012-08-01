<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateTest extends Base
{
    protected $template  = <<<EOF
<?php
%s
use Codeception\Util\Stub;

%s %sTest extends \Codeception\TestCase\Test
{
   /**
    * @var %s
    */
    protected $%s;


    // keep this setupUp and tearDown to enable proper work of Codeception modules
    protected function setUp()
    {
        if (\$this->bootstrap) require \$this->bootstrap;
        \$this->dispatcher->dispatch('test.before', new \Codeception\Event\Test(\$this));
        \$this->%s = new %s(\$scenario = new \Codeception\Scenario(\$this));
        \$scenario->run();

        // your own initialization code
    }

    protected function tearDown()
    {
        \$this->dispatcher->dispatch('test.after', new \Codeception\Event\Test(\$this));
    }

    // yourt tests

}
EOF;


    protected function configure()
    {
        $this->setDefinition(array(

            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new \Symfony\Component\Console\Input\InputArgument('class', InputArgument::REQUIRED, 'class name'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty Cest file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = \Codeception\Configuration::config();
        $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);

        $guy = $suiteconf['class_name'];

        $namespaces = explode('\\', $class);
        $classname = array_pop($namespaces);

        $use = '';

        $path = $suiteconf['path'];
        foreach ($namespaces as $namespace) {
            $path .= DIRECTORY_SEPARATOR.$namespace;
            @mkdir($path);
        }

        if (!empty($namespaces)) $use = 'namespace '.implode('\\', $namespaces).";\n";

        $filename = $path.DIRECTORY_SEPARATOR.$classname.'Test.php';

        if (file_exists($filename)) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        file_put_contents($filename, sprintf($this->template, $use, 'class', $classname, $guy, lcfirst($guy), lcfirst($guy), $guy));

        $output->writeln("<info>Test for $class was created in $filename</info>");

    }
}