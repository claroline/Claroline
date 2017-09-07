import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {tex, transChoice} from '#/main/core/translation'
import {makeModal} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import { Page, PageHeader, PageContent} from '#/main/core/layout/page/components/page.jsx'
import { PageActions, PageAction } from '#/main/core/layout/page/components/page-actions.jsx'
import { Pagination } from '#/main/core/layout/pagination/components/pagination.jsx'

import {select} from './../selectors'
import {actions as paginationActions} from './../actions/pagination'
import {actions as searchActions} from './../actions/search'
import {select as paginationSelect} from './../selectors/pagination'

import VisibleQuestions from './../containers/visible-questions.jsx'

import {MODAL_SEARCH} from './modal/search.jsx'
import {MODAL_ADD_ITEM} from './../../quiz/editor/components/modal/add-item-modal.jsx'

// TODO : do not load add item modal from editor
// TODO : finish to refactor modals for using the ones embed in <Page> component

const Bank = props =>
  <Page
    modal={props.modal}
    fadeModal={props.fadeModal}
    hideModal={props.hideModal}
  >
    <PageHeader
      title={tex('questions_bank')}
    >
      <PageActions>
        <PageAction
          id="bank-search"
          title={transChoice('active_filters', props.activeFilters, {count: props.activeFilters}, 'ujm_exo')}
          icon="fa fa-search"
          action={() => props.openSearchModal(props.searchFilters)}
        >
            <span className={classes('label', 0 < props.activeFilters ? 'label-primary' : 'label-default')}>
              {props.activeFilters}
            </span>
        </PageAction>
      </PageActions>
    </PageHeader>

    {props.modal.type &&
      props.createModal(
        props.modal.type,
        props.modal.props,
        props.modal.fading
      )
    }

    <PageContent>
      {0 === props.totalResults &&
      <div className="list-empty">No results found.</div>
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
    </PageContent>
  </Page>

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
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
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
    fadeModal() {
      dispatch(modalActions.fadeModal())
    },
    hideModal() {
      dispatch(modalActions.hideModal())
    },
    openSearchModal(searchFilters) {
      dispatch(modalActions.showModal(MODAL_SEARCH, {
        title: tex('search'),
        filters: searchFilters,
        handleSearch: (searchFilters) => dispatch(searchActions.search(searchFilters)),
        clearFilters: () => dispatch(searchActions.clearFilters()),
        fadeModal: () => dispatch(modalActions.fadeModal())
      }))
    },
    openAddModal() {
      dispatch(modalActions.showModal(MODAL_ADD_ITEM, {
        title: tex('add_question_from_new'),
        handleSelect: () => dispatch(modalActions.fadeModal())
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
