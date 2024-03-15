import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourceMenu} from '#/main/core/resource/containers/menu'

const LessonMenu = props =>
  <ResourceMenu
    overview={props.overview}
    actions={[
      {
        name: 'summary',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-sitemap',
        label: trans('summary'),
        target: `${props.path}/summary`
      }
    ]}
  />

LessonMenu.propTypes = {
  path: T.string.isRequired,
  overview: T.bool.isRequired
}

export {
  LessonMenu
}
