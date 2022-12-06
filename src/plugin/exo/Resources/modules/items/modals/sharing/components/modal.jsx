import React, {createElement, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {ConfirmModal} from '#/main/app/modals/confirm/components/modal'
import {UserCard} from '#/main/community/user/components/card'

const SharingModal = (props) => {
  const [shareAdminRights, setShareAdminRights] = useState(false)

  return (
    <ConfirmModal
      {...omit(props, 'questions', 'users', 'handleShare')}
      icon="fa fa-fw fa-share"
      title={transChoice('share_items', props.questions.length, {count: props.questions.length}, 'quiz')}
      question={transChoice('share_items_confirm_message', props.questions.length, {count: props.questions.length}, 'quiz')}
      additional={[
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, props.users.map(user => createElement(UserCard, {
          key: user.id,
          orientation: 'row',
          size: 'xs',
          data: user
        })).concat([
          createElement(Checkbox, {
            id: 'share-items',
            key: 'share-items',
            label: trans('share_admin_rights', {}, 'quiz'),
            checked: shareAdminRights,
            onChange: setShareAdminRights
          })
        ]))
      ]}
      confirmAction={{
        type: CALLBACK_BUTTON,
        label: trans('share', {}, 'actions'),
        callback: () => {
          props.handleShare(shareAdminRights)
          props.fadeModal()
        }
      }}
    />
  )
}

SharingModal.propTypes = {
  users: T.array,
  questions: T.array,
  fadeModal: T.func.isRequired,
  handleShare: T.func.isRequired
}

export {
  SharingModal
}
