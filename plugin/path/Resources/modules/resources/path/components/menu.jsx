import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {EditorMenu} from '#/plugin/path/resources/path/editor/containers/menu'
import {PlayerMenu} from '#/plugin/path/resources/path/player/containers/menu'

const PathMenu = props =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('innova_path', {}, 'resource')}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/edit',
          render: () => {
            return (
              <EditorMenu path={props.path} />
            )
          },
          disabled: !props.editable
        }, {
          path: '/',
          render: () => {
            return (
              <PlayerMenu path={props.path} />
            )
          }
        }
      ]}
    />
  </MenuSection>

PathMenu.propTypes = {
  path: T.string.isRequired,

  editable: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  PathMenu
}
