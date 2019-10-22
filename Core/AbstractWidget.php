<?php

namespace Widget\Core;

use App\Entity\Category;
use App\Model\WidgetInterface;
use App\Repository\CategoryRepository;
use App\Repository\JobRepository;
use App\Repository\StepRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractWidget
 * @package App\Core
 */
abstract class AbstractWidget implements WidgetInterface
{

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var JobRepository
     */
    protected $jobRepository;

    protected $config = null;

    protected $params = [];

    private $widgetName;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var StepRepository
     */
    protected $stepRepository;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Router
     */
    protected $router;

    /**
     * AbstractWidget constructor.
     * @param Environment $environment
     * @param EntityManagerInterface $entityManager
     * @param JobRepository $jobRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        Environment $environment,
        EntityManagerInterface $entityManager,
        JobRepository $jobRepository,
        CategoryRepository $categoryRepository,
        StepRepository $stepRepository,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        RouterInterface $router
    )
    {
        $this->widgetName = static::WIDGET_NAME;
        $this->environment = $environment;

        $this->entityManager = $entityManager;
        $this->jobRepository = $jobRepository;
        $this->categoryRepository = $categoryRepository;
        $this->stepRepository = $stepRepository;
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /**
     * @return array|mixed|null
     */
    protected function getConfig()
    {

        //Get your config widget file in "config/widget/widget_name.yaml"
        try {
            $this->config = Yaml::parseFile('../config/widget/' . $this->widgetName . '.yaml');
            $this->getFiles();
        } catch (ParseException $exception) {
            printf('Unable to parse the YAML string: %s', $exception->getMessage());
        }

        return $this->config;
    }

    /**
     * @param array $options
     * @return string|null
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render($options = [])
    {
        //According to your file in "config/widget/widget_name.yaml"
        //You can set the key enable to false to desactivate the widget's rendering
        if (isset($this->config['widget']['enable']) && false === $this->config['widget']['enable']){
            return null;
        }

        return $this->environment->render('widget/' . $this->widgetName . '.html.twig', [
            'params' => $this->params
        ]);
    }

    /**
     * @param array $params
     * Add elements to render in the final view
     */
    protected function addParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return array|mixed|null
     */
    private function getFiles()
    {
        if (isset($this->config['widget']['loader']) && in_array('css', $this->config['widget']['loader'])) {
            $css = '';
            $handle = '';

            $file = $this->widgetName . '.css';

            // open the "css" directory
            if ($handle = opendir('css/widget')) {
                if ((file_exists ('css/widget/' .$file))) {
                    // list directory contents
                    while (false !== ($file = readdir($handle))) {
                        // only grab file names
                        if (is_file('css/widget/' . $file)) {
                            // insert HTML code for loading css files
                            $css .= '<link rel="stylesheet" href="/css/widget/' . $file .
                                '" type="text/css" media="all" />' . "\n";
                        }
                    }
                    closedir($handle);
                    echo $css;

                } else {
                    throw new Exception("According to '/config/widget/" . $this->widgetName .".yaml, you have to create the ". $file . " in /public/css/widget");
                }
            }

            return new JsonResponse(['message' => $file . " wasn't loaded correctly."], 200);
        }

        return new JsonResponse(['message' => "the file wasn't loaded correctly."], 200);
    }
}
