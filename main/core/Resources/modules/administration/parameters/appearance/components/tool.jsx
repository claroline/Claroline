import React from 'react'

import {ToolPage} from '#/main/core/tool/containers/page'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {Layout} from '#/main/core/administration/parameters/appearance/components/layout'
import {Icons} from '#/main/core/administration/parameters/appearance/components/icons'
//import {Theme} from '#/main/core/administration/parameters/appearance/components/theme'

const Tool = () =>
  <ToolPage>
    <div className="row">
      <div className="col-md-3">
        <Vertical
          tabs={[
            {
              icon: 'fa fa-fw fa-layer-group',
              title: trans('layout'),
              path: '/layout'
            }, {
              icon: 'fa fa-fw fa-edit',
              title: trans('icons'),
              path: '/icons'
            }/*, {
              icon: 'fa fa-fw fa-swatchbook',
              title: trans('theme'),
              path: '/theme'
            }*/
          ]}
        />
      </div>

      <div className="col-md-9">
        <Routes
          redirect={[
            {from: '/', exact: true, to: '/layout' }
          ]}
          routes={[
            {
              path: '/layout',
              component: Layout
            }, {
              path: '/icons',
              component: Icons
            }/*, {
               path: '/theme',
               component: Theme
             }*/
          ]}
        />
      </div>
    </div>
  </ToolPage>

export {
  Tool
}
