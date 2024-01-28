import React from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import omit from 'lodash/omit'
import set from 'lodash/set'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MODAL_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {
  WidgetContainer as WidgetContainerTypes,
  WidgetInstance as WidgetInstanceTypes
} from '#/main/core/widget/prop-types'

import {WidgetContent} from '#/main/core/widget/content/containers/content'
import {MODAL_WIDGET_CONTENT} from '#/main/core/widget/content/modals/creation'
import {MODAL_CONTENT_PARAMETERS} from '#/main/core/widget/content/modals/parameters'

import {WidgetContainer} from '#/main/core/widget/components/container'
import {WidgetToolbar} from '#/main/core/widget/components/toolbar'

const WidgetCol = props =>
  <div className={`widget-col col-xs-12 col-md-${props.size}`}>
    {props.content &&
      <Toolbar
        className="btn-toolbar mb-2 gap-1 justify-content-center"
        buttonName="btn btn-outline-secondary"
        size="sm"
        disabled={props.disabled}
        /*tooltip="bottom"*/
        actions={[
          {
            name: 'edit',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            modal: [MODAL_CONTENT_PARAMETERS, {
              currentContext: props.currentContext,
              content: props.content,
              save: props.updateContent
            }]
          }, {
            name: 'move-start',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-arrows',
            label: trans('move', {}, 'actions'),
            callback: () => props.startMovingContent(props.content.id),
            displayed: props.content.id !== props.isMoving,
            disabled: !!props.isMoving
          }, {
            name: 'move-drop',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-ban',
            label: trans('cancel', {}, 'actions'),
            callback: () => props.stopMovingContent(),
            displayed: props.content.id === props.isMoving
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            confirm: {
              title: trans('widget_delete_confirm_title', {}, 'widget'),
              message: trans('widget_delete_confirm_message', {}, 'widget')
            },
            callback: () => props.deleteContent(props.content),
            dangerous: true
          }
        ]}
      />
    }

    {props.content &&
      <WidgetContent
        instance={props.content}
        currentContext={props.currentContext}
        display={props.display}
      />
    }

    {!props.content &&
      <Toolbar
        buttonName="btn btn-block"
        disabled={props.disabled}
        actions={[
          {
            name: 'insert',
            type: CALLBACK_BUTTON,
            label: trans('insert_widget', {}, 'widget'),
            callback: () => props.moveContent(props.isMoving),
            displayed: !!props.isMoving
          }, {
            name: 'add',
            type: MODAL_BUTTON,
            label: trans('add_widget', {}, 'widget'),
            modal: [MODAL_WIDGET_CONTENT, {
              currentContext: props.currentContext,
              add: props.addContent
            }],
            displayed: !props.isMoving
          }
        ]}
      />
    }
  </div>

WidgetCol.propTypes = {
  disabled: T.bool,
  size: T.number.isRequired,
  currentContext: T.object,
  content: T.shape(
    WidgetInstanceTypes.propTypes
  ),
  display: T.object,
  addContent: T.func.isRequired,
  updateContent: T.func.isRequired,
  moveContent: T.func.isRequired,
  startMovingContent: T.func.isRequired,
  stopMovingContent:T.func.isRequired,
  deleteContent:T.func.isRequired,
  isMoving: T.string
}

WidgetCol.defaultProps = {
  disabled: false
}

const WidgetEditor = props =>
  <WidgetContainer
    className={props.isSelected ? 'selected' : undefined}
    widget={props.widget}
    onClick={props.selectContainer}
  >
    <WidgetToolbar
      widget={props.widget}
      actions={props.actions}
      disabled={props.disabled}
      updateProp={(prop, value) => {
        const updated = cloneDeep(props.widget)
        set(updated, prop, value)
        props.update(updated)
      }}
    />

    <div className="row">
      {times(props.widget.display.layout.length, col =>
        <WidgetCol
          key={col}
          size={(12 / sum(props.widget.display.layout)) * props.widget.display.layout[col]}
          currentContext={props.currentContext}
          content={props.widget.contents[col]}
          display={omit(props.widget.display, 'layout')}
          addContent={(content) => {
            const widget = cloneDeep(props.widget)

            widget.contents[col] = content

            props.update(widget)
          }}
          updateContent={(newContent) => {
            // copy array
            const widget = cloneDeep(props.widget)
            // replace modified widget
            widget.contents[col] = newContent
            // propagate change
            props.update(widget)
          }}
          deleteContent={(content) => {
            const widgets = cloneDeep(props.widget)
            const contentIndex = widgets.contents.findIndex(widget => widget && widget.id === content.id)
            // removes the content to delete and replace by null
            widgets.contents[contentIndex] = null
            props.update(widgets)
          }}
          startMovingContent={props.startMovingContent}
          moveContent={(movingContentId) => props.moveContent(movingContentId, props.widget.id, col)}
          stopMovingContent={props.stopMovingContent}
          isMoving={props.isMoving}
          disabled={props.disabled}
        />
      )}
    </div>
  </WidgetContainer>

WidgetEditor.propTypes = {
  disabled: T.bool,
  currentContext: T.object,
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  update: T.func.isRequired,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )).isRequired,
  moveContent: T.func.isRequired,
  startMovingContent: T.func.isRequired,
  stopMovingContent:T.func.isRequired,
  isMoving: T.string,
  isSelected: T.bool,
  selectContainer: T.func
}

WidgetEditor.defaultProps = {
  disabled: false,
  isSelected: false,
  isMoving: false
}

export {
  WidgetEditor
}
