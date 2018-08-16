import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {withRouter} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {actions, selectors} from '#/plugin/announcement/resources/announcement/store'
import {MODAL_ANNOUNCEMENT_SENDING_CONFIRM} from '#/plugin/announcement/resources/announcement/modals'

const AnnounceSendComponent = props =>
  <FormData
    name="announcementForm"
    level={2}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-paper-plane-o',
      label: trans('send', {}, 'actions'),
      disabled: parseInt(props.announcement.meta.notifyUsers) === 0,
      callback: () => {
        props.send(props.aggregateId, props.announcement)
        props.history.push('/')
      }
    }}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'meta.notifyUsers',
            type: 'choice',
            label: trans('announcement_notify_users', {}, 'announcement'),
            options: {
              choices: {
                0: trans('do_not_send', {}, 'announcement'),
                1: trans('send_directly', {}, 'announcement')
              }
            },
            linked: [
              {
                name: 'roles',
                label: trans('roles_to_send_to', {}, 'announcement'),
                type: 'choice',
                displayed: (announcement) => 0 !== parseInt(announcement.meta.notifyUsers),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: props.workspaceRoles.reduce((acc, current) => {
                    acc[current.id] = trans(current.translationKey)

                    return acc
                  }, {})
                }
              }
            ]
          }
        ]
      }
    ]}
  />

AnnounceSendComponent.propTypes = {
  aggregateId: T.string.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  send: T.func.isRequired,
  workspaceRoles: T.arrayOf(T.shape({
    id: T.string.isRequired,
    translationKey: T.string.isRequired
  }))
}

AnnounceSendComponent.defaultProps = {
  announcement: AnnouncementTypes.defaultProps
}

const RoutedAnnounceSend = withRouter(AnnounceSendComponent)

const AnnounceSend = connect(
  (state) => ({
    announcement: formSelectors.data(formSelectors.form(state, 'announcementForm')),
    aggregateId: selectors.aggregateId(state),
    workspaceRoles: selectors.workspaceRoles(state)
  }),
  (dispatch) => ({
    send(aggregateId, announce) {
      dispatch(listActions.addFilter('selected.list', 'roles', announce.roles))
      dispatch(
        modalActions.showModal(MODAL_ANNOUNCEMENT_SENDING_CONFIRM, {
          filters: {roles: announce.roles},
          aggregateId: aggregateId,
          announcementId: announce.id,
          handleConfirm: () => {
            dispatch(actions.sendAnnounce(aggregateId, announce))
          }
        })
      )
    }
  })
)(RoutedAnnounceSend)

export {
  AnnounceSend
}
