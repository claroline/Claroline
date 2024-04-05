import React from 'react'
import {PropTypes as T} from 'prop-types'

import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'

const PageBreadcrumb = props => {
  if (0 !== props.breadcrumb.length) {
    return (
      <nav aria-label="breadcrumb">
        <ol className="breadcrumb">
          {props.breadcrumb.map((item) =>
            <li key={item.id || toKey(item.label)} className="breadcrumb-item">
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

const PageTitle = props =>
  <div role="presentation">
    {!props.embedded &&
      <PageBreadcrumb
        breadcrumb={props.breadcrumb}
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
  breadcrumb: T.arrayOf(T.shape({
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  })),
  embedded: T.bool
}

export {
  PageTitle
}
