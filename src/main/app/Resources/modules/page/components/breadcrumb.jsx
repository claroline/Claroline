import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'

const PageBreadcrumb = props => {
  if (0 !== props.breadcrumb.length) {
    return (
      <nav aria-label={trans('breadcrumb')} className={classes('', props.className)}>
        <ol className="breadcrumb flex-nowrap">
          {props.breadcrumb.map((item) =>
            <li key={item.id || toKey(item.label)} className="breadcrumb-item text-truncate">
              <Button
                {...item}
                type={LINK_BUTTON}
              />
            </li>
          )}
          <li className="breadcrumb-item active visually-hidden" aria-current="page">{props.current}</li>
        </ol>
      </nav>
    )
  }

  return undefined
}

PageBreadcrumb.propTypes = {
  current: T.string.isRequired,
  breadcrumb: T.arrayOf(T.shape({
    label: T.string.isRequired,
    target: T.string
  }))
}

PageBreadcrumb.defaultProps = {
  path: []
}

export {
  PageBreadcrumb
}
