import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions} from '#/main/app/content/form/store'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

import {constants} from '#/main/app/content/list/constants'

class ListWidgetForm extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    const displayModesList = Object.keys(constants.DISPLAY_MODES).reduce((acc, current) => Object.assign(acc, {[current]: constants.DISPLAY_MODES[current].label}), {})

    return (
      <FormData
        embedded={true}
        level={5}
        name={this.props.name}
        dataPart="parameters"
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [

            ]
          }, {
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
            fields: [
              {
                name: 'display',
                type: 'choice',
                label: trans('list_display_mode_default'),
                required: true,
                options: {
                  choices: displayModesList,
                  condensed: true
                },
                onChange: (value) => {
                  if (value && (!this.props.instance.parameters.availableDisplays || -1 === this.props.instance.parameters.availableDisplays.indexOf(value))) {
                    this.props.updateProp(this.props.name, 'parameters.availableDisplays', [value])
                  }
                }
              }, {
                name: 'availableDisplays',
                type: 'choice',
                label: trans('list_display_modes'),
                required: true,
                options: {
                  choices: displayModesList,
                  noEmpty: true,
                  multiple: true,
                  inline: false
                },
                onChange: (selected) => {
                  if (-1 === selected.indexOf(this.props.instance.parameters.display) && selected[0]) {
                    // the default display is no longer in the list of available, get the first available
                    this.props.updateProp(this.props.name, 'parameters.display', selected[0])
                  }
                }
              }
            ]
          }
        ]}
      />
    )
  }
}

ListWidgetForm.propTypes = {
  name: T.string.isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,

  // from redux
  updateProp: T.func.isRequired
}

const ListWidgetParameters = connect(
  null,
  (dispatch) => ({
    updateProp(formName, prop, value) {
      dispatch(actions.updateProp(formName, prop, value))
    }
  })
)(ListWidgetForm)

export {
  ListWidgetParameters
}
