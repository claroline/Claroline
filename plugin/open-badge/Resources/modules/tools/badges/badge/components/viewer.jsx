import React from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/app/intl/translation'

import {actions}    from '#/plugin/open-badge/tools/badges/store/actions'

import {MODAL_USERS} from '#/main/core/modals/users'
import {CALLBACK_BUTTON, MODAL_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ListData} from '#/main/app/content/list/containers/data'
import {FormSection} from '#/main/app/content/form/components/sections'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'
import {UserCard} from '#/main/core/user/components/card'

import {
  selectors as formSelect
} from '#/main/app/content/form/store'

// TODO : add tools
const BadgeViewerComponent = (props) => {
  return (
    <div>
      <BadgeCard
        data={props.badge}
        size="sm"
        orientation="col"
      />

      {props.badge.assignable &&
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('users')}
          actions={[{
            displayed: props.badge.assignable,
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            modal: [MODAL_USERS, {
              url: ['apiv2_user_list_registerable'], // maybe not the correct URL
              title: props.title,
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('select', {}, 'actions'),
                callback: () => props.addUsers(props.badge.id, selected)
              })
            }]
          }]}
        >
          {props.badge.meta && props.badge.meta.enabled ?
            <ListData
              name={selectors.STORE_NAME + '.badges.current.assertions'}
              fetch={{
                url: ['apiv2_badge-class_assertion', {badge: props.badge.id}],
                autoload: props.badge.id && !props.new
              }}
              primaryAction={(row) => ({
                type: LINK_BUTTON,
                target: props.path + `/badges/${props.badge.id}/assertion/${row.id}`,
                label: trans('', {}, 'actions')
              })}
              delete={{
                url: ['apiv2_badge-class_remove_users', {badge: props.badge.id}]
              }}
              definition={[
                {
                  name: 'user.username',
                  type: 'username',
                  label: trans('username'),
                  displayed: true,
                  primary: true
                }, {
                  name: 'user.lastName',
                  type: 'string',
                  label: trans('last_name'),
                  displayed: true
                }, {
                  name: 'user.firstName',
                  type: 'string',
                  label: trans('first_name'),
                  displayed: true
                }, {
                  name: 'user.email',
                  type: 'email',
                  label: trans('email'),
                  displayed: true
                }
              ]}
              card={UserCard}
            />:
            <div>{trans('badge_must_be_enabled or assignable')}</div>
          }
        </FormSection>
      }
    </div>
  )
}

const BadgeViewer = connect(
  (state) => ({
    currentContext: toolSelectors.context(state),
    path: toolSelectors.path(state),
    badge: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.badges.current'))
  }),
  (dispatch) =>({
    addUsers(badgeId, selected) {
      dispatch(actions.addUsers(badgeId, selected))
    }
  })
)(BadgeViewerComponent)

export {
  BadgeViewer
}
