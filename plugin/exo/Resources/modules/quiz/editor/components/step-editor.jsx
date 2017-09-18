import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {t, tex, trans} from '#/main/core/translation'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {makeItemPanelKey, makeStepPropPanelKey} from './../../../utils/utils'
import {makeSortable, SORT_VERTICAL} from './../../../utils/sortable'
import {getDefinition, isQuestionType} from './../../../items/item-types'
import {getContentDefinition} from './../../../contents/content-types'

import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {MODAL_ADD_ITEM} from './modal/add-item-modal.jsx'
import {MODAL_IMPORT_ITEMS} from './modal/import-items-modal.jsx'
import {MODAL_ADD_CONTENT} from './modal/add-content-modal.jsx'
import {MODAL_MOVE_ITEM} from './modal/move-item-modal.jsx'
import {MODAL_DUPLICATE_ITEM} from '#/plugin/exo/items/components/modal/duplicate-modal.jsx'

import {Icon as ItemIcon} from './../../../items/components/icon.jsx'
import {ValidationStatus} from './validation-status.jsx'
import {StepForm} from './step-form.jsx'
import {ItemForm} from './item-form.jsx'
import {ContentItemForm} from './content-item-form.jsx'
import {ItemPanelDragPreview} from './item-panel-drag-preview.jsx'
import {ContentPanelDragPreview} from './content-panel-drag-preview.jsx'
import {getNumbering} from './../../../utils/numbering'
import {NUMBERING_NONE} from './../../../quiz/enums'

const ParametersHeader = props =>
  <div onClick={props.onClick} className="panel-title editor-panel-title" aria-expanded={props.active}>
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

    <TooltipButton
      id={`item-panel-${props.itemId}-delete`}
      className="btn-link-danger"
      title={t('delete')}
      position="left"
      onClick={e => {
        e.stopPropagation()
        props.showModal(MODAL_DELETE_CONFIRM, {
          title: tex('delete_item'),
          question: tex('remove_question_confirm_message'),
          handleConfirm: () => props.handleItemDelete(props.itemId)
        })
      }}
    >
      <span className="fa fa-fw fa-trash-o" />
    </TooltipButton>

    <TooltipButton
      id={`item-panel-${props.itemId}-change-step`}
      className="btn-link-default"
      title={tex('change_step')}
      position="left"
      onClick={e => {
        e.stopPropagation()
        props.showModal(MODAL_MOVE_ITEM, {
          title: tex('change_step'),
          question: tex('change_step_confirm_message'),
          itemId: props.itemId,
          handleClick: (stepId) => props.handleItemChangeStep(props.itemId, stepId)
        })
      }}
    >
      <span className="fa fa-fw fa-exchange" />
    </TooltipButton>

    <TooltipButton
      id={`item-panel-${props.itemId}-duplicate`}
      className="btn-link-default"
      title={tex('duplicate')}
      position="left"
      onClick={e => {
        e.stopPropagation()
        props.showModal(MODAL_DUPLICATE_ITEM, {
          title: tex('duplicate_item'),
          handleSubmit: (amount) => props.handleItemDuplicate(props.itemId, amount)
        })
      }}
    >
      <span className="fa fa-fw fa-copy" />
    </TooltipButton>

    <TooltipElement
      id={`item-panel-${props.itemId}-move`}
      position="left"
      tip={tex('move_item')}
    >
      {props.connectDragSource(
        <span
          role="button"
          className="btn btn-link-default drag-handle"
          draggable="true"
          onClick={(e) => e.stopPropagation()}
        >
          <span className="fa fa-fw fa-arrows" />
        </span>
      )}
    </TooltipElement>
  </div>

ItemActions.propTypes = {
  itemId: T.string.isRequired,
  hasErrors: T.bool.isRequired,
  validating: T.bool.isRequired,
  handleItemDelete: T.func.isRequired,
  showModal: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  handleItemChangeStep: T.func.isRequired,
  handleItemDuplicate: T.func.isRequired
}

const ItemHeader = props =>
  <div
    className="item-header"
    onClick={() => props.handlePanelClick(makeItemPanelKey(props.item.type, props.item.id))}
  >
    <span className="panel-title" aria-expanded={props.expanded}>
      <ItemIcon name={getDefinition(props.item.type).name}/>
      {props.numbering &&
        <span>{props.numbering}.{'\u00A0'}</span>
      }
      {props.item.title || trans(getDefinition(props.item.type).name, {}, 'question_types')}
    </span>

    <ItemActions
      itemId={props.item.id}
      hasErrors={props.hasErrors}
      validating={props.validating}
      handleItemDelete={props.handleItemDelete}
      handleItemDuplicate={props.handleItemDuplicate}
      handleItemChangeStep={props.handleItemChangeStep}
      showModal={props.showModal}
      connectDragSource={props.connectDragSource}
    />
  </div>

