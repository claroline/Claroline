import {connect} from 'react-redux'

import {trans, transChoice} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {BankTool as BankToolComponent} from '#/plugin/exo/tools/bank/components/tool'
import {actions} from '#/plugin/exo/tools/bank/store'
import {MODAL_ITEM_SHARING} from '#/plugin/exo/items/modals/sharing'

const BankTool = connect(
  null,
  (dispatch) => ({
    removeQuestions(questions) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: transChoice('delete_items', questions.length, {count: questions.length}, 'quiz'),
          question: trans('remove_questions_confirm', {
            question_list: questions.map(question => question.title || question.content.substr(0, 40)).join(', ')
          }, 'quiz'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.removeQuestions(questions))
        })
      )
    },

    duplicateQuestions(questions) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: transChoice('copy_questions', questions.length, {count: questions.length}, 'quiz'),
          question: trans('copy_questions_confirm', {
            workspace_list: questions.map(question => question.title || question.content.substr(0, 40)).join(', ')
          }, 'quiz'),
          handleConfirm: () => dispatch(actions.duplicateQuestions(questions))
        })
      )
    },

    shareQuestions(questions) {
      dispatch(modalActions.showModal(MODAL_ITEM_SHARING, {
        title: transChoice('share_items', questions.length, {count: questions.length}, 'quiz'),
        handleShare: (users, adminRights) => {
          dispatch(modalActions.fadeModal())
          dispatch(actions.shareQuestions(questions, users, adminRights))
        }
      }))
    }
  })
)(BankToolComponent)

export {
  BankTool
}
