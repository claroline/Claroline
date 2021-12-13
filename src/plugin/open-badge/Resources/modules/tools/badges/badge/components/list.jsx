import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans, transChoice} from '#/main/app/intl/translation'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'
import {actions, selectors} from '#/plugin/open-badge/tools/badges/badge/store'

const BadgesList = (props) =>
  <ListData
    name={selectors.LIST_NAME}
    fetch={{
      url: 'workspace' === props.currentContext.type ?
        ['apiv2_badge-class_workspace_badge_list', {workspace: props.currentContext.data.id}] :
        ['apiv2_badge-class_list'],
      autoload: true
    }}
    definition={[
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.enabled',
        label: trans('enabled'),
        type: 'boolean',
        displayed: true
      }, {
        name: 'assignable',
        label: trans('assignable', {}, 'badge'),
        type: 'boolean',
        displayed: false,
        displayable: false,
        filterable: true
      }, {
        name: 'workspace',
        label: trans('workspace'),
        type: 'workspace',
        displayed: true,
        filterable: true
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        sortable: false,
        options: {
          objectClass: 'Claroline\\OpenBadgeBundle\\Entity\\BadgeClass'
        }
      }
    ]}
    card={BadgeCard}

    primaryAction={(row) => ({
      label: trans('open', {}, 'actions'),
      type: LINK_BUTTON,
      target: props.path + `/badges/${row.id}`
    })}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        scope: ['object', 'collection'],
        target: props.path + `/badges/${rows[0].id}/edit`,
        displayed: 0 < rows.filter(b => b.permissions.edit).length,
        group: trans('management')
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check-circle',
        label: trans('enable', {}, 'actions'),
        scope: ['object', 'collection'],
        displayed: 0 < rows.filter(b => b.permissions.edit && !b.meta.enabled).length,
        callback: () => props.enable(rows),
        group: trans('management')
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-times-circle',
        label: trans('disable', {}, 'actions'),
        scope: ['object', 'collection'],
        displayed: 0 < rows.filter(b => b.permissions.edit && b.meta.enabled).length,
        callback: () => props.disable(rows),
        confirm: {
          title: transChoice('disable_badges', rows.length, {count: rows.length}),
          message: trans('disable_badges_confirm', {users_list: rows.map(b => `${b.name}`).join(', ')})
        },
        group: trans('management')
      }, {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete', {}, 'actions'),
        dangerous: true,
        displayed: 0 < rows.filter(b => b.permissions.delete).length,
        confirm: {
          title: trans('objects_delete_title'),
          message: transChoice('objects_delete_question', 1, {count: 1}),
          button: trans('delete', {}, 'actions')
        },
        callback: () => props.delete(rows),
        group: trans('management')
      }
    ]}

    display={{current: listConstants.DISPLAY_LIST_SM}}
  />

BadgesList.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  isAdmin: T.bool.isRequired,
  disable: T.func.isRequired,
  enable: T.func.isRequired,
  delete: T.func.isRequired
}

const Badges = connect(
  (state) => ({
    isAdmin: securitySelectors.isAdmin(state),
    currentContext: toolSelectors.context(state),
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    delete(badges) {
      dispatch(actions.delete(badges))
    },
    enable(badges) {
      dispatch(actions.enable(badges))
    },
    disable(badges) {
      dispatch(actions.disable(badges))
    }
  })
)(BadgesList)

export {
  Badges
}
