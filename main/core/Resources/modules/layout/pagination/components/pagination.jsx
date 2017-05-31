import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DEFAULT_PAGE_SIZE} from '#/main/core/layout/pagination/selectors'
import {countPages} from '#/main/core/layout/pagination/utils'
import {ResultsPerPage} from '#/main/core/layout/pagination/components/results-per-page.jsx'

const PaginationLink = props =>
  <li className={props.className}>
    <a
      href="#"
      onClick={props.handleClick}
    >
      {props.children}
    </a>
  </li>

PaginationLink.propTypes = {
  handleClick: T.func.isRequired,
  className: T.node,
  children: T.node.isRequired
}

PaginationLink.defaultProps = {
  className: ''
}

const PageLink = props =>
  <PaginationLink
    className={props.current ? 'active' : ''}
    handleClick={e => {
      e.stopPropagation()
      props.handlePageChange(props.page)
    }}
  >
    {props.page + 1}
  </PaginationLink>

PageLink.propTypes = {
  page: T.number.isRequired,
  handlePageChange: T.func.isRequired,
  current: T.bool
}

PageLink.defaultProps = {
  current: false
}

const PreviousLink = props =>
  <PaginationLink
    className={props.disabled ? 'disabled' : ''}
    handleClick={e => {
      e.stopPropagation()
      if (!props.disabled) {
        props.handlePagePrevious()
      }
    }}
  >
    <span aria-hidden="true">&laquo;</span>
    <span className="sr-only">Previous</span>
  </PaginationLink>

PreviousLink.propTypes = {
  disabled: T.bool,
  handlePagePrevious: T.func.isRequired
}

PreviousLink.defaultProps = {
  disabled: false
}

const NextLink = props =>
  <PaginationLink
    className={props.disabled ? 'disabled' : ''}
    handleClick={e => {
      e.stopPropagation()
      if (!props.disabled) {
        props.handlePageNext()
      }
    }}
  >
    <span aria-hidden="true">&raquo;</span>
    <span className="sr-only">Next</span>
  </PaginationLink>

NextLink.propTypes = {
  disabled: T.bool,
  handlePageNext: T.func.isRequired
}

NextLink.defaultProps = {
  disabled: false
}

const Pagination = props => {
  const pages = countPages(props.totalResults, props.pageSize)

  const PageLinks = []
  for (let i = 0; i < pages; i++) {
    PageLinks.push(
      <PageLink
        key={i}
        page={i}
        current={i === props.current}
        handlePageChange={props.handlePageChange}
      />
    )
  }

  return (
    <nav className="pagination-container page-nav" aria-label="Page navigation">
      <ResultsPerPage
        pageSize={props.pageSize}
        handlePageSizeUpdate={props.handlePageSizeUpdate}
      />

      {1 !== pages &&
        <ul className="pagination">
          <PreviousLink
            disabled={0 === props.current}
            handlePagePrevious={() => props.handlePageChange(props.current - 1)}
          />

          {PageLinks}

          <NextLink
            disabled={pages - 1 === props.current}
            handlePageNext={() => props.handlePageChange(props.current + 1)}
          />
        </ul>
      }
    </nav>
  )
}

Pagination.propTypes = {
  current: T.number,
  pageSize: T.number.isRequired,
  totalResults: T.number.isRequired,
  //pages: T.number.isRequired,
  handlePageChange: T.func.isRequired,
  handlePageSizeUpdate: T.func.isRequired
}

Pagination.defaultProps = {
  current: 0,
  pageSize: DEFAULT_PAGE_SIZE
}

export {
  Pagination
}