ItemHeader.propTypes = {
  item: T.object.isRequired,
  numbering: T.string,
  handlePanelClick: T.func.isRequired,
  handleItemDelete: T.func.isRequired,
  handleItemChangeStep: T.func.isRequired,
  handleItemDuplicate: T.func.isRequired,
  showModal: T.func.isRequired,
  hasErrors: T.bool.isRequired,
  validating: T.bool.isRequired,
  connectDragSource: T.func.isRequired,
  expanded: T.bool.isRequired
}

const ItemPanel = props =>
  <div
    id={`panel-${props.item.id}`}
    style={{opacity: props.isDragging ? 0 : 1}}
  >
    <fieldset
      disabled={props.item.meta.protectQuestion && !props.item.rights.edit}
    >
      <Panel
        header={
          <ItemHeader
            item={props.item}
            numbering={props.numbering !== NUMBERING_NONE ? props.stepIndex + '.' + getNumbering(props.numbering, props.index): null}
            handlePanelClick={props.handlePanelClick}
            handleItemDelete={props.handleItemDelete}
            handleItemChangeStep={props.handleItemChangeStep}
            handleItemDuplicate={props.handleItemDuplicate}
            showModal={props.showModal}
            connectDragSource={props.connectDragSource}
            hasErrors={!isEmpty(props.item._errors)}
            validating={props.validating}
            expanded={props.expanded}
          />
        }
        collapsible={true}
        expanded={props.expanded}
      >
        <ItemForm
          item={props.item}
          validating={props.validating}
          showModal={props.showModal}
          mandatoryQuestions={props.mandatoryQuestions}
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
      </Panel>
    </fieldset>
  </div>

ItemPanel.propTypes = {
  id: T.string.isRequired,
  index: T.number.isRequired,
  stepIndex: T.number.isRequired,
  item: T.object.isRequired,
  numbering: T.string.isRequired,
  expanded: T.bool.isRequired,
  mandatoryQuestions: T.bool.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDelete: T.func.isRequired,
  handleItemUpdate: T.func.isRequired,
  handleItemChangeStep: T.func.isRequired,
  handleItemDetailUpdate: T.func.isRequired,
  handleItemHintsUpdate: T.func.isRequired,
  handleItemDuplicate: T.func.isRequired,
  showModal: T.func.isRequired,
  closeModal: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  connectDropTarget: T.func.isRequired,
  isDragging: T.bool.isRequired,
  onSort: T.func.isRequired,
  sortDirection: T.string.isRequired,
  validating: T.bool.isRequired
}

const ContentHeader = props =>
  <div
    className="item-header"
    onClick={() => props.handlePanelClick(makeItemPanelKey(props.item.type, props.item.id))}
  >
    <span className="panel-title" aria-expanded={props.expanded}>
      <span className={classes('item-icon', 'item-icon-sm', getContentDefinition(props.item.type).icon)} />
      {props.item.title || trans(getContentDefinition(props.item.type).type, {}, 'question_types')}
    </span>

    <ItemActions
      itemId={props.item.id}
      handleItemDelete={props.handleItemDelete}
      handleItemDuplicate={props.handleItemDuplicate}
      handleItemChangeStep={props.handleItemChangeStep}
      showModal={props.showModal}
      hasErrors={props.hasErrors}
      validating={props.validating}
      connectDragSource={props.connectDragSource}
    />
  </div>

ContentHeader.propTypes = {
  item: T.object.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDelete: T.func.isRequired,
  handleItemDuplicate: T.func.isRequired,
  handleItemChangeStep: T.func.isRequired,
  showModal: T.func.isRequired,
  hasErrors: T.bool.isRequired,
  validating: T.bool.isRequired,
  connectDragSource: T.func.isRequired,
  expanded: T.bool.isRequired
}

