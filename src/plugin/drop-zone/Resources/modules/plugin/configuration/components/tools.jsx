import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {makeId} from '#/main/core/scaffolding/id'
import {PageFull} from '#/main/app/page/components/full'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_SELECTION} from '#/main/app/modals/selection'

import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/plugin/drop-zone/plugin/configuration/constants'
import {actions} from '#/plugin/drop-zone/plugin/configuration/actions'

class Tools extends Component {
  showCompilatioForm(tool = null) {
    const toolForm = !tool ? {
      id: makeId(),
      name: '',
      type: constants.compilatioValue,
      data: {
        url: 'http://service.compilatio.net/webservices/CompilatioUserClient2.wsdl',
        key: null
      }
    } : tool

    this.props.loadToolForm(toolForm)

    this.props.showModal('MODAL_COMPILATIO_FORM', {
      title: trans('compilatio_configuration', {}, 'dropzone')
    })
  }

  showForm() {
    this.props.showModal(MODAL_SELECTION, {
      title: trans('tool_type_selection_title', {}, 'dropzone'),
      items: constants.toolTypes,
      handleSelect: (type) => this.handleToolTypeSelection(type)
    })
  }

  handleToolTypeSelection(toolType) {
    this.props.fadeModal()

    switch (toolType.type) {
      case constants.compilatioValue:
        this.showCompilatioForm()
        break
    }
  }

  editTool(tool) {
    switch (tool.type) {
      case constants.compilatioValue:
        this.showCompilatioForm(tool)
        break
    }
  }

  generateColumns() {
    const columns = []

    columns.push({
      name: 'name',
      label: trans('name', {}, 'platform'),
      type: 'string',
      displayed: true
    })
    columns.push({
      name: 'type',
      label: trans('type', {}, 'platform'),
      type: 'number',
      displayed: true,
      render: (rowData) => {
        let type = rowData.type

        switch (type) {
          case constants.compilatioValue:
            type = 'Compilatio'
            break
        }

        return type
      }
    })
    columns.push({
      name: 'data',
      label: trans('data', {}, 'dropzone'),
      type: 'string',
      displayed: true,
      render: (rowData) => {
        let dataBox =
          <div>
            {Object.keys(rowData.data).map((k, idx) =>
              <div key={`data-row-${idx}`}>
                {trans(k, {}, 'dropzone')} : {rowData.data[k]}
              </div>
            )}
          </div>

        return dataBox
      }
    })

    return columns
  }

  render() {
    return (
      <PageFull
        title={trans('tools_management', {}, 'dropzone')}
        actions={[
          {
            name: 'add',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_tool', {}, 'dropzone'),
            callback: () => this.showForm(),
            primary: true
          }
        ]}
      >
        <ListData
          name="tools"
          fetch={{
            url: ['apiv2_dropzonetool_list'],
            autoload: true
          }}
          delete={{
            url: ['apiv2_dropzonetool_delete_bulk']
          }}
          definition={this.generateColumns()}
          actions={(rows) => [
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit_tool', {}, 'dropzone'),
              callback: () => this.editTool(rows[0]),
              scope: ['object']
            }
          ]}
        />
      </PageFull>
    )
  }
}

Tools.propTypes = {
  tools: T.object,
  loadToolForm: T.func.isRequired,
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const ConnectedTools = connect(
  (state) => ({
    tools: state.tools
  }),
  (dispatch) => ({
    loadToolForm: (tool) => dispatch(actions.loadToolForm(tool)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props)),
    fadeModal: () => dispatch(modalActions.fadeModal())
  })
)(Tools)

export {
  ConnectedTools as Tools
}