import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import {makeItemPanelKey, makeStepPropPanelKey} from './../../../utils/utils'
import {t, tex, trans} from '#/main/core/translation'
import {makeSortable, SORT_VERTICAL} from './../../../utils/sortable'
import {getDefinition, isQuestionType} from './../../../items/item-types'
import {getContentDefinition} from './../../../contents/content-types'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {MODAL_ADD_ITEM} from './../components/add-item-modal.jsx'
import {MODAL_IMPORT_ITEMS} from './../components/import-items-modal.jsx'
import {MODAL_ADD_CONTENT} from './../components/add-content-modal.jsx'
import {Icon as ItemIcon} from './../../../items/components/icon.jsx'
import {ValidationStatus} from './validation-status.jsx'
import {StepForm} from './step-form.jsx'
import {ItemForm} from './item-form.jsx'
import {ContentItemForm} from './content-item-form.jsx'
import {ItemPanelDragPreview} from './item-panel-drag-preview.jsx'
import {ContentPanelDragPreview} from './content-panel-drag-preview.jsx'

const ParametersHeader = props =>
  <div onClick={props.onClick} className="panel-title editor-panel-title">
    <span className={
      classes(
        'fa fa-fw',
        props.active ? 'fa-caret-down' : 'fa-caret-right'
      )}
    />
    &nbsp;{t('parameters', {}, 'platform')}
  </div>

ParametersHeader.propTypes = {
  active: T.bool.isRequired,
  onClick: T.func.isRequired
}

const ItemActions = props =>
  <div className="item-actions">
    {props.hasErrors &&
      <ValidationStatus
        id={`${props.itemId}-panel-tip`}
        validating={props.validating}
        position="left"
      />
    }

    <OverlayTrigger
      placement="left"
      overlay={
        <Tooltip id={`item-panel-${props.itemId}-delete`}>
          {tex('delete_item')}
        </Tooltip>
      }
    >
      <button
        type="button"
        className="btn btn-link-default"
        onClick={e => {
          e.stopPropagation()
          props.showModal(MODAL_DELETE_CONFIRM, {
            title: tex('delete_item'),
            question: tex('remove_question_confirm_message'),
            handleConfirm: () => props.handleItemDeleteClick(props.itemId, props.stepId)
          })
        }}
      >
        <span className="fa fa-fw fa-trash-o" />
      </button>
    </OverlayTrigger>

    {props.connectDragSource(
      <span>
        <OverlayTrigger
          placement="left"
          overlay={
            <Tooltip id={`item-panel-${props.itemId}-toggle`}>
              {tex('move_item')}
            </Tooltip>
          }
        >
          <span
            role="button"
            className="btn btn-link-default drag-handle"
            draggable="true"
            onClick={() => false}
          >
            <span className="fa fa-fw fa-arrows" />
          </span>
        </OverlayTrigger>
      </span>
    )}
  </div>

ItemActions.propTypes = {
  itemId: T.string.isRequired,
  stepId: T.string.isRequired,
  hasErrors: T.bool.isRequired,
  validating: T.bool.isRequired,
  handleItemDeleteClick: T.func.isRequired,
  showModal: T.func.isRequired,
  connectDragSource: T.func.isRequired
}

const ItemHeader = props =>
  <div
    className="item-header"
    onClick={() => props.handlePanelClick(
      props.stepId,
      makeItemPanelKey(props.item.type, props.item.id)
    )}
  >
    <span className="panel-title">
      <ItemIcon name={getDefinition(props.item.type).name}/>
      {props.item.title || trans(getDefinition(props.item.type).name, {}, 'question_types')}
    </span>

    <ItemActions
      itemId={props.item.id}
      stepId={props.stepId}
      hasErrors={props.hasErrors}
      validating={props.validating}
      handleItemDeleteClick={props.handleItemDeleteClick}
      showModal={props.showModal}
      connectDragSource={props.connectDragSource}
    />
  </div>

ItemHeader.propTypes = {
  item: T.object.isRequired,
  stepId: T.string.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDeleteClick: T.func.isRequired,
  showModal: T.func.isRequired,
  hasErrors: T.bool.isRequired,
  validating: T.bool.isRequired,
  connectDragSource: T.func.isRequired
}

