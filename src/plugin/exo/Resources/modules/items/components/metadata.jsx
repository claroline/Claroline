import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'
import {ObjectsPlayer} from '#/plugin/exo/items/components/item-objects'

export const Metadata = props =>
  <div className="item-metadata">
    <div>
      {props.numbering &&
        <span className="numbering">{props.numbering}. {'\u0020'}</span>
      }

      {props.item.content && !props.isContentItem &&
        <ContentHtml className="item-content">{props.item.content}</ContentHtml>
      }
    </div>

    {props.item.description &&
      <ContentHtml className="item-description">{props.item.description}</ContentHtml>
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
