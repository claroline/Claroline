import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

const CollectionDisplay = (props) =>
  <div className="collection-control collection-display">
    {isEmpty(props.data) &&
      <div className="no-item-info">{props.placeholder}</div>
    }

    {!isEmpty(props.data) &&
      <ul>
        {props.data.map((value, index) => {
          let customInput
          if (props.component) {
            customInput = createElement(props.component, Object.assign({}, props.options, {
              data: value
            }))
          } else if (props.render) {
            customInput = props.render(value, index)
          }

          return (
            <li key={index} className="collection-item">
              {customInput}
            </li>
          )
        })}
      </ul>
    }
  </div>

CollectionDisplay.propTypes = {
  data: T.array,
  placeholder: T.string,

  // items def
  type: T.string,
  render: T.func,
  component: T.any,
  options: T.object // depends on the type of items
}

CollectionDisplay.defaultProps = {
  data: [],
  placeholder: trans('no_item')
}

export {
  CollectionDisplay
}
