import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router/components/routes'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {UiMain} from '#/main/theme/administration/appearance/ui/containers/main'
import {IconMain} from '#/main/theme/administration/appearance/icon/containers/main'

const AppearanceTool = (props) =>
  <Fragment>
    <header className="row content-heading">
      <ContentTabs
        sections={[
          {
            name: 'user-interface',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-desktop',
            label: trans('user_interface', {}, 'appearance'),
            target: `${props.path}/appearance`,
            exact: true
          }, {
            name: 'icons',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-icons',
            label: trans('icons', {}, 'appearance'),
            target: `${props.path}/appearance/icons`
          }
        ]}
      />
    </header>

    <Routes
      path={`${props.path}/appearance`}
      routes={[
        {
          path: '/',
          exact: true,
          component: UiMain
        }, {
          path: '/icons',
          component: IconMain
        }
      ]}
    />
  </Fragment>

AppearanceTool.propTypes = {
  path: T.string.isRequired
}

export {
  AppearanceTool
}
