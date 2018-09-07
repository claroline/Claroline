import React from 'react'
import {PropTypes as T} from 'prop-types'
import times from 'lodash/times'

import {trans, transChoice} from '#/main/core/translation'
import {CallbackButton, CALLBACK_BUTTON} from '#/main/app/buttons/callback'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

const PaginationPages = props =>
  <div className="pagination-condensed btn-group">
    <CallbackButton
      className="btn btn-link btn-previous"
      disabled={0 === props.current}
      callback={() => props.changePage(props.current - 1)}
    >
      <span className="fa fa-angle-double-left" aria-hidden="true" />
      <span className="sr-only">{trans('previous')}</span>
    </CallbackButton>

    <MenuButton
      id="pagination-pages-dropdown"
      className="btn btn-link"
      disabled={1 === props.pages}
      menu={{
        position: 'top',
        label: trans('pages'),
        items: times(props.pages, (page) => ({
          type: CALLBACK_BUTTON,
          label: transChoice('page_number', page + 1, {number: page + 1}),
          active: page === props.current,
          callback: () => props.changePage(page)
        }))
      }}
    >
      {trans('current_page', {current: props.current + 1, pages: props.pages})}
    </MenuButton>

    <CallbackButton
      className="btn btn-link btn-next"
      disabled={props.pages - 1 === props.current}
      callback={() => props.changePage(props.current + 1)}
    >
      {trans('next')}
      <span className="fa fa-angle-double-right icon-with-text-left" aria-hidden="true" />
    </CallbackButton>
  </div>

PaginationPages.propTypes = {
  current: T.number,
  pages: T.number.isRequired,
  changePage: T.func.isRequired
}

PaginationPages.defaultProps = {
  current: 0
}

export {
  PaginationPages
}
