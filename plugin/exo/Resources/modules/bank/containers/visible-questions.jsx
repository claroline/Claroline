import { connect } from 'react-redux'

import {tex, transChoice} from '#/main/core/translation'
import QuestionList from './../components/question-list.jsx'
import {getVisibleQuestions} from './../selectors/questions'
import {actions as sortActions} from './../actions/sort-by'
import {actions as questionsActions} from './../actions/questions'
import {actions as selectActions} from './../actions/select'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {MODAL_SHARE} from './../components/modal/share.jsx'
import {select} from './../selectors'

const mapStateToProps = (state) => {
  return {
    questions: getVisibleQuestions(state),
    selected: select.selected(state),
    sortBy: state.sortBy
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    /**
     * Update sort order.
     *
     * @param property
     */
    onSort: (property) => {
      dispatch(sortActions.updateSortBy(property))
    },

    toggleSelectPage: () => {

    },

    toggleSelectAll: () => {

    },

    toggleSelect: (item) => {
      dispatch(selectActions.toggleSelect(item.id))
    },

    onShare: (items) => {
      dispatch(modalActions.showModal(MODAL_SHARE, {
        title: transChoice('share_items', items.length, {count: items.length}, 'ujm_exo'),
        fadeModal: () => dispatch(modalActions.fadeModal()),
        handleShare: (users, adminRights) => {
          dispatch(modalActions.fadeModal())
          dispatch(questionsActions.shareQuestions(items, users, adminRights))
        }
      }))
    },

    onDelete: (items) => {
      dispatch(modalActions.showModal(MODAL_DELETE_CONFIRM, {
        title: transChoice('delete_items', items.length, {count: items.length}, 'ujm_exo'),
        question: tex('remove_question_confirm_message'),
        handleConfirm: () => dispatch(questionsActions.deleteQuestions(items)),
        fadeModal: () => dispatch(modalActions.fadeModal())
      }))
    }
  }
}

const VisibleQuestions = connect(
  mapStateToProps,
  mapDispatchToProps
)(QuestionList)

export default VisibleQuestions
