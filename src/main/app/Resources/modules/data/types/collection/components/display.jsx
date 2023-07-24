import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {DataDisplay} from '#/main/app/data/components/display'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const CollectionDisplay = (props) =>
  <div className="collection-control collection-display">
    {isEmpty(props.data) &&
      <ContentPlaceholder title={props.placeholder} />
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
              <DataDisplay
                id={`${props.id}-${index}`}
                type={props.type}
                options={props.options}

                label={`${props.label} #${index + 1}`}
                size="sm"
                hideLabel={true}
                value={value}
              >
                {customInput}
              </DataDisplay>
            </li>
          )
        })}
      </ul>
    }
  </div>

CollectionDisplay.propTypes = {
  id: T.string,
  label: T.string,
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
