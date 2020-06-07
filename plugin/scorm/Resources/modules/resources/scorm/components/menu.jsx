import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {PlayerMenu} from '#/plugin/scorm/resources/scorm/player/containers/menu'

const ScormMenu = props =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('claroline_scorm', {}, 'resource')}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/play',
          render() {
            const Menu = (
              <PlayerMenu autoClose={props.autoClose} />
            )

            return Menu
          }
        }
      ]}
    />
  </MenuSection>

ScormMenu.propTypes = {
  path: T.string.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ScormMenu
}
