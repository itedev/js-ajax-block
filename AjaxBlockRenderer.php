<?php

namespace ITE\Js\AjaxBlock;

/**
 * Class AjaxBlockRenderer
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class AjaxBlockRenderer
{
    /**
     * @var array
     */
    protected $storage = array();

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $originalContent = '';

    /**
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Renders ajax block from the base template with given context.
     *
     * @param string $baseTemplateName
     * @param string $ajaxBlockName
     * @param array  $context
     * @param bool   $optional
     *
     * @return string
     */
    public function render($baseTemplateName, $ajaxBlockName, $context = array(), $optional = false)
    {
        $blockHash = $this->generateBlockHash($baseTemplateName, $ajaxBlockName);

        if (isset($this->storage[$blockHash])) {
            return $this->storage[$blockHash];
        }

        AjaxBlockStorage::clearStorage();
        $this->originalContent = $this->twig->render($baseTemplateName, $context);

        foreach (AjaxBlockStorage::getStorage() as $blockName => $blockContent) {
            $hash                 = $this->generateBlockHash($baseTemplateName, $blockName);
            $this->storage[$hash] = $blockContent;
        }

        if (!isset($this->storage[$blockHash])) {
            if ($optional) {
                return false;
            }

            throw new \InvalidArgumentException(
              sprintf('Ajax block "%s" was not found in the template "%s".',
              $ajaxBlockName,
              $baseTemplateName
            ));
        }

        return $this->storage[$blockHash];
    }

    /**
     * Generates hash for identify needed ajax block.
     *
     * @param string $baseTemplateName
     * @param string $ajaxBlockName
     *
     * @return string
     */
    protected function generateBlockHash($baseTemplateName, $ajaxBlockName)
    {
        $hashString = $baseTemplateName . $ajaxBlockName;

        return md5($hashString);
    }

    /**
     * Get originalContent
     *
     * @return string
     */
    public function getOriginalContent()
    {
        return $this->originalContent;
    }
}