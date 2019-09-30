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
import {actions} from '#/plugin/open-badge/tools/badges/badge/store/actions'

const BadgesList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
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
      }
    ]}
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
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        scope: ['object', 'collection'],
        target: props.path + `/badges/${rows[0].id}/form`,
        displayed: 0 < rows.filter(b => b.permissions.edit).length,
        group: trans('management')
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check-circle',
        label: trans('enable', {}, 'actions'),
        scope: ['object', 'collection'],
        displayed: props.isAdmin && 0 < rows.filter(b => !b.meta.enabled).length,
        callback: () => props.enable(rows),
        group: trans('management')
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-times-circle',
        label: trans('disable', {}, 'actions'),
        scope: ['object', 'collection'],
        displayed: props.isAdmin && 0 < rows.filter(b => b.meta.enabled).length,
        callback: () => props.disable(rows),
        confirm: {
          title: transChoice('disable_badges', rows.length, {count: rows.length}),
          message: trans('disable_badges_confirm', {users_list: rows.map(b => `${b.name}`).join(', ')})
        },
        group: trans('management')
      }
    ]}

    display={{current: listConstants.DISPLAY_LIST_SM}}
  />

BadgesList.propTypes = {
  path: T.string.isRequired,
  isAdmin: T.bool.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  disable: T.func.isRequired,
  enable: T.func.isRequired
}

const Badges = connect(
  (state) => ({
    isAdmin: securitySelectors.isAdmin(state),
    path: toolSelectors.path(state)
  }),
  dispatch => ({
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
