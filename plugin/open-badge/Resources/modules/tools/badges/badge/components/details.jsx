import React, {Fragment} from 'react'
import {connect} from 'react-redux'
import get from 'lodash/get'
import {trans} from '#/main/app/intl/translation'

import {CALLBACK_BUTTON, MODAL_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSection} from '#/main/app/content/form/components/sections'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {UserCard} from '#/main/core/user/components/card'
import {MODAL_USERS} from '#/main/core/modals/users'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'
import {actions, selectors}  from '#/plugin/open-badge/tools/badges/store'

import {selectors as formSelectors} from '#/main/app/content/form/store'

// TODO : add tools
const BadgeDetailsComponent = (props) =>
  <Fragment>
    <div className="badge-meta">

    </div>

    <BadgeCard
      data={props.badge}
      size="sm"
      orientation="col"
    />

    {get(props.badge, 'permissions.assign') &&
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        actions={[{
          displayed: get(props.badge, 'permissions.assign'),
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
        {get(props.badge, 'meta.enabled ') ?
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
  </Fragment>

const BadgeDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    badge: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.badges.current'))
  }),
  (dispatch) =>({
    addUsers(badgeId, selected) {
      dispatch(actions.addUsers(badgeId, selected))
    }
  })
)(BadgeDetailsComponent)

export {
  BadgeDetails
}
