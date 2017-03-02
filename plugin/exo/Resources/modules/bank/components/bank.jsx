import React, {PropTypes as T} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'

import {tex, transChoice} from './../../utils/translate'
import {makeModal} from './../../modal'
import {showModal, fadeModal} from './../../modal/actions'
import {select} from './../selectors'
import {actions as paginationActions} from './../actions/pagination'
import {actions as searchActions} from './../actions/search'
import {select as paginationSelect} from './../selectors/pagination'

import PageHeader from './../../components/layout/page-header.jsx'
import PageActions from './../../components/layout/page-actions.jsx'
import VisibleQuestions from './../containers/visible-questions.jsx'
import {Pagination} from './pagination/pagination.jsx'

import {MODAL_SEARCH} from './modal/search.jsx'
// TODO : do not load from editor
import {MODAL_ADD_ITEM} from './../../quiz/editor/components/add-item-modal.jsx'

const Bank = (props) => {
  const actions = [
    {
      icon: 'fa fa-fw fa-search',
      label: tex('search'),
      handleAction: () => props.openSearchModal(props.searchFilters),
      badge: (
      <OverlayTrigger
        placement="top"
        overlay={<Tooltip id="activeFiltersCount">{transChoice('active_filters', props.activeFilters, {count: props.activeFilters}, 'ujm_exo')}</Tooltip>}
      >
        <small className={classes('label', 0 < props.activeFilters ? 'label-primary' : 'label-default')}>{props.activeFilters}</small>
      </OverlayTrigger>
      ),
      primary: true
    }
  ]

  return (
    <div className="page-container">
      <PageHeader
        title={<span>{tex('questions_bank')} <small>{props.totalResults}&nbsp;questions</small></span>}
      >
        <PageActions actions={actions} />
      </PageHeader>

      {props.modal.type &&
        props.createModal(
          props.modal.type,
          props.modal.props,
          props.modal.fading
        )
      }

      {0 === props.totalResults &&
        <div className="empty-list">No results found.</div>
      }

      {0 < props.totalResults &&
        <VisibleQuestions />
      }

      {0 < props.totalResults &&
        <Pagination
          current={props.pagination.current}
          pageSize={props.pagination.pageSize}
          pages={props.pages}
          handlePageChange={props.handlePageChange}
          handlePagePrevious={props.handlePagePrevious}
          handlePageNext={props.handlePageNext}
          handlePageSizeUpdate={props.handlePageSizeUpdate}
        />
      }
    </div>
  )
}

Bank.propTypes = {
  totalResults: T.number.isRequired,
  searchFilters: T.object.isRequired,
  activeFilters: T.number.isRequired,
  modal: T.shape({
    type: T.string,
    fading: T.bool.isRequired,
    props: T.object.isRequired
  }),
  pages: T.number.isRequired,
  pagination: T.shape({
    current: T.number.isRequired,
    pageSize: T.number.isRequired
  }),
  createModal: T.func.isRequired,
  openSearchModal: T.func.isRequired,
  openAddModal: T.func.isRequired,
  handlePageChange: T.func.isRequired,
  handlePagePrevious: T.func.isRequired,
  handlePageNext: T.func.isRequired,
  handlePageSizeUpdate: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    searchFilters: select.filters(state),
    activeFilters: select.countFilters(state),
    modal: select.modal(state),
    totalResults: paginationSelect.getTotalResults(state),
    pagination: paginationSelect.getPagination(state),
    pages: paginationSelect.countPages(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createModal: (type, props, fading) => makeModal(type, props, fading, dispatch),
    openSearchModal(searchFilters) {
      dispatch(showModal(MODAL_SEARCH, {
        title: tex('search'),
        filters: searchFilters,
        handleSearch: (searchFilters) => dispatch(searchActions.search(searchFilters)),
        clearFilters: () => dispatch(searchActions.clearFilters())
      }))
    },
    openAddModal() {
      dispatch(showModal(MODAL_ADD_ITEM, {
        title: tex('add_question_from_new'),
        handleSelect: () => dispatch(fadeModal())
      }))
    },
    handlePagePrevious() {
      dispatch(paginationActions.previousPage())
    },
    handlePageNext() {
      dispatch(paginationActions.nextPage())
    },
    handlePageChange(page) {
      dispatch(paginationActions.changePage(page))
    },
    handlePageSizeUpdate(pageSize) {
      dispatch(paginationActions.updatePageSize(pageSize))
    }
  }
}

const ConnectedBank = connect(mapStateToProps, mapDispatchToProps)(Bank)

export {ConnectedBank as Bank}
