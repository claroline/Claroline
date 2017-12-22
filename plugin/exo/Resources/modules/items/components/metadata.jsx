import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ObjectsPlayer} from './item-objects.jsx'

export const Metadata = props =>
  <div className="item-metadata">
    <div>
      {props.numbering &&
        <span className="numbering">{props.numbering}. {'\u0020'}</span>
      }

      {props.item.content && !props.isContentItem &&
        <span className="item-content" dangerouslySetInnerHTML={{__html: props.item.content}} />
      }
    </div>

    {props.item.description &&
      <div className="item-description" dangerouslySetInnerHTML={{__html: props.item.description}}></div>
    }

    {props.item.objects && 0 !== props.item.objects.length &&
      <ObjectsPlayer item={props.item} />
    }
  </div>

Metadata.propTypes = {
  item: T.shape({
    title: T.string,
    content: T.string,
    description: T.string,
    objects: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      url: T.string,
      data: T.string
    }))
  }).isRequired,
  isContentItem: T.bool,
  numbering: T.string
}
