import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Panel from 'react-bootstrap/lib/Panel'

import {trans} from '#/main/core/translation'
import {getContentDefinition} from './../../../contents/content-types'

const ContentHeaderPreview = props =>
  <div className="item-header">
    <span className="panel-title">
      <span className={classes('item-icon', 'item-icon-sm', getContentDefinition(props.item.type).icon)}></span>
      {props.item.title || trans(getContentDefinition(props.item.type).type, {}, 'question_types')}
    </span>
  </div>

ContentHeaderPreview.propTypes = {
  item: T.object.isRequired
}

export const ContentPanelDragPreview = props =>
  <div>
    <Panel header={<ContentHeaderPreview {...props}/>} />
  </div>

ContentPanelDragPreview.propTypes = {
  item: T.object.isRequired
}
