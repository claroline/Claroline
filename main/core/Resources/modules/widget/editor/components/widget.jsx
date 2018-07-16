import React from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {trans} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'
import {Button} from '#/main/app/action/components/button'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {
  WidgetContainer as WidgetContainerTypes,
  WidgetInstance as WidgetInstanceTypes
} from '#/main/core/widget/prop-types'
import {computeStyles} from '#/main/core/widget/utils'
import {WidgetContent} from '#/main/core/widget/content/components/content'
import {MODAL_WIDGET_CONTENT} from '#/main/core/widget/content/modals/creation'
import {MODAL_CONTENT_PARAMETERS} from '#/main/core/widget/content/modals/parameters'

const WidgetCol = props =>
  <div className={`widget-col col-md-${props.size}`}>
    {props.content &&
      <div className="widget-col-configure" >
        <Button
          className="btn btn-link text-movie-subtitles"
          type="modal"
          icon="fa fa-fw fa-pencil"
          label={trans('modify_content', {}, 'widget')}
          modal={[MODAL_CONTENT_PARAMETERS, {
            content: props.content,
            save: props.updateContent
          }]}
        />
      </div>
    }
    {props.content &&
      <WidgetContent
        {...props.content}
        context={props.context}
      />
    }

    {!props.content &&
      <Button
        className="btn btn-block btn-emphasis"
        type="modal"
        label={trans('add_content', {}, 'widget')}
        modal={[MODAL_WIDGET_CONTENT, {
          context: props.context,
          add: props.addContent
        }]}
      />
    }
  </div>

WidgetCol.propTypes = {
  size: T.number.isRequired,
  context: T.object,
  content: T.shape(
    WidgetInstanceTypes.propTypes
  ),
  addContent: T.func.isRequired,
  updateContent: T.func.isRequired
}

const WidgetEditor = props =>
  <div className="widget-container">
    {props.actions.map(action =>
      <Button
        {...action}
        key={toKey(action.label)}
        id={`${toKey(action.label)}-${props.widget.id}`}
        className="btn-link"
        tooltip="top"
      />
    )}

    <section className="widget" style={computeStyles(props.widget)}>
      {props.widget.name &&
        <h2 className="h-first widget-title">{props.widget.name}</h2>
      }

      <div className="row">
        {times(props.widget.display.layout.length, col =>
          <WidgetCol
            key={col}
            size={(12 / sum(props.widget.display.layout)) * props.widget.display.layout[col]}
            context={props.context}
            widget={props.widget}
            content={props.widget.contents[col]}
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
          />
        )}
      </div>
    </section>
  </div>

WidgetEditor.propTypes = {
  context: T.object,
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  update: T.func.isRequired,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )).isRequired
}

export {
  WidgetEditor
}
