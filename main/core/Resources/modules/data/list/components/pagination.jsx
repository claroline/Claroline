import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import times from 'lodash/times'
import {DropdownButton, MenuItem} from 'react-bootstrap'

import {t, transChoice} from '#/main/core/translation'
import {constants} from '#/main/core/data/list/constants'
import {countPages} from '#/main/core/data/list/utils'

const ResultsPerPage = props =>
  <div className="results-per-page">
    <DropdownButton
      id="page-sizes-dropdown"
      title={
        <span>
          {-1 !== props.pageSize ? props.pageSize: t('all')}
          <span className="fa fa-sort" />
        </span>
      }
      bsStyle="link"
      noCaret={true}
      dropup={true}
      pullRight={true}
    >
      <MenuItem header>{t('results_per_page')}</MenuItem>
      {props.availableSizes.map((size, index) =>
        <MenuItem
          key={index}
          onClick={() => props.pageSize !== size ? props.updatePageSize(size) : false}
          className={props.pageSize === size ? 'active' : ''}
        >
          {transChoice('list_results_count', size, {count: size}, 'platform')}
        </MenuItem>
      )}
    </DropdownButton>
  </div>

ResultsPerPage.propTypes = {
  totalResults: T.number.isRequired,
  availableSizes: T.arrayOf(T.number),
  pageSize: T.number,
  updatePageSize: T.func.isRequired
}

ResultsPerPage.defaultProps = {
  availableSizes: constants.AVAILABLE_PAGE_SIZES,
  pageSize: constants.DEFAULT_PAGE_SIZE
}

const PaginationLink = props =>
  <button
    type="button"
    className={classes('btn btn-link', props.className, {
      disabled: props.disabled
    })}
    onClick={(e) => {
      e.stopPropagation()
      if (!props.disabled) {
        props.handleClick()
      }
    }}
  >
    {props.children}
  </button>

PaginationLink.propTypes = {
  disabled: T.bool,
  handleClick: T.func.isRequired,
  className: T.node,
  children: T.node.isRequired
}

PaginationLink.defaultProps = {
  disabled: false,
  className: ''
}

const PreviousLink = props =>
  <PaginationLink
    className="btn-previous"
    disabled={props.disabled}
    handleClick={props.previousPage}
  >
    <span className="fa fa-angle-double-left" aria-hidden="true" />
    <span className="sr-only">{t('previous')}</span>
  </PaginationLink>

PreviousLink.propTypes = {
  disabled: T.bool,
  previousPage: T.func.isRequired
}

PreviousLink.defaultProps = {
  disabled: false
}

const NextLink = props =>
  <PaginationLink
    className="btn-next"
    disabled={props.disabled}
    handleClick={props.nextPage}
  >
    {t('next')}
    <span className="fa fa-angle-double-right" aria-hidden="true" />
  </PaginationLink>

NextLink.propTypes = {
  disabled: T.bool,
  nextPage: T.func.isRequired
}

NextLink.defaultProps = {
  disabled: false
}

const Pagination = props => {
  const pages = countPages(props.totalResults, props.pageSize)

  return (
    <nav className="pagination-container page-nav">
      <div className="pagination-condensed btn-group">
        <PreviousLink
          disabled={0 === props.current}
          previousPage={() => props.changePage(props.current - 1)}
        />

        <DropdownButton
          id="pagination-pages-dropdown"
          title={t('current_page', {current: props.current + 1, pages: pages})}
          bsStyle="link"
          noCaret={true}
          dropup={true}
          disabled={1 === pages}
        >
          <MenuItem header>{t('pages')}</MenuItem>
          {times(pages, (page) =>
            <MenuItem
              key={`page-${page}`}
              onClick={() => page !== props.current ? props.changePage(page) : false}
              className={classes({
                active: page === props.current
              })}
            >
              Page {page + 1}
            </MenuItem>
          )}
        </DropdownButton>

        <NextLink
          disabled={pages - 1 === props.current}
          nextPage={() => props.changePage(props.current + 1)}
        />
      </div>

      <ResultsPerPage
        pageSize={props.pageSize}
        totalResults={props.totalResults}
        updatePageSize={props.updatePageSize}
      />
    </nav>
  )
}

Pagination.propTypes = {
  current: T.number,
  pageSize: T.number,
  totalResults: T.number.isRequired,
  changePage: T.func.isRequired,
  updatePageSize: T.func.isRequired
}

Pagination.defaultProps = {
  current: 0
}

export {
  Pagination
}
