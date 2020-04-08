import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {WidgetEditor} from '#/main/core/widget/editor/components/widget'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {MODAL_WIDGET_CREATION} from '#/main/core/widget/editor/modals/creation'
import {MODAL_WIDGET_PARAMETERS} from '#/main/core/widget/editor/modals/parameters'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

class WidgetGridEditor extends Component {
  constructor(props) {
    super(props)

    this.state = {
      movingContentId: null
    }
  }

  startMovingContent(contentId) {
    this.setState({movingContentId: contentId})
  }

  stopMovingContent() {
    this.setState({movingContentId: null})
  }


  render() {
    return (
      <div className="widgets-grid">
        {this.props.widgets.map((widgetContainer, index) =>
          <WidgetEditor
            key={index}
            widget={widgetContainer}
            currentContext={this.props.currentContext}
            isMoving={this.state.movingContentId}
            stopMovingContent={() => this.stopMovingContent()}
            startMovingContent={(contentId) => this.startMovingContent(contentId)}
            moveContent={(movingContentId, newParentId, position) => {
              const widgets = cloneDeep(this.props.widgets)
              let movingContentIndex = -1

              let oldWidgets = null
              let oldParentTabIndex = null
              let oldParent = null

              //this is not pretty but we need to be aware of all the tabs because widget can move from one to an other
              this.props.tabs.forEach((tab, index) => {
                tab.widgets.forEach(widget => {
                  if (widget.contents.findIndex(content => content && content.id === movingContentId) > -1) {
                    oldWidgets = tab.widgets
                    oldParentTabIndex = index
                    movingContentIndex = widget.contents.findIndex(content => content && content.id === movingContentId)
                  }
                })
              })

              if (oldWidgets && -1 !== movingContentIndex) {
                if (this.props.currentTabIndex !== oldParentTabIndex) {
                  oldWidgets = cloneDeep(oldWidgets)
                } else {
                  oldWidgets = widgets
                }

                oldWidgets.forEach(widget => {
                  if (widget.contents.findIndex(content => content && content.id === movingContentId) > -1) {
                    oldParent = widget
                  }
                })

                const newParent = widgets.find(widget => widget.id === newParentId)
                // generate a new id for moved content for save simplicity
                const newContent = cloneDeep(oldParent.contents[movingContentIndex])
                newContent.id = makeId()
                newParent.contents[position] = newContent

                // removes the content to delete and replace by null
                oldParent.contents[movingContentIndex] = null

                this.props.update(widgets)
                this.props.update(oldWidgets, oldParentTabIndex)
              }



              this.stopMovingContent()
            }}
            update={(widget) => {
              // copy array
              const widgets = this.props.widgets.slice(0)
              // replace modified widget
              widgets[index] = widget
              // propagate change
              this.props.update(widgets)
            }}
            actions={[
              {
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_section_before'),
                modal: [MODAL_WIDGET_CREATION, {
                  create: (widget) => {
                    // copy array
                    const widgets = this.props.widgets.slice(0)
                    // insert element
                    widgets.splice(index, 0, widget) // insert element

                    // propagate change
                    this.props.update(widgets)
                  }
                }]
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-arrow-up',
                label: trans('move_top', {}, 'actions'),
                disabled: 0 === index,
                callback: () => {
                  // copy array
                  const widgets = this.props.widgets.slice(0)

                  // permute widget with the previous one
                  const movedWidget = widgets[index]
                  widgets[index] = widgets[index - 1]
                  widgets[index - 1] = movedWidget
                  // propagate change
                  this.props.update(widgets)
                }
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-arrow-down',
                label: trans('move_bottom', {}, 'actions'),
                disabled: this.props.widgets.length - 1 === index,
                callback: () => {
                  // copy array
                  const widgets = this.props.widgets.slice(0)

                  // permute widget with the next one
                  const movedWidget = widgets[index]
                  widgets[index] = widgets[index + 1]
                  widgets[index + 1] = movedWidget

                  // propagate change
                  this.props.update(widgets)
                }
              }, {
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-cog',
                label: trans('configure', {}, 'actions'),
                modal: [MODAL_WIDGET_PARAMETERS, {
                  widget: widgetContainer,
                  save: (widget) => {
                    // copy array
                    const widgets = this.props.widgets.slice(0)
                    // replace modified widget
                    widgets[index] = widget
                    // propagate change
                    this.props.update(widgets)
                  }
                }]
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                confirm: {
                  title: trans('section_delete_confirm_title'),
                  message: trans('section_delete_confirm_message'),
                  subtitle: this.props.widgets[index].name
                },
                callback: () => {
                  const widgets = this.props.widgets.slice(0) // copy array
                  widgets.splice(index, 1) // remove element
                  this.props.update(widgets)
                }
              }
            ]}
          />
        )}

        {0 === this.props.widgets.length &&
          <ContentPlaceholder
            size="lg"
            icon="fa fa-frown-o"
            title={trans('no_section')}
          />
        }

        <Button
          className="btn btn-block btn-emphasis btn-add-section component-container"
          type={MODAL_BUTTON}
          label={trans('add_section')}
          modal={[MODAL_WIDGET_CREATION, {
            create: (widget) => this.props.update(
              this.props.widgets.concat([widget]) // copy array & append element
            )
          }]}
          disabled={this.props.disabled}
          primary={true}
        />
      </div>
    )
  }
}

WidgetGridEditor.propTypes = {
  disabled: T.bool,
  currentContext: T.object.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )),
  currentTabIndex: T.number.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  update: T.func.isRequired
}

WidgetGridEditor.defaultProps = {
  disabled: false,
  widgets: []
}

export {
  WidgetGridEditor
}
