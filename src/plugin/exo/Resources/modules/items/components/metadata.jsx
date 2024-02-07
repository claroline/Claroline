import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'
import {ObjectsPlayer} from '#/plugin/exo/items/components/item-objects'

const Metadata = props =>
  <>
    {((props.showTitle && props.item.title) || props.numbering) &&
      <h4 className="h5 item-title">
        {props.numbering &&
          <span className="h-numbering">{props.numbering}</span>
        }

        {props.showTitle && props.item.title}
      </h4>
    }

    {props.item.content && !props.isContentItem &&
      <ContentHtml className="item-content lead mb-3">{props.item.content}</ContentHtml>
    }

    {props.item.description &&
      <ContentHtml className="item-description mb-3">{props.item.description}</ContentHtml>
    }

    {props.item.objects && 0 !== props.item.objects.length &&
      <ObjectsPlayer item={props.item} />
    }
  </>

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
  numbering: T.string,
  showTitle: T.bool
}

Metadata.defaultProps = {
  showTitle: true
}

export {
  Metadata
}
