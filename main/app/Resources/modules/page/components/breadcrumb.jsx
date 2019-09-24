import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {toKey} from '#/main/core/scaffolding/text'
import {URL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

const PageBreadcrumb = props => {
  const items = props.path
    .filter(item => undefined === item.displayed || item.displayed)

  return (
    <ul className={classes('breadcrumb', props.className)}>
      {items
        .filter(item => undefined === item.displayed || item.displayed)
        .map((item, index) => index !== items.length - 1 ?
          <li key={toKey(item.label)} role="presentation">
            <Button
              type={item.type || URL_BUTTON}
              {...omit(item, 'displayed')}
            />
          </li>
          :
          <li key={toKey(item.label)} className="active" role="presentation">{item.label}</li>
        )
      }
    </ul>
  )
}

PageBreadcrumb.propTypes = {
  className: T.string,
  path: T.arrayOf(T.shape({
    type: T.string,
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  }))
}

PageBreadcrumb.defaultProps = {
  path: []
}

export {
  PageBreadcrumb
}