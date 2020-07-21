<?php

namespace WidgetBundle\Core;

use App\Entity\Category;
use Doctrine\ODM\MongoDB\DocumentManager;
use Djoandco\WidgetBundle\Model\WidgetInterface;
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

    protected $documentManager;

    protected $config = null;

    protected $params = [];

    private $widgetName;

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
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        Environment $environment,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        RouterInterface $router,
        DocumentManager $documentManager
    )
    {
        $this->widgetName = static::WIDGET_NAME;
        $this->environment = $environment;

        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /**
     * @return array|mixed|null
     */
    protected function getConfig()
    {

        //Get your config widget-bundle file in "config/widget-bundle/widget_name.yaml"
        try {
            $this->config = Yaml::parseFile('../config/widget-bundle/' . $this->widgetName . '.yaml');
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
        //According to your file in "config/widget-bundle/widget_name.yaml"
        //You can set the key enable to false to desactivate the widget-bundle's rendering
        if (isset($this->config['widget-bundle']['enable']) && false === $this->config['widget-bundle']['enable']){
            return null;
        }

        return $this->environment->render('widget-bundle/' . $this->widgetName . '.html.twig', [
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
        if (isset($this->config['widget-bundle']['loader']) && in_array('css', $this->config['widget-bundle']['loader'])) {
            $css = '';
            $handle = '';

            $file = $this->widgetName . '.css';

            // open the "css" directory
            if ($handle = opendir('css/widget-bundle')) {
                if ((file_exists ('css/widget-bundle/' .$file))) {
                    // list directory contents
                    while (false !== ($file = readdir($handle))) {
                        // only grab file names
                        if (is_file('css/widget-bundle/' . $file)) {
                            // insert HTML code for loading css files
                            $css .= '<link rel="stylesheet" href="/css/widget-bundle/' . $file .
                                '" type="text/css" media="all" />' . "\n";
                        }
                    }
                    closedir($handle);
                    echo $css;

                } else {
                    throw new Exception("According to '/config/widget-bundle/" . $this->widgetName .".yaml, you have to create the ". $file . " in /public/css/widget-bundle");
                }
            }

            return new JsonResponse(['message' => $file . " wasn't loaded correctly."], 200);
        }

        return new JsonResponse(['message' => "the file wasn't loaded correctly."], 200);
    }
}
