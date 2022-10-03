import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/plugin/exo/tools/bank/store/selectors'
import {ItemList} from '#/plugin/exo/items/components/list'
import {MODAL_ITEM_SHARING} from '#/plugin/exo/items/modals/sharing'

const BankTool = props =>
  <ToolPage>
    <ItemList
      name={selectors.LIST_QUESTIONS}
      actions={(rows) => [
        /*{
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-copy',
          label: trans('duplicate', {}, 'actions'),
          callback: (rows) => props.duplicateQuestions(rows, false),
          confirm: {
            title: transChoice('copy_questions', rows.length, {count: rows.length}, 'quiz'),
            message: trans('copy_questions_confirm', {
              workspace_list: rows.map(question => question.title || question.content.substr(0, 40)).join(', ')
            }, 'quiz')
          }
         }, */{
          // TODO : checks if the current user has the rights to share to enable the action
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-share',
          label: trans('share', {}, 'actions'),
          modal: [MODAL_ITEM_SHARING, {
            title: transChoice('share_items', rows.length, {count: rows.length}, 'quiz'),
            handleShare: (users, adminRights) => props.shareQuestions(rows, users, adminRights)
          }]
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          callback: () => props.removeQuestions(rows),
          confirm: {
            title: transChoice('delete_items', rows.length, {count: rows.length}, 'quiz'),
            message: trans('remove_questions_confirm', {
              question_list: rows.map(question => question.title || question.content.substr(0, 40)).join(', ')
            }, 'quiz')
          },
          dangerous: true
        }
      ]}
    />
  </ToolPage>

BankTool.propTypes = {
  removeQuestions: T.func.isRequired,
  duplicateQuestions: T.func.isRequired,
  shareQuestions: T.func.isRequired
}

export {
  BankTool
}
