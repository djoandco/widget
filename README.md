# widget



##in your composer.json add: 

```
"repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:djoandco/widget.git"
        }
    ],
```
###then execute:
```
    composer require djoandco/widget:dev-master
```

## packages/twig.yaml

```

twig:
    default_path: '%kernel.project_dir%/templates'
    debug: '%kernel.debug%'
    strict_variables: '%kernel.debug%'
    globals:
        # the value is the service's id
      
   
```


## create the class: HelperCommand in src/Command

```

<?php
// src/Command

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;


class HelperCommand extends Command
{
    protected static $defaultName = 'dev:helper';


    public function __construct(

    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Help you to understand everything. :)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select what you need to know (defaults to widget)',
            ['widget'],
            0
        );
        $question->setErrorMessage('%s is invalid.');

        $doc = $helper->ask($input, $output, $question);

        switch ($doc) {
            case 'widget':
                $question = new ChoiceQuestion(
                    'Please select what you need to do (defaults to create)',
                    ['create'],
                    0
                );
                $question->setErrorMessage('%s is invalid.');
                $whatToDo = $helper->ask($input, $output, $question);

                switch ($whatToDo) {
                    case 'create':
                        $question = new Question('Please, chose your widget\'s name: ', 'AcmeWidget');
                        $question->setValidator(function ($answer) {
                            if (!is_string($answer) || 'Widget' !== substr($answer, -6) || 'AcmeWidget' === $answer) {
                                throw new \RuntimeException(
                                    'The name of the Widget should be suffixed with \'Widget\''
                                );
                            }

                            return $answer;
                        });
                        $question->setMaxAttempts(3);


                        $widgetName = $helper->ask($input, $output, $question);

                        if (!file_exists('src/Widget/')) {
                            mkdir('src/Widget/');
                        }

                        $ClassWidgetHandle = fopen('src/Widget/'. $widgetName . '.php', 'w') or die('Cannot open file:  '.$widgetName); //implicitly creates file
                        fwrite($ClassWidgetHandle, $this->getDataClassWidget($widgetName));

                        if (!file_exists('templates/widget/')) {
                            mkdir('templates/widget/');
                        }

                        $templateWidgetHandle = fopen('templates/widget/'. $widgetName . '.html.twig', 'w') or die('Cannot open file:  '.$widgetName); //implicitly creates file
                        fwrite($templateWidgetHandle, "<p class='". strtolower($widgetName) ."'>I'm here dude, my name is " . $widgetName . "</p>");

                        if (!file_exists('public/css/')) {
                            mkdir('public/css/');
                        }

                        if (!file_exists('public/css/widget/')) {
                            mkdir('public/css/widget/');
                        }

                        $cssWidgetHandle = fopen('public/css/widget/'. $widgetName . '.css', 'w') or die('Cannot open file:  '.$widgetName); //implicitly creates file
                        fwrite($cssWidgetHandle, $this->getDataCssWidget($widgetName));

                        if (!file_exists('config/widget/')) {
                            mkdir('config/widget/');
                        }

                        $configWidgetHandle = fopen('config/widget/'. $widgetName . '.yaml', 'w') or die('Cannot open file:  '.$widgetName); //implicitly creates file
                        fwrite($configWidgetHandle, $this->getDataConfigWidget());

                        $twigConfigHandle = fopen('config/packages/twig.yaml', 'a') or die('Cannot open file:  '.$widgetName); //implicitly creates file
                        $newLineInTwigConfigFile = "\n"."        " . substr_replace(strtolower($widgetName), '', -6) . "_widget: '@App\Widget\\" . $widgetName . "'";
                        fwrite($twigConfigHandle, $newLineInTwigConfigFile);

                        $io->success("It's done :)");
                        $io->note("Now, copy/paste this '{{ " .substr_replace(strtolower($widgetName), '', -6) . "_widget.render()|raw }}' in a template :')");


                        break;
                }
                break;
        }

    }

    private function getDataClassWidget($widgetName) {
        return
            '<?php
namespace App\Widget;

use Djoandco\WidgetBundle\Core\AbstractWidget;

class '. $widgetName .' extends AbstractWidget
{
    const WIDGET_NAME = \''.$widgetName.'\';

    public function render($options = [])
    {
       
        $this->getConfig();
        //$this->addParams([\'title\' => $options[\'title\']]);
        return parent::render();
    }
}
';
    }

    private function getDataConfigWidget() {
        return '#Here, you can pass values to your widget
#This file have to write like your const WIDGET_NAME following to the extension ".yaml"
widget:
    #criteria:
    #    disable: false
    #orderBy:
    #    createdAt: DESC
    #limit: 3

    #Desable widget
    #enable: true

    #Load Custom Css Files which have to be in "public/widget/
    loader:
        - css';
    }

    private function getDataCssWidget($widgetName) {
        return '.' . strtolower($widgetName) . ' {
    background-color: sandybrown;
    color: white;
}';
    }
}


```

### Run

```
bin/console dev:helper and following intructions
```

# Enjoy

