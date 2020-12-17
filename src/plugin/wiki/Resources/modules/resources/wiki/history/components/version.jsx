import React from 'react'
import {PropTypes as T} from 'prop-types'
import {displayDate} from '#/main/app/intl/date'
import {ContentHtml} from '#/main/app/content/components/html'

const Version = props =>
  <div>
    <h2>{props.version.title && <ContentHtml>{props.version.title}</ContentHtml>}</h2>
    {props.version.meta && props.version.meta.creator &&
    <h5 className="small text-muted">[ {props.version.meta.creator.username} ({props.version.meta.creator.name}) - {displayDate(props.version.meta.createdAt, true, true)} ]</h5>
    }
    <ContentHtml className="wiki-section-content">{props.version.text}</ContentHtml>
  </div>
  
Version.propTypes = {
  version: T.object.isRequired
}

export {
  Version
}