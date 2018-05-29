import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {withRouter, matchPath} from '#/main/app/router'

import {hasPermission} from '#/main/core/user/permissions'

import {MODAL_USER_PASSWORD, MODAL_USER_PUBLIC_URL, MODAL_USER_MESSAGE} from '#/main/core/user/modals'
import {
  PageGroupActions,
  PageActions,
  PageAction,
  MoreAction
} from '#/main/core/layout/page'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

const EditGroupActionsComponent = props =>
  <PageGroupActions>
    <FormPageActionsContainer
      formName="user"
      opened={!!matchPath(props.location.pathname, {path: '/edit'})}
      target={(user) => ['apiv2_user_update', {id: user.id}]}
      open={{
        type: 'link',
        label: trans('edit_profile'),
        target: '/edit'
      }}
      cancel={{
        type: 'link',
        target: '/show',
        exact: true
      }}
    />
  </PageGroupActions>

EditGroupActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const EditGroupActions = withRouter(EditGroupActionsComponent)

const UserPageActions = props => {
  const moreActions = [].concat(props.customActions, [
    {
      type: 'modal',
      icon: 'fa fa-fw fa-lock',
      label: trans('change_password'),
      group: trans('user_management'),
      displayed: hasPermission('edit', props.user),
      modal: [MODAL_USER_PASSWORD, {
        changePassword: (password) => props.updatePassword(props.user, password)
      }]
    }, {
      type: 'modal',
      icon: 'fa fa-fw fa-link',
      label: trans('change_profile_public_url'),
      group: trans('user_management'),
      displayed: hasPermission('edit', props.user),
      disabled: props.user.meta.publicUrlTuned,
      modal: [MODAL_USER_PUBLIC_URL, {
        url: props.user.meta.publicUrl,
        changeUrl: (publicUrl) => props.updatePublicUrl(props.user, publicUrl)
      }]
    }, {
      type: 'url',
      icon: 'fa fa-fw fa-line-chart',
      label: trans('show_tracking'),
      group: trans('user_management'),
      displayed: hasPermission('edit', props.user),
      target: ['claro_user_tracking', {publicUrl: props.user.meta.publicUrl}]
    }, {
      type: 'async',
      icon: 'fa fa-fw fa-trash-o',
      label: trans('delete', {}, 'actions'),
      displayed: hasPermission('delete', props.user),
      request: {
        type: 'delete',
        url: ['apiv2_user_delete_bulk', {ids: [props.user.id]}],
        request: {
          method: 'DELETE'
        }
        //success: () => window.location = url(['claro_desktop_open']) todo redirect
      },
      dangerous: true,
      confirm: {
        title: trans('user_delete_confirm_title'),
        message: trans('user_delete_confirm_message')
      }
    }
  ])

  return (
    <PageActions>
      {(props.user.rights.current.edit || props.canEditProfile) &&
        <EditGroupActions />
      }

      {props.user.rights.current.contact &&
        <PageGroupActions>
          <PageAction
            id="send-message"
            type="modal"
            label={trans('send_message')}
            icon="fa fa-paper-plane-o"
            modal={[MODAL_USER_MESSAGE, {

            }]}
          />
          <PageAction
            id="add-contact"
            type="callback"
            label={trans('add_contact')}
            icon="fa fa-address-book-o"
            action={() => true}
          />
        </PageGroupActions>
      }

      {0 !== moreActions.length &&
        <PageGroupActions>
          <MoreAction
            menuLabel={trans('user')}
            actions={moreActions}
          />
        </PageGroupActions>
      }
    </PageActions>
  )
}

UserPageActions.propTypes = {
  user: T.shape({
    id: T.string.isRequired,
    meta: T.shape({
      publicUrl: T.string.isRequired,
      publicUrlTuned: T.bool
    }).isRequired,
    rights: T.shape({
      current: T.shape({
        contact: T.bool.isRequired,
        edit: T.bool.isRequired,
        delete: T.bool.isRequired
      }).isRequired
    }).isRequired
  }).isRequired,
  customActions: T.array,
  updatePassword: T.func.isRequired,
  updatePublicUrl: T.func.isRequired,
  canEditProfile: T.bool.isRequired
}

UserPageActions.defaultProps = {
  customActions: [],
  canEditProfile: false
}

export {
  UserPageActions
}
