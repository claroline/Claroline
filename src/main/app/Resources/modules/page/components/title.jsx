import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action'
import {URL_BUTTON} from '#/main/app/buttons'

const PageBreadcrumb = props => {
  const items = props.path
    .filter(item => undefined === item.displayed || item.displayed)

  if (0 !== items.length) {
    return (
      <nav aria-label="breadcrumb" className={props.className}>
        <ol className="breadcrumb">
          {items
            .filter(item => undefined === item.displayed || item.displayed)
            .map((item) =>
              <li key={item.id || toKey(item.label)} className="breadcrumb-item">
                <Button
                  type={item.type || URL_BUTTON}
                  {...omit(item, 'displayed')}
                />
              </li>
            )
          }
          <li className="breadcrumb-item active visually-hidden" aria-current="page">{props.current}</li>
        </ol>
      </nav>
    )
  }

  return null
}

PageBreadcrumb.propTypes = {
  className: T.string,
  current: T.string.isRequired,
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

const PageTitle = props =>
  <div className="" role="presentation">
    {!props.embedded &&
      <PageBreadcrumb
        path={props.path}
        current={props.title}
      />
    }

    <h1 className="page-title">
      {props.title}
    </h1>
  </div>

PageTitle.propTypes = {
  title: T.string.isRequired,
  /**
   * The path of the page inside the application (used to build the breadcrumb).
   */
  path: T.arrayOf(T.shape({
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  })),
  embedded: T.bool
}


export {
  PageTitle
}
