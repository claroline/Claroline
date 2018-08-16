import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'

const MessagesSort = props =>
  <div>
    <div className="messages-sort">
      {trans('list_sort_by')}
      <button
        type="button"
        className="btn btn-link"
        disabled={0 === props.totalResults}
        onClick={props.toggleSort}
      >
        {trans(1 === props.sortOrder ? 'from_older_to_newer':'from_newer_to_older', {}, 'forum')}
      </button>
    </div>

    {props.children}

    {1 < props.pages &&
      <nav className="text-right">
        <div className="pagination-condensed btn-group">
          <button
            type="button"
            className="btn btn-pagination btn-previous"
            disabled={0 === props.currentPage}
            onClick={props.changePagePrev}
          >
            <span className="fa fa-angle-double-left" aria-hidden="true" />
            <span className="sr-only">
              {trans(1 === props.sortOrder ? 'older':'newer', {}, 'forum')}
            </span>
          </button>

          <button
            type="button"
            className="btn btn-pagination btn-next"
            disabled={(props.pages - 1) === props.currentPage}
            onClick={props.changePage}
          >
            {trans(1 === props.sortOrder ? 'newer':'older', {}, 'forum')}
            <span className="fa fa-angle-double-right" aria-hidden="true" />
          </button>
        </div>
      </nav>
    }
  </div>

MessagesSort.propTypes = {
  sortOrder: T.number.isRequired,
  children: T.node.isRequired,
  currentPage: T.number,
  pages: T.number.isRequired,
  changePage: T.func.isRequired,
  changePagePrev: T.func.isRequired,
  toggleSort: T.func.isRequired,
  messages: T.arrayOf(T.shape({})),
  totalResults: T.number.isRequired
}

export {
  MessagesSort
}
