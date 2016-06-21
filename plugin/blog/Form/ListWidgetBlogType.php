<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class ListWidgetBlogType extends AbstractType
{
    private $listWidgetBlogOrder;

    public function __construct(array $listWidgetBlogOrder)
    {
        $this->listWidgetBlogOrder = $listWidgetBlogOrder;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $mask = $form->getData();
        $newListWidgetOrder = [];

        $mask_pos = $mask;
        for ($impair = 1;$impair < strlen($mask);$impair += 2) {
            $mask_pos = substr_replace($mask_pos, 'x', $impair, 1);
        }

        for ($i = 0, $option = 0, $index = 0, $visible; $i < strlen($mask); $i += 2, $option++, $index++) {
            if ((int) $mask{$i + 1} == 1) {
                $visible = true;
            } else {
                $visible = false;
            }

            $pos_string_temp = strpos($mask_pos, strval($index));
            if ($pos_string_temp % 2 == 0) {
                $pos_string = $pos_string_temp / 2;
            }

            $newListWidgetOrder[(int) $pos_string] = [
                'label' => $this->listWidgetBlogOrder[$index],
                'visible' => $visible,
                'option' => $option,
            ];
        }

        ksort($newListWidgetOrder);

        $view->vars['widgetLists'] = $newListWidgetOrder;
    }

    public function getParent()
    {
        return 'hidden';
    }

    public function getName()
    {
        return 'listWidgetBlog';
    }
}
