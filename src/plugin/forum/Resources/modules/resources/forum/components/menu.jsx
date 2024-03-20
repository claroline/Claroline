import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ResourceMenu} from '#/main/core/resource'

const ForumMenu = (props) =>
  <ResourceMenu
    overview={props.overview}
    actions={[
      {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-list-ul',
        label: trans('subjects', {}, 'forum'),
        target: `${props.path}/subjects`
      },  {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-flag',
        label: trans('moderation', {}, 'forum'),
        displayed: props.moderator,
        target: `${props.path}/moderation`
      }
    ]}
  />

ForumMenu.propTypes = {
  path: T.string.isRequired,
  overview: T.bool.isRequired,
  moderator: T.bool.isRequired
}

export {
  ForumMenu
}
