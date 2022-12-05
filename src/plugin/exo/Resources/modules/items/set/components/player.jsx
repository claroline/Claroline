import React, {Component} from 'react'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'
import {makeDraggable, makeDroppable} from '#/plugin/exo/utils/dragAndDrop'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {SetItemDragPreview} from '#/plugin/exo/items/set/components/set-item-drag-preview'

let DropBox = props => props.connectDropTarget(
  <div className={classes('set-drop-placeholder', {
    hover: props.isOver
  })}>
    {trans('set_drop_item', {}, 'quiz')}
  </div>
)

DropBox.propTypes = {
  connectDropTarget: T.func.isRequired,
  isOver: T.bool.isRequired,
  onDrop: T.func.isRequired,
  canDrop: T.bool.isRequired,
  object: T.object.isRequired
}

DropBox = makeDroppable(DropBox, 'ITEM')

const Association = props =>
  <div className="association answer-item set-answer-item selected">
    <ContentHtml className="item-content">
      {props.association._itemData}
    </ContentHtml>

    {props.removable &&
      <Button
        id={`ass-${props.association.itemId}-${props.association.setId}-delete`}
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash"
        label={trans('delete', {}, 'actions')}
        callback={() => props.handleItemRemove(props.association.setId, props.association.itemId)}
        tooltip="top"
      />
    }
  </div>

Association.propTypes = {
  association: T.object.isRequired,
  removable: T.bool.isRequired,
  handleItemRemove: T.func.isRequired
}

const Set = props =>
  <div className="set">
    <ContentHtml className="set-heading">
      {props.set.data}
    </ContentHtml>

    <ul>
      {props.associations.map(ass =>
        <li key={`${ass.itemId}-${ass.setId}`}>
          <Association handleItemRemove={props.onAssociationItemRemove} association={ass} removable={!props.disabled}/>
        </li>
      )}
    </ul>

    {!props.disabled &&
      <DropBox object={props.set} onDrop={props.onDrop} />
    }
  </div>

Set.propTypes = {
  set: T.object.isRequired,
  disabled: T.bool.isRequired,
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
          disabled={props.disabled}
        />
      </li>
    )}
  </ul>


SetList.propTypes = {
  sets: T.arrayOf(T.object).isRequired,
  answers: T.arrayOf(T.object).isRequired,
  disabled: T.bool.isRequired,
  onAssociationItemRemove: T.func.isRequired,
  onAssociationItemDrop: T.func.isRequired
}

let Item = props =>
  <div className="set-answer-item answer-item">
    <ContentHtml className="item-content">
      {props.item.data}
    </ContentHtml>

    {props.connectDragSource(
      <div>
        <OverlayTrigger
          placement="top"
          overlay={
            <Tooltip id={`item-${props.item.id}-drag`}>{trans('move')}</Tooltip>
          }
        >
          <span
            title={trans('move', {}, 'quiz')}
            draggable="true"
            className="btn-link default drag-handle"
          >
            {props.draggable &&
              <span className="fa fa-arrows"/>
            }
          </span>
        </OverlayTrigger>
      </div>
    )}
  </div>

Item.propTypes = {
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired,
  draggable: T.bool.isRequired
}

Item = makeDraggable(Item, 'ITEM', SetItemDragPreview)

const ItemList = props =>
  <ul>
    {props.items.map((item) =>
      <li key={item.id}>
        <Item item={item} draggable={props.draggable} />
      </li>
    )}
  </ul>

ItemList.propTypes = {
  items:  T.arrayOf(T.object).isRequired,
  draggable: T.bool.isRequired
}

class SetPlayer extends Component {
  constructor(props) {
    super(props)

    this.handleAssociationItemRemove = this.handleAssociationItemRemove.bind(this)
    this.handleAssociationItemDrop = this.handleAssociationItemDrop.bind(this)
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
    if (undefined === this.props.answer.find(el => el.setId === target.object.id && el.itemId === source.item.id)){
      // do something to add to solution
      this.props.onChange(
        [{itemId: source.item.id, setId: target.object.id, _itemData: source.item.data}].concat(this.props.answer)
      )
    }
  }

  render() {
    return (
      <div className="set-item set-player row">
        <div className="items-col col-md-5 col-sm-5 col-xs-5">
          <ItemList items={this.props.item.items} draggable={!this.props.disabled}/>
        </div>

        <div className="sets-col col-md-7 col-sm-7 col-xs-7">
          <SetList
            onAssociationItemRemove={this.handleAssociationItemRemove}
            onAssociationItemDrop={this.handleAssociationItemDrop}
            answers={this.props.answer}
            sets={this.props.item.sets}
            disabled={this.props.disabled}
          />
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
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

SetPlayer.defaultProps = {
  answer: [],
  disabled: false
}

export {
  SetPlayer
}
