<?php

namespace ITE\Js\AjaxBlock;

use ITE\Js\AjaxBlock\Annotation\AjaxBlock;
use ITE\JsBundle\EventListener\Event\AjaxRequestEvent;
use ITE\JsBundle\SF\SFExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\TemplateReference;

/**
 * Class AjaxBlockExtension
 *
 * @author sam0delkin <t.samodelkin@gmail.com>
 */
class AjaxBlockExtension extends SFExtension
{
    /**
     * @var AjaxBlockRenderer
     */
    protected $renderer;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param AjaxBlockRenderer $renderer
     * @param array             $options
     */
    public function __construct(AjaxBlockRenderer $renderer, array $options)
    {
        $this->renderer = $renderer;
        $this->options  = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascripts()
    {
        return [__DIR__ . '/Resources/public/js/sf.ajax_block.js'];
    }

    /**
     * {@inheritdoc}
     */
    public function onAjaxRequest(AjaxRequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_ajax_block')) {
            return;
        }

        $annotations = $request->attributes->get('_ajax_block');
        $single = 1 === count($annotations);

        $blocks = [];
        /** @var AjaxBlock $annotation */
        foreach ($annotations as $annotation) {
            $content = $this->renderer->render(
                $this->getTemplate($request, $annotation),
                $annotation->getBlockName(),
                $event->getControllerResult()
            );

            if ($single && !$annotation->getSelector()) {
                $event->setContent($content);

                return;
            }

            // we don't need to re-render the template
            if (null === $annotation->getTemplate()) {
                if (empty($event->getAjaxDataBag()->getOriginalResponse())) {
                    $event->getAjaxDataBag()->setOriginalResponse($this->renderer->getOriginalContent());
                }
            }

            if (!$annotation->getSelector()) {
                throw new \InvalidArgumentException('You should specify selector for multiple ajaxBlock annotations.');
            }

            /** @var AjaxBlock $annotation */
            $blocks[$annotation->getSelector()] = [
                'content' => $content,
                'show_animation' => [
                    'type' => null === $annotation->getShowAnimation()
                        ? $this->options['show_animation']['type']
                        : $annotation->getShowAnimation(),
                    'length' => null === $annotation->getShowLength()
                        ? $this->options['show_animation']['length']
                        : $annotation->getShowLength()
                ],
            ];
        }

        if (!empty($blocks)) {
            $event->getAjaxDataBag()->addBodyData('blocks', $blocks);
            $event->stopParentEventPropagation();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadConfiguration(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/Resources/config'));

        if ($config['extensions']['ajax_block']['enabled']) {
            $container->setParameter('ite_js.ajax_block.options', $config['extensions']['ajax_block']);
            $loader->load('ajax_block.yml');
        }
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration(ContainerBuilder $container)
    {
        $builder = new TreeBuilder();

        $rootNode = $builder->root('ajax_block');
        $rootNode
            ->canBeEnabled()
                ->children()
                    ->arrayNode('show_animation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->enumNode('type')
                                ->defaultValue('show')
                                ->values(['show', 'slide', 'fade'])
                                ->info('animation type')
                            ->end()
                            ->integerNode('length')
                                ->defaultValue(0)
                                ->min(0)
                                ->info('time in ms')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    /**
     * @param Request   $request
     * @param AjaxBlock $configuration
     * @return string
     */
    protected function getTemplate(Request $request, AjaxBlock $configuration)
    {
        if ($configuration->getTemplate()) {
            return $configuration->getTemplate();
        }

        /** @var TemplateReference $template */
        $template = $request->attributes->get('_template');
        if (!$template) {
            throw new \InvalidArgumentException('You should set template for render ajax_block.');
        }

        if (is_string($template)) {
            return $template;
        }

        $originalFormat = $template->get('format');
        $template->set('format', 'html');
        $templateName = $template->getPath();
        $template->set('format', $originalFormat);

        return $templateName;
    }
}