let ItemPanel = props =>
    props.connectDropTarget(
      <div
        style={{opacity: props.isDragging ? 0 : 1}}
      >
        <Panel
          header={
            <ItemHeader
              item={props.item}
              stepId={props.stepId}
              handlePanelClick={props.handlePanelClick}
              handleItemDeleteClick={props.handleItemDeleteClick}
              showModal={props.showModal}
              connectDragSource={props.connectDragSource}
              hasErrors={!isEmpty(props.item._errors)}
              validating={props.validating}
            />
          }
          collapsible={true}
          expanded={props.expanded}
        >
          {props.expanded &&
            <ItemForm
              item={props.item}
              validating={props.validating}
              showModal={props.showModal}
              closeModal={props.closeModal}
              onChange={(propertyPath, value) =>
                props.handleItemUpdate(props.item.id, propertyPath, value)
              }
              onHintsChange={(updateType, payload) =>
                props.handleItemHintsUpdate(props.item.id, updateType, payload)
              }
            >
              {React.createElement(
                getDefinition(props.item.type).editor.component,
                {
                  item: props.item,
                  validating: props.validating,
                  onChange: subAction =>
                    props.handleItemDetailUpdate(props.item.id, subAction)
                }
              )}
            </ItemForm>
          }
        </Panel>
      </div>
  )

ItemPanel.propTypes = {
  id: T.string.isRequired,
  stepId: T.string.isRequired,
  index: T.number.isRequired,
  item: T.object.isRequired,
  expanded: T.bool.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDeleteClick: T.func.isRequired,
  handleItemUpdate: T.func.isRequired,
  handleItemDetailUpdate: T.func.isRequired,
  showModal: T.func.isRequired,
  closeModal: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  isDragging: T.bool.isRequired,
  onSort: T.func.isRequired,
  sortDirection: T.string.isRequired,
  validating: T.bool.isRequired
}

const ContentHeader = props =>
  <div
    className="item-header"
    onClick={() => props.handlePanelClick(
      props.stepId,
      makeItemPanelKey(props.item.type, props.item.id)
    )}
  >
    <span className="panel-title">
      <span className={classes('item-icon', 'item-icon-sm', getContentDefinition(props.item.type).altIcon)}></span>
      {props.item.title || trans(getContentDefinition(props.item.type).type, {}, 'question_types')}
    </span>

    {props.hasErrors &&
      <ValidationStatus
        id={`${props.item.id}-panel-tip`}
        validating={props.validating}
      />
    }

    <ItemActions
      itemId={props.item.id}
      stepId={props.stepId}
      handleItemDeleteClick={props.handleItemDeleteClick}
      showModal={props.showModal}
      connectDragSource={props.connectDragSource}
    />
  </div>

ContentHeader.propTypes = {
  item: T.object.isRequired,
  stepId: T.string.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDeleteClick: T.func.isRequired,
  showModal: T.func.isRequired,
  hasErrors: T.bool.isRequired,
  validating: T.bool.isRequired,
  connectDragSource: T.func.isRequired
}

let ContentPanel = props =>
    props.connectDropTarget(
      <div
        style={{opacity: props.isDragging ? 0 : 1}}
      >
        <Panel
          header={
            <ContentHeader
              item={props.item}
              stepId={props.stepId}
              handlePanelClick={props.handlePanelClick}
              handleItemDeleteClick={props.handleItemDeleteClick}
              showModal={props.showModal}
              connectDragSource={props.connectDragSource}
              hasErrors={!isEmpty(props.item._errors)}
              validating={props.validating}
            />
          }
          collapsible={true}
          expanded={props.expanded}
        >
          {props.expanded &&
          <ContentItemForm
            item={props.item}
            validating={props.validating}
            onChange={(propertyPath, value) =>
                props.handleContentItemUpdate(props.item.id, propertyPath, value)
              }
          >
            {React.createElement(
              getContentDefinition(props.item.type).editor.component,
              {
                item: props.item,
                validating: props.validating,
                onChange: subAction =>
                  props.handleContentItemDetailUpdate(props.item.id, subAction)
              }
            )}
          </ContentItemForm>
          }
        </Panel>
      </div>
    )

