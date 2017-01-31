import React, {PropTypes as T} from 'react'

export const Metadata = props => {
  return(
      <div className="item-metadata">
        {props.item.content &&
          <div className="item-content" dangerouslySetInnerHTML={{__html: props.item.content}}></div>
        }

        {props.item.description &&
          <div className="item-description" dangerouslySetInnerHTML={{__html: props.item.description}}></div>
        }
      </div>
  )
}

Metadata.propTypes = {
  item: T.shape({
    title: T.string,
    content: T.string.isRequired,
    description: T.string
  }).isRequired
}
