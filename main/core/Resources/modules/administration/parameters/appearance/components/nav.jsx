import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

const Nav = () =>
  <Vertical
    tabs={[
      {
        icon: 'fa fa-fw fa-header',
        title: trans('header'),
        path: '/header'
      },
      {
        icon: 'fa fa-fw fa-copyright',
        title: trans('footer'),
        path: '/footer'
      },
      {
        icon: 'fa fa-fw fa-edit',
        title: trans('icons'),
        path: '/icons'
      } /* {
        icon: 'fa fa-fw fa-paint-brush',
        title: trans('themes_management'),
        path: '/themes'
      }*/
    ]}
  />


export {
  Nav
}
