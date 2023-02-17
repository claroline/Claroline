import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback'

const MessagesSort = props =>
  <Fragment>
    <div className="messages-sort">
      {trans('list_sort_by')}
      <CallbackButton
        className="btn btn-link"
        disabled={0 === props.totalResults}
        callback={props.toggleSort}
        primary={true}
      >
        {trans(1 === props.sortOrder ? 'from_older_to_newer':'from_newer_to_older', {}, 'forum')}
      </CallbackButton>
    </div>

    {props.children}

    {1 < props.pages &&
      <nav className="text-right">
        <div className="pagination-condensed btn-group">
          <CallbackButton
            className="btn btn-pagination btn-previous"
            disabled={0 === props.currentPage}
            callback={props.changePagePrev}
          >
            <span className="fa fa-angle-double-left" aria-hidden="true" />
            <span className="sr-only">
              {trans(1 === props.sortOrder ? 'older':'newer', {}, 'forum')}
            </span>
          </CallbackButton>

          <CallbackButton
            className="btn btn-pagination btn-next"
            disabled={(props.pages - 1) === props.currentPage}
            callback={props.changePage}
          >
            {trans(1 === props.sortOrder ? 'newer':'older', {}, 'forum')}
            <span className="fa fa-angle-double-right" aria-hidden="true" />
          </CallbackButton>
        </div>
      </nav>
    }
  </Fragment>

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
