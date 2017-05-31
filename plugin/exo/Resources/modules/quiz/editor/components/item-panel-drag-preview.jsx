import React from 'react'
import {PropTypes as T} from 'prop-types'
import Panel from 'react-bootstrap/lib/Panel'

import {trans} from '#/main/core/translation'
import {getDefinition} from './../../../items/item-types'
import {Icon as ItemIcon} from './../../../items/components/icon.jsx'

const ItemHeaderPreview = props =>
  <div className="item-header">
    <span className="panel-title">
      <ItemIcon name={getDefinition(props.item.type).name}/>
      {props.item.title || trans(getDefinition(props.item.type).name, {}, 'question_types')}
    </span>
  </div>

ItemHeaderPreview.propTypes = {
  item: T.object.isRequired
}

export const ItemPanelDragPreview = props =>
  <div>
    <Panel header={<ItemHeaderPreview {...props}/>} />
  </div>


ItemPanelDragPreview.propTypes = {
  item: T.object.isRequired
}
