import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {withRouter, matchPath} from '#/main/core/router'

import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {MODAL_CHANGE_PASSWORD, MODAL_SEND_MESSAGE} from '#/main/core/user/modals'
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
        label: t('edit_profile'),
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
      type: 'callback',
      icon: 'fa fa-fw fa-lock',
      label: t('change_password'),
      group: t('user_management'),
      displayed: props.user.rights.current.edit,
      callback: () => props.showModal(MODAL_CHANGE_PASSWORD, {
        changePassword: (password) => props.updatePassword(props.user, password)
      })
    }, {
      type: 'url',
      icon: 'fa fa-fw fa-line-chart',
      label: t('show_tracking'),
      group: t('user_management'),
      displayed: props.user.rights.current.edit,
      target: ['claro_user_tracking', {publicUrl: props.user.meta.publicUrl}]
    }, {
      type: 'callback',
      icon: 'fa fa-fw fa-trash-o',
      label: t('delete'),
      displayed: props.user.rights.current.delete,
      callback: () =>  props.showModal(MODAL_DELETE_CONFIRM, {

      }),
      dangerous: true
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
            type="callback"
            label={t('send_message')}
            icon="fa fa-paper-plane-o"
            callback={() => props.showModal(MODAL_SEND_MESSAGE, {

            })}
          />
          <PageAction
            id="add-contact"
            type="callback"
            label={t('add_contact')}
            icon="fa fa-address-book-o"
            action={() => true}
          />
        </PageGroupActions>
      }

      {0 !== moreActions.length &&
        <PageGroupActions>
          <MoreAction
            menuLabel={t('user')}
            actions={moreActions}
          />
        </PageGroupActions>
      }
    </PageActions>
  )
}

UserPageActions.propTypes = {
  user: T.shape({
    meta: T.shape({
      publicUrl: T.string.isRequired
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
  showModal: T.func.isRequired,
  updatePassword: T.func.isRequired,
  canEditProfile: T.bool.isRequired
}

UserPageActions.defaultProps = {
  customActions: [],
  canEditProfile: false
}

export {
  UserPageActions
}
