import React from 'react'
import {connect} from 'react-redux'
import {BadgeList} from '#/plugin/open-badge/tools/badges/badge/components/badge-list'
import {ListData} from '#/main/app/content/list/containers/data'

import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {actions} from '#/plugin/open-badge/tools/badges/badge/store/actions'

import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'
import {constants as listConstants} from '#/main/app/content/list/constants'
// todo : restore custom actions the same way resource actions are implemented
const BadgesList = props =>
  <ListData
    name={selectors.STORE_NAME +'.badges.list'}
    fetch={{
      url: props.currentContext === 'workspace' ? ['apiv2_badge-class_workspace_badge_list', {workspace: props.workspace.uuid}]: ['apiv2_badge-class_list'],
      autoload: true
    }}
    definition={BadgeList.definition}
    primaryAction={BadgeList.open}
    delete={{
      url: ['apiv2_badge-class_delete_bulk'],
      displayed: () => props.currentContext !== 'desktop',
      disabled: () => props.currentContext === 'desktop'
    }}
    actions={(rows) => [
      {
        displayed: props.currentContext !== 'desktop',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pen',
        label: trans('edit'),
        target: props.path + `/badges/form/${rows[0].id}`,
        scope: ['object']
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check-circle',
        label: trans('enable'),
        scope: ['object', 'collection'],
        displayed: 0 < (rows.filter(b => !b.meta.enabled).length) && props.currentContext === 'administration',
        callback: () => props.enable(rows)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-times-circle',
        label: trans('disable'),
        scope: ['object', 'collection'],
        displayed: 0 < (rows.filter(b => b.meta.enabled).length) && props.currentContext === 'administration',
        callback: () => props.disable(rows),
        dangerous: true
      }
    ]}
    card={BadgeList.card}
    display={{current: listConstants.DISPLAY_LIST_SM}}
  />

const Badges = connect(
  (state, ownProps) => ({
    currentContext: state.currentContext,
    workspace: state.workspace,
    path: ownProps.path
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
