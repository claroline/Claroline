import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

const Nav = () =>
  <Vertical
    tabs={[
      {
        icon: 'fa fa-fw fa-cog',
        title: trans('parameters'),
        path: '/main'
      }, {
        icon: 'fa fa-fw fa-edit',
        title: trans('icons'),
        path: '/icons'
      },  {
        icon: 'fa fa-fw fa-paint-brush',
        title: trans('themes_management'),
        path: '/themes'
      }
    ]}
  />


export {
  Nav
}
