import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {EditorMenu} from '#/plugin/exo/resources/quiz/editor/containers/menu'

// TODO : add menu in player too with a config

const QuizMenu = props =>
  <MenuSection
    {...omit(props, 'path', 'editable')}
    title={trans('ujm_exercise', {}, 'resource')}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/edit',
          component: EditorMenu,
          disabled: !props.editable
        }
      ]}
    />
  </MenuSection>

QuizMenu.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  QuizMenu
}
