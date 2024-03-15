import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ResourceMenu} from '#/main/core/resource/containers/menu'
import {LINK_BUTTON} from '#/main/app/buttons'

const QuizMenu = props =>
  <ResourceMenu
    overview={props.hasOverview}
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

QuizMenu.propTypes = {
  path: T.string.isRequired,
  hasOverview: T.bool.isRequired
}

export {
  QuizMenu
}
