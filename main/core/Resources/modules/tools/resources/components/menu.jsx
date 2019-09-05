import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {ResourceMenu} from '#/main/core/resource/containers/menu'

const RootMenu = props =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('resources', {}, 'tools')}
  >

  </MenuSection>

RootMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

const ResourcesMenu = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => {
          const Menu = (
            <RootMenu {...props} />
          )

          return Menu
        }
      }, {
        path: '/:id',
        render: () => {
          const Menu = (
            <ResourceMenu {...omit(props, 'path')} />
          )

          return Menu
        }
      }
    ]}
  />

ResourcesMenu.propTypes = {
  path: T.string.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  ResourcesMenu
}
