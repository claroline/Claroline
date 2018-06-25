import React from 'react'
import {PropTypes as T} from 'prop-types'
import {displayDate} from '#/main/core/scaffolding/date'
import {HtmlText} from '#/main/core/layout/components/html-text'

const Version = props =>
  <div>
    <h2>{props.version.title && <HtmlText>{props.version.title}</HtmlText>}</h2>
    {props.version.meta && props.version.meta.creator &&
    <h5 className="small text-muted">[ {props.version.meta.creator.username} ({props.version.meta.creator.name}) - {displayDate(props.version.meta.createdAt, true, true)} ]</h5>
    }
    <HtmlText className="wiki-section-content">{props.version.text}</HtmlText>
  </div>
  
Version.propTypes = {
  version: T.object.isRequired
}

export {
  Version
}