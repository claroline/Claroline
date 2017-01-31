import { connect } from 'react-redux'

import {tex, transChoice} from './../../utils/translate'
import QuestionList from './../components/question-list.jsx'
import {getVisibleQuestions} from './../selectors/questions'
import {actions as sortActions} from './../actions/sort-by'
import {actions as questionsActions} from './../actions/questions'
import {actions as selectActions} from './../actions/select'
import {showModal, fadeModal} from './../../modal/actions'
import {MODAL_DELETE_CONFIRM} from './../../modal'
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
      dispatch(showModal(MODAL_SHARE, {
        title: transChoice('share_items', items.length, {count: items.length}, 'ujm_exo'),
        fadeModal: fadeModal,
        handleShare: (users, adminRights) => {
          dispatch(fadeModal())
          dispatch(questionsActions.shareQuestions(items, users, adminRights))
        }
      }))
    },

    onDelete: (items) => {
      dispatch(showModal(MODAL_DELETE_CONFIRM, {
        title: transChoice('delete_items', items.length, {count: items.length}, 'ujm_exo'),
        question: tex('remove_question_confirm_message'),
        handleConfirm: () => dispatch(questionsActions.deleteQuestions(items))
      }))
    }
  }
}

const VisibleQuestions = connect(
  mapStateToProps,
  mapDispatchToProps
)(QuestionList)

export default VisibleQuestions
