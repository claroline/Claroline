import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {actions} from '#/plugin/open-badge/tools/badges/badge/store/actions'
import {transChoice} from '#/main/app/intl/translation'
import {isAdmin as userIsAdmin} from '#/main/app/security/permissions'
import {currentUser} from '#/main/app/security'


import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'
import {BadgeList} from '#/plugin/open-badge/tools/badges/badge/components/definition'

const BadgesList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    definition={BadgeList.definition}
    card={BadgeCard}

    primaryAction={(row) => ({
      label: trans('open'),
      type: LINK_BUTTON,
      target: props.path + `/badges/${row.id}`
    })}
    delete={{
      url: ['apiv2_badge-class_delete_bulk'],
      displayed: (rows) => 0 < (rows.filter(b => b.permissions.delete).length)
    }}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pen',
        label: trans('edit'),
        scope: ['object', 'collection'],
        target: props.path + `/badges/${rows[0].id}/form`,
        displayed: 0 < (rows.filter(b => b.permissions.edit).length)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check-circle',
        label: trans('enable'),
        scope: ['object', 'collection'],
        displayed: 0 < (rows.filter(b => !b.meta.enabled).length) && userIsAdmin(currentUser()),
        callback: () => props.enable(rows)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-times-circle',
        label: trans('disable'),
        scope: ['object', 'collection'],
        displayed: 0 < (rows.filter(b => b.meta.enabled).length) && userIsAdmin(currentUser()),
        callback: () => props.disable(rows),
        dangerous: true
      }
    ]}

    display={{current: listConstants.DISPLAY_LIST_SM}}
  />

BadgesList.propTypes = {
  currentUser: T.object,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  disable: T.func.isRequired,
  enable: T.func.isRequired,
  currentContext: T.object.isRequired,
  path: T.string.isRequired
}

const Badges = connect(
  (state) => ({
    currentContext: toolSelectors.context(state),
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    enable(badges) {
      dispatch(actions.enable(badges))
    },
    disable(badges) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-times-circle',
          title: transChoice('disable_badges', badges.length, {count: badges.length}),
          question: trans('disable_badges_confirm', {users_list: badges.map(b => `${b.name}`).join(', ')}),
          dangerous: true,
          handleConfirm: () => dispatch(actions.disable(badges))
        })
      )
    }
  })
)(BadgesList)

export {
  Badges
}
