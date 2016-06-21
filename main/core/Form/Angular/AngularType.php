<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Angular;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

abstract class AngularType extends AbstractType
{
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $model = isset($options['ng-model']) ? $options['ng-model'] : 'data';
        $alias = isset($options['ng-controllerAs']) ? $options['ng-controllerAs'] : 'modal';
        $this->setParam($view,  array('model' => $model));
        $this->setParam($view,  array('alias' => $alias));
    }

    private function setParam(FormView $view, array $params)
    {
        $this->updateParam($view, $params);
        $this->updateChild($view, $params);
    }

    private function updateChild(FormView $parent, array $params)
    {
        foreach ($parent->children as $child) {
            $this->updateParam($child, $params);
            $this->updateChild($child, $params);
        }
    }

    private function updateParam(FormView $view, array $params)
    {
        foreach ($params as $key => $value) {
            $view->vars[$key] = $value;
        }
    }
}
