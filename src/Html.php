<?php

namespace Snscripts\HtmlHelper;

use \Snscripts\HtmlHelper\Interfaces\Router as RouterInterface;
use \Snscripts\HtmlHelper\Interfaces\FormData as FormDataInterface;
use \Snscripts\HtmlHelper\Interfaces\Assets as AssetsInterface;

class Html
{
    protected $RouterInterface;
    protected $FormDataInterface;
    protected $AssetsInterface;

    protected $Attr;
    public $Form;

    /**
     * setup the helper, providing the interfaces
     * for routes/ form data and assets
     *
     * @param   Object  Instance of an RouterInterface
     * @param   Object  Instance of an FormDataInterface
     * @param   Object  Instance of an AssetsInterface
     */
    public function __construct(
        RouterInterface $Router,
        FormDataInterface $FormData,
        AssetsInterface $Assets
    ) {
        $this->setRouter($Router);
        $this->setFormData($FormData);
        $this->setAssets($Assets);

        $this->Attr = new \Snscripts\HtmlAttributes\Attributes;
        $this->Form = new \Snscripts\HtmlHelper\Helpers\Form($this);
    }

    /**
     * render a tag
     *
     * @param   string      tag to render
     * @param   array       attributes for the tag
     * @param   string      contents of tag when not self closing
     * @param   bool        self-close the tag?
     * @return  string
     */
    public function tag($tag, $attr = null, $content = null, $close = false)
    {
        $tag = strtolower($tag);

        if (! empty($attr) && is_array($attr)) {
            $attr = $this->Attr->attr($attr);
        }

        if ($close) {
            return sprintf('<%s%s>%s</%1$s>', $tag, $attr, $content);
        } else {
            return sprintf('<%s%s>', $tag, $attr);
        }
    }

    /**
     * shortcut for a div
     *
     * @param   string      Contents of the div
     * @param   array       Array of attributes for the div
     * @return  string
     */
    public function div($contents, $attr = null)
    {
        return $this->tag('div', $attr, $contents, true);
    }

    /**
     * shortcut for a paragraph
     *
     * @param   string      Contents of the div
     * @param   array       Array of attributes for the div
     * @return  string
     */
    public function p($contents, $attr = null)
    {
        return $this->tag('p', $attr, $contents, true);
    }

    /**
     * unordered list
     *
     * @param   array   Array of items to list / attr
     * @param   array   Array of attributes for the ul
     * @return  string
     */
    public function ul($list, $attr = null)
    {
        $out = $this->tag('ul', $attr);

        if (! empty($list) && is_array($list)) {
            $out .= $this->processList($list);
        }

        $out .= $this->tag('/ul');

        return $out;
    }

    /**
     * ordered list
     *
     * @param   array   Array of items to list / attr
     * @param   array   Array of attributes for the ul
     * @return  string
     */
    public function ol($list, $attr = null)
    {
        $out = $this->tag('ol', $attr);

        if (! empty($list) && is_array($list)) {
            $out .= $this->processList($list, 'ol');
        }

        $out .= $this->tag('/ol');

        return $out;
    }

    /**
     * process list items
     *
     * @todo    add ability to define sublists from within the list
     * @param   array   Array of list items to process
     * @param   string  method to use for sub-lists
     * @return  string
     */
    public function processList($list, $subListType = 'ul')
    {
        $out = '';

        foreach ($list as $key => $value) {
            if (is_array($value) && (isset($value['list']) || isset($value['attr']))) {
                $attr = (isset($value['attr'])) ? $value['attr'] : null;
                $listAttr = (isset($value['listAttr'])) ? $value['listAttr'] : null;
                $subList = (isset($value['list'])) ? $this->{$subListType}($value['list'], $listAttr) : '';

                $out .= $this->tag('li', $attr, $key . $subList, true);
            } elseif (is_array($value)) {
                $out .= $this->tag('li', null, $key . $this->{$subListType}($value), true);
            } else {
                $out .= $this->tag('li', null, $value, true);
            }
        }

        return $out;
    }

    /**
     * create a link
     *
     * @param   string  Link text
     * @param   mixed   url data. Will be passed to the router interface for processing
     * @param   array   attributes to place on the link tag
     * @return  string
     */
    public function link($text, $url = null, $attr = null)
    {
        $url = $this->Router->getUrl($url);
        $attr['href'] = $url;
        return $this->tag('a', $attr, $text, true);
    }

    /**
     * create an image
     *
     * @param   mixed   image path data - will be passed to the assets interface for processing
     * @param   array   attributes to be placed on the img tag
     * @return  string
     */
    public function image($src, $attr = array())
    {
        $src = $this->Assets->getImage($src);
        $attr['src'] = $src;
        return $this->tag('img', $attr);
    }

    /**
     * create a style link
     *
     * @param   mixed   style path data - will be passed to the assets interface for processing
     * @param   array   attributes to be placed on the link tag
     * @return  string
     */
    public function style($src, $attr = array())
    {
        $src = $this->Assets->getStyle($src);
        $attr['href'] = $src;

/*        $attr = $this->Attr->mergeAttr(
            array(
                'media' => 'screen',
                'rel' => 'stylesheet',
                'type' => 'text/css'
            ),
            $attr
        );*/
        $attr = array_merge(
            array(
                'media' => 'screen',
                'rel' => 'stylesheet',
                'type' => 'text/css'
            ),
            $attr
        );

        return $this->tag('link', $attr);
    }

    /**
     * create a script tag
     *
     * @param   mixed   script path data - will be passed to the assets interface for processing
     * @param   array   attributes to be placed on the script tag
     * @return  string
     */
    public function script($src, $attr = array())
    {
        $src = $this->Assets->getScript($src);
        $attr['src'] = $src;
        return $this->tag('script', $attr, '', true);
    }

    /**
     * check and set the router interface
     *
     * @param   Object  Instance of an RouterInterface
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    public function setRouter($Router)
    {
        if (! is_object($Router) || ! $Router instanceof RouterInterface) {
            throw new \InvalidArgumentException(
                'The Router Interface must be a valid RouterInterface Object'
            );
        }
        $this->Router = $Router;

        return true;
    }

    /**
     * check and set the form data interface
     *
     * @param   Object  Instance of an FormDataInterface
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    public function setFormData($FormData)
    {
        if (! is_object($FormData) || ! $FormData instanceof FormDataInterface) {
            throw new \InvalidArgumentException(
                'The FormData Interface must be a valid FormDataInterface Object'
            );
        }
        $this->FormData = $FormData;

        return true;
    }

    /**
     * check and set the Asset interface
     *
     * @param   Object  Instance of an AssetsInterface
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    public function setAssets($Assets)
    {
        if (! is_object($Assets) || ! $Assets instanceof AssetsInterface) {
            throw new \InvalidArgumentException(
                'The Assets Interface must be a valid AssetsInterface Object'
            );
        }
        $this->Assets = $Assets;

        return true;
    }
}