ContentPanel.propTypes = {
  id: T.string.isRequired,
  stepId: T.string.isRequired,
  index: T.number.isRequired,
  item: T.object.isRequired,
  expanded: T.bool.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDeleteClick: T.func.isRequired,
  handleItemUpdate: T.func.isRequired,
  handleItemDetailUpdate: T.func.isRequired,
  handleContentItemUpdate: T.func.isRequired,
  handleContentItemDetailUpdate: T.func.isRequired,
  showModal: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  isDragging: T.bool.isRequired,
  onSort: T.func.isRequired,
  sortDirection: T.string.isRequired,
  validating: T.bool.isRequired
}

ItemPanel = makeSortable(
  ItemPanel,
  'STEP_ITEM',
  ItemPanelDragPreview
)
ContentPanel = makeSortable(
  ContentPanel,
  'STEP_ITEM',
  ContentPanelDragPreview
)

class StepFooter extends Component {
  constructor(props) {
    super(props)
    // this is required before componentDidMount. If not state is not defined...
    this.state = {
      currentLabel: tex('add_question_from_new'),
      currentAction: MODAL_ADD_ITEM
    }
  }

  handleBtnClick(action) {
    this.setState({
      currentLabel:action === MODAL_ADD_ITEM ?
        tex('add_question_from_new') :
        action === MODAL_IMPORT_ITEMS ?
          tex('add_question_from_existing') :
          tex('add_content'),
      currentAction: action
    })
    if (action === MODAL_ADD_ITEM) {
      this.props.showModal(MODAL_ADD_ITEM, {
        title: tex('add_question_from_new'),
        handleSelect: type => {
          this.props.closeModal()
          this.props.handleItemCreate(this.props.stepId, type)
        }
      })
    } else if (action === MODAL_IMPORT_ITEMS) {
      this.props.showModal(MODAL_IMPORT_ITEMS, {
        title: tex('add_question_from_existing'),
        handleSelect: selected => {
          this.props.closeModal()
          this.props.handleItemsImport(this.props.stepId, selected)
        }
      })
    } else if (action === MODAL_ADD_CONTENT) {
      this.props.showModal(MODAL_ADD_CONTENT, {
        title: tex('add_content'),
        handleSelect: (selected) => {
          this.props.closeModal()
          return this.props.handleContentItemCreate(this.props.stepId, selected)
        },
        handleFileUpload: (itemId, file) => {
          this.props.handleFileUpload(itemId, file)
          return this.props.closeModal()
        }
      })
    }
  }

  render() {
    return (
      <div className="step-footer">
        <div className="btn-group">
          <button type="button" onClick={() => this.handleBtnClick(this.state.currentAction)} className="btn btn-primary">{this.state.currentLabel}</button>
          <button type="button" className="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span className="caret"></span>
            <span className="sr-only">Toggle Dropdown</span>
          </button>
          { this.state.currentAction === MODAL_IMPORT_ITEMS ?
            <ul className="dropdown-menu">
              <li>
                <a role="button" onClick={() => this.handleBtnClick(MODAL_ADD_ITEM)}>
                  {tex('add_question_from_new')}
                </a>
              </li>
              <li>
                <a role="button" onClick={() => this.handleBtnClick(MODAL_ADD_CONTENT)}>
                  {tex('add_content')}
                </a>
              </li>
            </ul>
            :
            this.state.currentAction === MODAL_ADD_ITEM ?
              <ul className="dropdown-menu">
                <li>
                  <a role="button" onClick={() => this.handleBtnClick(MODAL_IMPORT_ITEMS)}>
                    {tex('add_question_from_existing')}
                  </a>
                </li>
                <li>
                  <a role="button" onClick={() => this.handleBtnClick(MODAL_ADD_CONTENT)}>
                    {tex('add_content')}
                  </a>
                </li>
              </ul>
              :
              <ul className="dropdown-menu">
                <li>
                  <a role="button" onClick={() => this.handleBtnClick(MODAL_ADD_ITEM)}>
                    {tex('add_question_from_new')}
                  </a>
                </li>
                <li>
                  <a role="button" onClick={() => this.handleBtnClick(MODAL_IMPORT_ITEMS)}>
                    {tex('add_question_from_existing')}
                  </a>
                </li>
              </ul>
          }
        </div>
      </div>
    )
  }
}

