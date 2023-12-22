import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Badge as BadgeTypes} from '#/plugin/open-badge//prop-types'
import {BadgePage} from '#/plugin/open-badge/badge/components/page'
import {BadgeDetails} from '#/plugin/open-badge/badge/components/details'

const BadgeShow = (props) =>
  <BadgePage
    path={props.path}
    badge={props.badge}
    reload={props.reload}
  >
    {props.badge &&
      <BadgeDetails
        path={props.path}
        badge={props.badge}
      />
    }
  </BadgePage>

BadgeShow.propTypes = {
  path: T.string.isRequired,
  badge: T.shape(
    BadgeTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  BadgeShow
}
