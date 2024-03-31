import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Badge as BadgeTypes} from '#/plugin/open-badge//prop-types'
import {BadgePage} from '#/plugin/open-badge/badge/components/page'
import {BadgeForm} from '#/plugin/open-badge/badge/components/form'

import {selectors} from '#/plugin/open-badge/tools/badges/store/selectors'

const BadgeEdit = (props) =>
  <BadgePage
    path={props.path}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('edition'),
        target: '' // current page, link is not needed
      }
    ]}
    badge={props.badge}
    reload={props.reload}
  >
    <BadgeForm
      name={selectors.FORM_NAME}
    />
  </BadgePage>

BadgeEdit.propTypes = {
  path: T.string.isRequired,
  badge: T.shape(
    BadgeTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  BadgeEdit
}
