import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router/components/routes'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {UiMain} from '#/main/theme/administration/appearance/ui/containers/main'
import {IconMain} from '#/main/theme/administration/appearance/icon/containers/main'

import {AppearanceThemes} from '#/main/theme/administration/appearance/components/themes'
import {AppearancePosters} from '#/main/theme/administration/appearance/components/posters'
import {AppearanceColors} from '#/main/theme/administration/appearance/components/colors'

const AppearanceMain = (props) =>
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
            name: 'themes',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-swatchbook',
            label: trans('themes', {}, 'appearance'),
            target: `${props.path}/appearance/themes`
          }, {
            name: 'icons',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-icons',
            label: trans('icons', {}, 'appearance'),
            target: `${props.path}/appearance/icons`
          }, {
            name: 'posters',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-image',
            label: trans('posters', {}, 'appearance'),
            target: `${props.path}/appearance/posters`
          }, {
            name: 'colors',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-palette',
            label: trans('colors', {}, 'appearance'),
            target: `${props.path}/appearance/colors`
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
          path: '/themes',
          render: () => (
            <AppearanceThemes path={`${props.path}/appearance`} />
          )
        }, {
          path: '/icons',
          component: IconMain
        }, {
          path: '/posters',
          render: () => (
            <AppearancePosters path={`${props.path}/appearance`} />
          )
        }, {
          path: '/colors',
          render: () => (
            <AppearanceColors path={`${props.path}/appearance`} />
          )
        }
      ]}
    />
  </Fragment>

AppearanceMain.propTypes = {
  path: T.string.isRequired
}

export {
  AppearanceMain
}
