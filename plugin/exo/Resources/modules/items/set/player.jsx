import React, {Component} from 'react'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import {tex, t} from '#/main/core/translation'
import {makeDraggable, makeDroppable} from './../../utils/dragAndDrop'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {SetItemDragPreview} from './set-item-drag-preview.jsx'

let DropBox = props => {
  return props.connectDropTarget (
     <div className={classes(
       'set-drop-placeholder',
       {'hover': props.isOver}
     )}>
       {tex('set_drop_item')}
     </div>
   )
}

DropBox.propTypes = {
  connectDropTarget: T.func.isRequired,
  isOver: T.bool.isRequired,
  onDrop: T.func.isRequired,
  canDrop: T.bool.isRequired,
  object: T.object.isRequired
}

DropBox = makeDroppable(DropBox, 'ITEM')

const Association = props =>
  <div className="association answer-item selected">
    <div className="association-data" dangerouslySetInnerHTML={{__html: props.association._itemData}} />

    <TooltipButton
      id={`ass-${props.association.itemId}-${props.association.setId}-delete`}
      className="btn-link-default"
      title={t('delete')}
      onClick={() => props.handleItemRemove(props.association.setId, props.association.itemId)}
    >
      <span className="fa fa-fw fa-trash-o" />
    </TooltipButton>
  </div>

Association.propTypes = {
  association: T.object.isRequired,
  handleItemRemove: T.func.isRequired
}

const Set = props =>
  <div className="set answer-item">
    <div className="set-heading" dangerouslySetInnerHTML={{__html: props.set.data}} />

    <ul>
    {props.associations.map(ass =>
      <li key={`${ass.itemId}-${ass.setId}`}>
        <Association handleItemRemove={props.onAssociationItemRemove} association={ass} />
      </li>
    )}
    </ul>

    <DropBox object={props.set} onDrop={props.onDrop} />
  </div>

Set.propTypes = {
  set: T.object.isRequired,
  onDrop: T.func.isRequired,
  associations: T.arrayOf(T.object).isRequired,
  onAssociationItemRemove: T.func.isRequired
}

const SetList = props =>
  <ul>
    {props.sets.map((set) =>
      <li key={`set-id-${set.id}`}>
        <Set
          associations={props.answers.filter(answer => answer.setId === set.id) || []}
          onDrop={props.onAssociationItemDrop}
          onAssociationItemRemove={props.onAssociationItemRemove}
          set={set}
        />
      </li>
    )}
  </ul>


SetList.propTypes = {
  sets: T.arrayOf(T.object).isRequired,
  answers: T.arrayOf(T.object).isRequired,
  onAssociationItemRemove: T.func.isRequired,
  onAssociationItemDrop: T.func.isRequired
}

let Item = props => {
  return (
    <div className="set-item answer-item">
      <div className="item-content" dangerouslySetInnerHTML={{__html: props.item.data}} />
      {props.connectDragSource(
        <div>
          <OverlayTrigger
            placement="top"
            overlay={
              <Tooltip id={`item-${props.item.id}-drag`}>{t('move')}</Tooltip>
            }>
            <span
              draggable="true"
              className={classes(
                'tooltiped-button',
                'btn btn-link-default',
                'fa fa-fw',
                'fa-arrows',
                'drag-handle'
              )}
            />
          </OverlayTrigger>
        </div>
      )}
    </div>
  )
}

Item.propTypes = {
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired
}

Item = makeDraggable(
  Item,
  'ITEM',
  SetItemDragPreview
)

const ItemList = props =>
    <ul>
      { props.items.map((item) =>
        <li key={item.id}>
          <Item item={item}/>
        </li>
      )}
    </ul>


ItemList.propTypes = {
  items:  T.arrayOf(T.object).isRequired
}

class SetPlayer extends Component {
  constructor(props) {
    super(props)
  }

  handleAssociationItemRemove(setId, itemId) {
    this.props.onChange(
       this.props.answer.filter(answer => answer.setId !== setId || answer.itemId !== itemId)
    )
  }

    /**
     * handle item drop
     * @var {source} dropped item (item)
     * @var {target} target item (set)
     */
  handleAssociationItemDrop(source, target) {

    if(undefined === this.props.answer.find(el => el.setId === target.object.id && el.itemId === source.item.id)){
      // do something to add to solution
      this.props.onChange(
          [{itemId: source.item.id, setId: target.object.id, _itemData: source.item.data}].concat(this.props.answer)
       )
    }
  }

  render() {
    return (
      <div className="set-player row">
        <div className="items-col col-md-5 col-sm-5 col-xs-5">
          <ItemList items={this.props.item.items} />
        </div>

        <div className="sets-col col-md-7 col-sm-7 col-xs-7">
          <SetList
            onAssociationItemRemove={(setId, itemId) => this.handleAssociationItemRemove(setId, itemId)}
            onAssociationItemDrop={(source, target) => this.handleAssociationItemDrop(source, target)}
            answers={this.props.answer}
            sets={this.props.item.sets} />
        </div>
      </div>
    )
  }
}

SetPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    random: T.bool.isRequired,
    sets: T.arrayOf(T.object).isRequired,
    items: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.array.isRequired,
  onChange: T.func.isRequired
}

SetPlayer.defaultProps = {
  answer: []
}

export {SetPlayer}
