<?php

namespace ITE\Js\AjaxBlock\Twig\Node;

use Twig_Compiler;

/**
 * Class AjaxBlockReferenceNode
 *
 * @package ITE\Js\AjaxBlock\Twig\Node
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class AjaxBlockReferenceNode extends \Twig_Node_BlockReference
{
    /**
     * @var string
     */
    private $realName;

    /**
     * {@inheritdoc}
     */
    public function __construct($realName, $name, $lineno, $tag = null)
    {
        parent::__construct($name, $lineno, $tag);
        $this->realName = $realName;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->write("ob_start();\n")
        ;
        parent::compile($compiler);

        $compiler
            ->write(sprintf(
                "\$this->env->getExtension('ite_js.twig.ajax_block_extension')->addAjaxBlockContent('%s', ob_get_flush());\n",
                $this->realName
            ))
        ;
    }

}