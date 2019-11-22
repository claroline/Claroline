import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {Summary} from '#/main/app/content/components/summary'

import {normalizeTree} from '#/plugin/lesson/resources/lesson/utils'

const LessonMenu = props =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('icap_lesson', {}, 'resource')}
  >
    <Summary
      links={!isEmpty(props.tree) ? normalizeTree(props.tree, props.lesson.id, props.editable, props.path).children : []}
    />
  </MenuSection>

LessonMenu.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,
  lesson: T.object.isRequired,
  tree: T.any.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  LessonMenu
}