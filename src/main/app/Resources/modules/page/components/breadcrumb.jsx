import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {toKey} from '#/main/core/scaffolding/text'
import {URL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

const PageBreadcrumb = props => {
  const items = props.path
    .filter(item => undefined === item.displayed || item.displayed)

  if (0 !== items.length) {
    return (
      <nav aria-label="breadcrumb" className={props.className}>
        <ol className="breadcrumb">
          {items
            .filter(item => undefined === item.displayed || item.displayed)
            .map((item, index) => index !== items.length - 1 ?
              <li key={item.id || toKey(item.label)} className="breadcrumb-item">
                <Button
                  type={item.type || URL_BUTTON}
                  {...omit(item, 'displayed')}
                />
              </li>
              :
              <li key={item.id || toKey(item.label)} className="breadcrumb-item active visually-hidden"  aria-current="page">{item.label}</li>
            )
          }
        </ol>
      </nav>
    )
  }

  return null
}

PageBreadcrumb.propTypes = {
  className: T.string,
  path: T.arrayOf(T.shape({
    id: T.string,
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