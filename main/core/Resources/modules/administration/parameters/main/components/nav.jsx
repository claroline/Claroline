import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

const Nav = () =>
  <Vertical
    tabs={[
      {
        icon: 'fa fa-fw fa-id-card',
        title: trans('identification'),
        path: '/identification'
      },
      {
        icon: 'fa fa-fw fa-home',
        title: trans('home'),
        path: '/home'
      },
      {
        icon: 'fa fa-fw fa-language',
        title: trans('language'),
        path: '/i18n'
      },
      {
        icon: 'fa fa-fw fa-plug',
        title: trans('plugins'),
        path: '/plugins'
      },
      {
        icon: 'fa fa-fw fa-wrench',
        title: trans('maintenance'),
        path: '/maintenance'
      }
    ]}
  />


export {
  Nav
}
