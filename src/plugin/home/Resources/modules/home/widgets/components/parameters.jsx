import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {WidgetEditor} from '#/main/core/widget/editor/components/widget'
import {MODAL_WIDGET_CREATION} from '#/main/core/widget/editor/modals/creation'
import {MODAL_WIDGET_PARAMETERS} from '#/main/core/widget/editor/modals/parameters'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {WidgetQuickForm} from '#/main/core/widget/editor/components/quick-form'

class WidgetsTabParameters extends Component {
  constructor(props) {
    super(props)

    this.state = {
      movingContentId: null,
      selectedContainerId: null,
      quickForm: null
    }
  }

  startMovingContent(contentId) {
    this.setState({movingContentId: contentId})
  }

  stopMovingContent() {
    this.setState({movingContentId: null})
  }

  render() {
    const widgets = get(this.props.currentTab, 'parameters.widgets', [])

    const selected = this.state.selectedContainerId ? widgets.find(w => w.id === this.state.selectedContainerId) : widgets[0]

    return (
      <>
        {widgets.map((widgetContainer, index) => (
          <WidgetEditor
            key={widgetContainer.id || index}
            widget={widgetContainer}
            currentContext={this.props.currentContext}
            isMoving={this.state.movingContentId}
            isSelected={selected && selected.id === widgetContainer.id}
            stopMovingContent={() => this.stopMovingContent()}
            startMovingContent={(contentId) => this.startMovingContent(contentId)}
            selectContainer={() => {
              this.setState({selectedContainerId: widgetContainer.id})
              /*if (widgetContainer.id !== this.state.selectedContainerId) {
                this.setState({selectedContainerId: widgetContainer.id})
              } else {
                this.setState({selectedContainerId: null})
              }*/
            }}
            moveContent={(movingContentId, newParentId, position) => {
              const newWidgets = cloneDeep(widgets)
              let movingContentIndex = -1

              let oldWidgets = null
              let oldParentId = null
              let oldParent = null

              // this is not pretty, but we need to be aware of all the tabs because widget can move from one to another
              this.props.tabs.forEach((tab) => {
                get(tab, 'parameters.widgets', []).forEach(widget => {
                  if (widget.contents.findIndex(content => content && content.id === movingContentId) > -1) {
                    oldWidgets = tab.parameters.widgets
                    oldParentId = tab.id
                    movingContentIndex = widget.contents.findIndex(content => content && content.id === movingContentId)
                  }
                })
              })

              if (oldWidgets && -1 !== movingContentIndex) {
                if (this.props.currentTab.id !== oldParentId) {
                  oldWidgets = cloneDeep(oldWidgets)
                } else {
                  oldWidgets = newWidgets
                }

                oldWidgets.forEach(widget => {
                  if (widget.contents.findIndex(content => content && content.id === movingContentId) > -1) {
                    oldParent = widget
                  }
                })

                const newParent = newWidgets.find(widget => widget.id === newParentId)
                // generate a new id for moved content for save simplicity
                const newContent = cloneDeep(oldParent.contents[movingContentIndex])
                newContent.id = makeId()
                newParent.contents[position] = newContent

                // removes the content to delete and replace by null
                oldParent.contents[movingContentIndex] = null

                this.props.update('widgets', newWidgets)
                this.props.update('widgets', oldWidgets, oldParentId)
              }

              this.stopMovingContent()
            }}
            update={(widget) => {
              // copy array
              const newWidgets = widgets.slice(0)
              // replace modified widget
              newWidgets[index] = widget
              // propagate change
              this.props.update('widgets', newWidgets)
            }}
            actions={[
              {
                name: 'insert-section',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_section_before'),
                modal: [MODAL_WIDGET_CREATION, {
                  create: (widget) => {
                    // copy array
                    const newWidgets = widgets.slice(0)
                    // insert element
                    newWidgets.splice(index, 0, widget) // insert element

                    // propagate change
                    this.props.update('widgets', newWidgets)
                  }
                }]
              }, {
                name: 'quick-edit',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit', {}, 'actions'),
                callback: () => this.setState({quickForm: widgetContainer.id})
              }, {
                name: 'move-top',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-arrow-up',
                label: trans('move_top', {}, 'actions'),
                disabled: 0 === index,
                callback: () => {
                  // copy array
                  const newWidgets = widgets.slice(0)

                  // permute widget with the previous one
                  const movedWidget = newWidgets[index]
                  newWidgets[index] = newWidgets[index - 1]
                  newWidgets[index - 1] = movedWidget
                  // propagate change
                  this.props.update('widgets', newWidgets)
                }
              }, {
                name: 'move-bottom',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-arrow-down',
                label: trans('move_bottom', {}, 'actions'),
                disabled: widgets.length - 1 === index,
                callback: () => {
                  // copy array
                  const newWidgets = widgets.slice(0)

                  // permute widget with the next one
                  const movedWidget = newWidgets[index]
                  newWidgets[index] = newWidgets[index + 1]
                  newWidgets[index + 1] = movedWidget

                  // propagate change
                  this.props.update('widgets', newWidgets)
                }
              }, {
                name: 'configure',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-cog',
                label: trans('configure', {}, 'actions'),
                modal: [MODAL_WIDGET_PARAMETERS, {
                  widget: widgetContainer,
                  save: (widget) => {
                    // copy array
                    const newWidgets = widgets.slice(0)
                    // replace modified widget
                    newWidgets[index] = widget
                    // propagate change
                    this.props.update('widgets', newWidgets)
                  }
                }]
              }, {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                confirm: {
                  title: trans('section_delete_confirm_title'),
                  message: trans('section_delete_confirm_message'),
                  subtitle: widgets[index].name
                },
                callback: () => {
                  const newWidgets = widgets.slice(0) // copy array
                  newWidgets.splice(index, 1) // remove element
                  this.props.update('widgets', newWidgets)
                }
              }
            ]}
          />
        ))}

        {selected &&
          <WidgetQuickForm
            show={this.state.quickForm}
            editedContent={selected}
            close={() => this.setState({quickForm: null})}
            update={(widget) => {
              // copy array
              const newWidgets = widgets.slice(0)
              const index = newWidgets.findIndex(w => w.id === widget.id)

              // replace modified widget
              newWidgets[index] = widget
              // propagate change
              this.props.update('widgets', newWidgets)
            }}
          />
        }


        {0 === widgets.length &&
          <ContentPlaceholder
            className="my-3 flex-fill"
            size="lg"
            title={trans('no_section')}
          />
        }

        <Button
          className="btn btn-primary w-100 my-3 btn-add-section"
          type={MODAL_BUTTON}
          label={trans('add_section')}
          modal={[MODAL_WIDGET_CREATION, {
            create: (widget) => {
              this.setState({selectedContainerId: widget.id})
              return this.props.update('widgets',
                widgets.concat([widget]) // copy array & append element
              )
            }
          }]}
          primary={true}
          size="lg"
        />
      </>
    )
  }
}

WidgetsTabParameters.propTypes = {
  currentContext: T.object.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  ),
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  update: T.func.isRequired
}

export {
  WidgetsTabParameters
}
