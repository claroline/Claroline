import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {Select} from '#/main/app/input/components/select'

import {getActions} from '#/main/core/desktop'
import {getTools} from '#/main/core/tools'

const ShortcutRow = (props) => {
  let shortcutChoices = {}
  if ('action' === props.value.type) {
    shortcutChoices = props.actions.reduce((choices, action) => Object.assign(choices, {
      [action.name]: action.label
    }), {})
  } else if ('tool' === props.value.type) {
    shortcutChoices = props.tools.reduce((choices, tool) => Object.assign(choices, {
      [tool]: trans(tool, {}, 'tools')
    }), {})
  }

  return (
    <div className="row">
      <div className="col-md-6">
        <Select
          choices={{
            action: trans('action'),
            tool: trans('tool')
          }}
          value={props.value.type}
          onChange={(value) => props.onChange(merge({}, props.value, {type: value}))}
        />
      </div>

      <div className="col-md-6">
        <Select
          choices={shortcutChoices}
          value={props.value.name}
          onChange={(value) => props.onChange(merge({}, props.value, {name: value}))}
        />
      </div>
    </div>
  )
}

ShortcutRow.propTypes = {
  actions: T.array,
  tools: T.array,
  value: T.shape({
    type: T.oneOf(['tool', 'action']),
    name: T.string
  }),
  onChange: T.func.isRequired
}

ShortcutRow.defaultProps = {
  actions: [],
  tools: []
}

class SlideFormModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      actions: [],
      tools: Object.keys(getTools(this.props.currentUser)) || []
    }
  }

  componentDidMount() {
    getActions(this.props.currentUser).then((actions) => this.setState({actions: actions}))
  }

  render() {
    const Shortcuts = (props) =>
      <ShortcutRow
        {...props}
        actions={this.state.actions}
        tools={this.state.tools}
      />

    return (
      <Modal
        {...omit(this.props, 'formName', 'dataPart')}
        title={this.props.title}
      >
        <FormData
          level={5}
          name={this.props.formName}
          dataPart={this.props.dataPart}
          sections={[
            {
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'title',
                  label: trans('title'),
                  type: 'string',
                  required: false
                }, {
                  name: 'poster',
                  label: trans('image'),
                  type: 'image',
                  required: false
                }, {
                  name: 'content',
                  label: trans('content'),
                  type: 'html',
                  required: false
                }, {
                  name: 'shortcuts',
                  label: trans('shortcuts'),
                  type: 'collection',
                  options: {
                    component: Shortcuts,
                    defaultItem: {type: null, name: null}
                  }
                }
              ]
            }
          ]}
        />
      </Modal>
    )
  }
}

SlideFormModal.propTypes = {
  formName: T.string.isRequired,
  dataPart: T.string.isRequired,
  title: T.string.isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  )
}

SlideFormModal.defaultProps = {
  title: trans('content_edition')
}

export {
  SlideFormModal
}
