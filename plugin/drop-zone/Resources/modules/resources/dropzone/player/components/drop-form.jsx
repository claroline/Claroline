import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {HtmlGroup}  from '#/main/core/layout/form/components/group/html-group.jsx'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group.jsx'
import {FileGroup}  from '#/main/core/layout/form/components/group/file-group.jsx'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

class DropTextForm extends Component {
  constructor(props) {
    super(props)
    this.state = {
      text: ''
    }
  }

  render() {
    return (
      <div id="drop-text-form">
        <HtmlGroup
          id="drop-text"
          label="drop_text"
          content={this.state.text}
          onChange={value => this.setState({text: value})}
          minRows={3}
          hideLabel={true}
        />
        <button
          className="btn btn-primary"
          type="button"
          disabled={!this.state.text}
          onClick={() => this.props.handleSubmit(this.state.text)}
        >
          {trans('add', {}, 'platform')}
        </button>
      </div>
    )
  }
}

DropTextForm.propTypes = {
  handleSubmit: T.func.isRequired
}

class DropUrlForm extends Component {
  constructor(props) {
    super(props)
    this.state = {
      url: ''
    }
  }

  render() {
    return (
      <div id="drop-url-form">
        <TextGroup
          id="drop-url"
          label="drop_url"
          value={this.state.url}
          placeholder="http://..."
          onChange={value => this.setState({url: value})}
          hideLabel={true}
        />
        <button
          className="btn btn-primary"
          type="button"
          disabled={!this.state.url}
          onClick={() => this.props.handleSubmit(this.state.url)}
        >
          {trans('add', {}, 'platform')}
        </button>
      </div>
    )
  }
}

DropUrlForm.propTypes = {
  handleSubmit: T.func.isRequired
}

class DropFileForm extends Component {
  constructor(props) {
    super(props)
    this.state = {
      files: []
    }
  }

  render() {
    return (
      <div id="drop-file-form">
        <FileGroup
          controlId="drop-file"
          id="drop-file"
          label="drop_file"
          value={this.state.files}
          onChange={value => this.setState({files: value})}
          hideLabel={true}
          max={0}
        />
        <button
          className="btn btn-primary"
          type="button"
          disabled={this.state.files.length === 0}
          onClick={() => this.props.handleSubmit(this.state.files)}
        >
          {trans('add', {}, 'platform')}
        </button>
      </div>
    )
  }
}

DropFileForm.propTypes = {
  handleSubmit: T.func.isRequired
}

class DropForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      dropType: '',
    }
    this.submitDocument = this.submitDocument.bind(this)
  }

  submitDocument(data) {
    this.props.saveDocument(this.state.dropType, data)
    this.setState({dropType: ''})
  }

  render() {
    return (
      <div id="drop-form">
        <h2>{trans('add_document', {}, 'dropzone')}</h2>
        {this.props.allowedDocuments.length > 0 &&
          <SelectGroup
            id="drop-type"
            label={trans('drop_type', {}, 'dropzone')}
            choices={this.props.allowedDocuments.reduce((acc, current) => {
              acc[current] = constants.DOCUMENT_TYPES[current]

              return acc
            }, {})}
            value={this.state.dropType}
            onChange={value => this.setState({dropType: value})}
          />
        }
        {this.state.dropType === constants.DOCUMENT_TYPE_FILE &&
          <DropFileForm
            handleSubmit={this.submitDocument}
          />
        }
        {this.state.dropType === constants.DOCUMENT_TYPE_TEXT &&
          <DropTextForm
            handleSubmit={this.submitDocument}
          />
        }
        {this.state.dropType === constants.DOCUMENT_TYPE_URL &&
          <DropUrlForm
            handleSubmit={this.submitDocument}
          />
        }
      </div>
    )
  }
}

DropForm.propTypes = {
  allowedDocuments: T.arrayOf(T.string).isRequired,
  saveDocument: T.func.isRequired
}

export {
  DropForm
}
