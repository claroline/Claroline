import React from 'react'
import {PropTypes as T} from 'prop-types'
import times from 'lodash/times'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CallbackButton, CALLBACK_BUTTON} from '#/main/app/buttons/callback'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

const PaginationPages = props =>
  <nav className="pagination-condensed btn-group">
    <CallbackButton
      className="btn-previous btn btn-link"
      disabled={props.disabled || 0 === props.current}
      callback={() => props.changePage(props.current - 1)}
    >
      <span className="fa fa-angle-double-left" aria-hidden="true" />
      <span className="visually-hidden">{trans('previous')}</span>
    </CallbackButton>

    <MenuButton
      id="pagination-pages-dropdown"
      className="w-100 btn btn-link"
      disabled={props.disabled || 1 === props.pages}
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
      className="btn-next btn btn-link"
      disabled={props.disabled || props.pages - 1 === props.current}
      callback={() => props.changePage(props.current + 1)}
    >
      <span className="visually-hidden">{trans('next')}</span>
      <span className="fa fa-angle-double-right" aria-hidden="true" />
    </CallbackButton>
  </nav>

PaginationPages.propTypes = {
  disabled: T.bool.isRequired,
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
