<?php

namespace ITE\Js\AjaxBlock\Twig\Extension;

use ITE\Js\AjaxBlock\AjaxBlockStorage;
use ITE\Js\AjaxBlock\Twig\TokenParser\AjaxBlockTokenParser;

/**
 * Class AjaxBlockExtension
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class AjaxBlockExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new AjaxBlockTokenParser(),
        ];
    }

    /**
     * @param string $blockName
     * @param string $content
     */
    public function addAjaxBlockContent($blockName, $content)
    {
        AjaxBlockStorage::addAjaxBlock($blockName, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ite_js.twig.ajax_block_extension';
    }

}