class ContentPanel extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return this.props.connectDropTarget(
      <div style={{opacity: this.props.isDragging ? 0 : 1}}>
        <Panel
          header={
            <ContentHeader
              item={this.props.item}
              handlePanelClick={this.props.handlePanelClick}
              handleItemDelete={this.props.handleItemDelete}
              handleItemDuplicate={this.props.handleItemDuplicate}
              handleItemChangeStep={this.props.handleItemChangeStep}
              showModal={this.props.showModal}
              connectDragSource={this.props.connectDragSource}
              hasErrors={!isEmpty(this.props.item._errors)}
              validating={this.props.validating}
              expanded={this.props.expanded}
            />
          }
          collapsible={true}
          expanded={this.props.expanded}
        >
          <ContentItemForm
            item={this.props.item}
            validating={this.props.validating}
            onChange={(propertyPath, value) =>
                this.props.handleContentItemUpdate(this.props.item.id, propertyPath, value)
              }
          >
            {React.createElement(
              getContentDefinition(this.props.item.type).editor.component,
              {
                item: this.props.item,
                validating: this.props.validating,
                onChange: subAction =>
                  this.props.handleContentItemDetailUpdate(this.props.item.id, subAction)
              }
            )}
          </ContentItemForm>
        </Panel>
      </div>
    )
  }
}

ContentPanel.propTypes = {
  id: T.string.isRequired,
  item: T.object.isRequired,
  expanded: T.bool.isRequired,
  numbering: T.string,
  handlePanelClick: T.func.isRequired,
  handleItemDelete: T.func.isRequired,
  handleItemChangeStep: T.func.isRequired,
  /*handleItemUpdate: T.func.isRequired,*/
  handleItemDuplicate: T.func.isRequired,
  handleItemDetailUpdate: T.func.isRequired,
  handleContentItemUpdate: T.func.isRequired,
  handleContentItemDetailUpdate: T.func.isRequired,
  showModal: T.func.isRequired,
  connectDropTarget: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  isDragging: T.bool.isRequired,
  onSort: T.func.isRequired,
  sortDirection: T.string.isRequired,
  validating: T.bool.isRequired
}

let SortableItemPanel = makeSortable(
  ItemPanel,
  'STEP_ITEM',
  ItemPanelDragPreview
)

let SortableContentPanel = makeSortable(
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
            <span className="caret" />
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
          id={props.step.id}
          title={props.step.title}
          description={props.step.description}
          onChange={(newValue) => props.updateStep(props.step.id, newValue)}
        />
      </Panel>

      <hr />

      {props.step.items.map((item, index) => isQuestionType(item.type) ?
        <SortableItemPanel
          {...props}

          id={item.id}
          index={index}
          item={item}
          mandatoryQuestions={props.mandatoryQuestions}
          stepId={props.step.id}
          key={item.type + item.id}
          eventKey={makeItemPanelKey(item.type, item.id)}
          onSort={(id, swapId) => props.handleItemMove(id, swapId, props.step.id)}
          sortDirection={SORT_VERTICAL}
          validating={props.validating}

          handlePanelClick={panelKey => props.handlePanelClick(props.step.id, panelKey)}
          handleItemDelete={itemId => props.handleItemDelete(props.step.id, itemId)}
          handleItemDuplicate={(itemId, amount) => props.handleItemDuplicate(props.step.id, itemId, amount)}
          handleItemChangeStep={props.handleItemChangeStep}

          handleItemUpdate={props.handleItemUpdate}
          handleItemHintsUpdate={props.handleItemHintsUpdate}
          handleItemDetailUpdate={props.handleItemDetailUpdate}
          showModal={props.showModal}
          closeModal={props.closeModal}
        /> :
        <SortableContentPanel
          {...props}

          id={item.id}
          index={index}
          item={item}
          stepId={props.step.id}
          key={item.type + item.id}
          eventKey={makeItemPanelKey(item.type, item.id)}
          onSort={(id, swapId) => props.handleItemMove(id, swapId, props.step.id)}
          sortDirection={SORT_VERTICAL}
          validating={props.validating}

          handlePanelClick={panelKey => props.handlePanelClick(props.step.id, panelKey)}
          handleItemDelete={itemId => props.handleItemDelete(props.step.id, itemId)}
          handleItemDuplicate={(itemId, amount) => props.handleItemDuplicate(props.step.id, itemId, amount)}
          handleItemChangeStep={props.handleItemChangeStep}

          handleContentItemUpdate={props.handleContentItemUpdate}
          handleContentItemDetailUpdate={props.handleContentItemDetailUpdate}
          showModal={props.showModal}
        />
      )}
    </PanelGroup>

    {props.step.items.length === 0 &&
      <div className="no-item-info">{tex('no_item_info')}</div>
    }

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
  mandatoryQuestions: T.bool.isRequired,
  numbering: T.string,
  stepIndex: T.number,
  activePanelKey: T.oneOfType([T.string, T.bool]).isRequired,
  validating: T.bool.isRequired,
  updateStep: T.func.isRequired,
  handlePanelClick: T.func.isRequired,
  handleItemDelete: T.func.isRequired,
  handleItemChangeStep: T.func.isRequired,
  handleItemDuplicate: T.func.isRequired,
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