StepFooter.propTypes = {
  stepId: T.string.isRequired,
  showModal: T.func.isRequired,
  closeModal: T.func.isRequired,
  handleItemCreate: T.func.isRequired,
  handleItemsImport: T.func.isRequired,
  handleContentItemCreate: T.func.isRequired,
  handleContentItemUpdate: T.func,
  handleFileUpload: T.func
}

export const StepEditor = props =>
  <div>
    <PanelGroup accordion activeKey={props.activePanelKey}>
      <Panel
        className="step-parameters"
        eventKey={makeStepPropPanelKey(props.step.id)}
        header={
          <ParametersHeader
            active={props.activePanelKey === makeStepPropPanelKey(props.step.id)}
            onClick={() => props.handlePanelClick(
              props.step.id,
              makeStepPropPanelKey(props.step.id)
            )}
          />
        }
      >
        <StepForm
          onChange={(newValue) => props.updateStep(props.step.id, newValue)}
          {...props.step}
        />
      </Panel>
      {props.step.items.map((item, index) => isQuestionType(item.type) ?
        <ItemPanel
          id={item.id}
          index={index}
          item={item}
          stepId={props.step.id}
          key={item.type + item.id}
          eventKey={makeItemPanelKey(item.type, item.id)}
          onSort={(id, swapId) => props.handleItemMove(id, swapId, props.step.id)}
          sortDirection={SORT_VERTICAL}
          validating={props.validating}
          handlePanelClick={props.handlePanelClick}
          handleItemDeleteClick={props.handleItemDeleteClick}
          handleItemCreate={props.handleItemCreate}
          handleItemUpdate={props.handleItemUpdate}
          handleItemHintsUpdate={props.handleItemHintsUpdate}
          handleItemDetailUpdate={props.handleItemDetailUpdate}
          showModal={props.showModal}
          closeModal={props.closeModal}
          {...props}
        /> :
        <ContentPanel
          id={item.id}
          index={index}
          item={item}
          stepId={props.step.id}
          key={item.type + item.id}
          eventKey={makeItemPanelKey(item.type, item.id)}
          onSort={(id, swapId) => props.handleItemMove(id, swapId, props.step.id)}
          sortDirection={SORT_VERTICAL}
          validating={props.validating}
          handlePanelClick={props.handlePanelClick}
          handleItemDeleteClick={props.handleItemDeleteClick}
          handleContentItemUpdate={props.handleContentItemUpdate}
          handleContentItemDetailUpdate={props.handleContentItemDetailUpdate}
          showModal={props.showModal}
          {...props}
        />
      )}
    </PanelGroup>
    <StepFooter
      stepId={props.step.id}
      showModal={props.showModal}
      closeModal={props.closeModal}
      handleItemCreate={props.handleItemCreate}
      handleItemsImport={props.handleItemsImport}
      handleContentItemCreate={props.handleContentItemCreate}
      handleContentItemUpdate={props.handleContentItemUpdate}
      handleFileUpload={props.handleFileUpload}
    />
  </div>

StepEditor.propTypes = {
  step: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    parameters: T.shape({
      maxAttempts: T.number.isRequired
    }).isRequired,
    items: T.arrayOf(T.object).isRequired
  }).isRequired,
  activePanelKey: T.oneOfType([T.string, T.bool]).isRequired,
  validating: T.bool.isRequired,
  updateStep: T.func.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDeleteClick: T.func.isRequired,
  handleItemMove: T.func.isRequired,
  handleItemCreate: T.func.isRequired,
  handleItemUpdate: T.func.isRequired,
  handleItemHintsUpdate: T.func.isRequired,
  handleItemsImport: T.func.isRequired,
  handleContentItemCreate: T.func.isRequired,
  handleContentItemUpdate: T.func,
  handleFileUpload: T.func,
  showModal: T.func.isRequired,
  closeModal: T.func.isRequired
}
